<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Header from '../Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import dayjs from 'dayjs';


const props = defineProps({
    blog: Object,
    user: Object,
    categories: Object
});

// ---------- presentation helpers (no behaviour change) ----------
const imgUrl = (img) => `/${img.image}`

const images = computed(() => (props.blog.images || []).map(imgUrl))

// rough reading time from the two description blocks
const readMinutes = computed(() => {
    const text = `${props.blog.description1 || ''} ${props.blog.description2 || ''}`.replace(/<[^>]*>/g, ' ')
    const words = text.split(/\s+/).filter(Boolean).length
    return Math.max(1, Math.round(words / 200))
})

// ---- reading progress ----
const articleEl = ref(null)
const progress = ref(0)
let ticking = false

function updateProgress() {
    ticking = false
    const el = articleEl.value
    if (!el) return
    const rect = el.getBoundingClientRect()
    const total = rect.height - window.innerHeight * 0.6
    progress.value = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 0
}

function onScroll() {
    if (!ticking) {
        ticking = true
        requestAnimationFrame(updateProgress)
    }
}

// ---- lightbox ----
const lightbox = ref(null)

const openLightbox = (i) => (lightbox.value = i)
const closeLightbox = () => (lightbox.value = null)
const step = (d) => {
    if (lightbox.value === null) return
    lightbox.value = (lightbox.value + d + images.value.length) % images.value.length
}

function onKey(e) {
    if (lightbox.value === null) return
    if (e.key === 'Escape') closeLightbox()
    if (e.key === 'ArrowRight') step(1)
    if (e.key === 'ArrowLeft') step(-1)
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('keydown', onKey)
    updateProgress()
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('keydown', onKey)
})

// ---- share ----
const copied = ref(false)
const shareUrl = () => window.location.href
const shareLinks = computed(() => [
    { name: 'WhatsApp', icon: 'mdi:whatsapp', color: '#16a34a', href: () => `https://wa.me/?text=${encodeURIComponent(props.blog.title + ' ' + shareUrl())}` },
    { name: 'Facebook', icon: 'mdi:facebook', color: '#2563eb', href: () => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl())}` },
    { name: 'X', icon: 'mdi:twitter', color: '#0ea5e9', href: () => `https://twitter.com/intent/tweet?text=${encodeURIComponent(props.blog.title)}&url=${encodeURIComponent(shareUrl())}` },
])

async function copyLink() {
    try {
        await navigator.clipboard.writeText(shareUrl())
        copied.value = true
        setTimeout(() => (copied.value = false), 2000)
    } catch (e) {
        window.prompt('Copy this link', shareUrl())
    }
}

const isActive = (category) => props.blog.category?.slug && props.blog.category.slug === category.slug

</script>
<template>

    <Head :title="props.blog.title" />

    <!-- reading progress -->
    <div class="blv-progress" aria-hidden="true"><div :style="{ transform: `scaleX(${progress})` }"></div></div>

    <Header :user="user" />

    <!-- Hero -->
    <section class="blv-hero">
        <div class="blv-glow blv-glow-a"></div>
        <div class="blv-glow blv-glow-b"></div>
        <div class="blv-hero-inner">
            <nav class="blv-crumbs" aria-label="Breadcrumb">
                <Link href="/">Home</Link>
                <Icon icon="mdi:chevron-right" width="16" height="16" />
                <a :href="route('blog')">Blogs</a>
                <Icon icon="mdi:chevron-right" width="16" height="16" />
                <span>{{ props.blog.title }}</span>
            </nav>

            <h1>{{ props.blog.title }}</h1>

            <div class="blv-meta">
                <span v-if="props.blog.category?.name" class="blv-chip">{{ props.blog.category.name }}</span>
                <span v-if="props.blog.created_at"><Icon icon="mdi:calendar-blank-outline" width="16" height="16" />
                    {{ dayjs(props.blog.created_at).format("D MMM, YYYY") }}</span>
                <span><Icon icon="mdi:clock-outline" width="16" height="16" /> {{ readMinutes }} min read</span>
            </div>
        </div>
    </section>

    <section class="blv-section">
        <div class="blv-layout">

            <aside class="blv-aside">
                <h3 class="blv-aside-title">
                    <Icon icon="mdi:shape-outline" width="20" height="20" /> Categories
                </h3>
                <ul class="blv-cats">
                    <li v-for="category in categories" :key="category.id">
                        <a :href="route('category-blog', category.slug)" class="blv-cat"
                            :class="{ 'is-active': isActive(category) }">
                            <span>{{ category.name }}</span>
                            <Icon icon="mdi:chevron-right" width="18" height="18" />
                        </a>
                    </li>
                </ul>
            </aside>


            <!-- Main Content: Blog -->
            <article ref="articleEl" class="blv-article">

                <!-- First image -->
                <figure v-if="blog.images && blog.images.length > 0" class="blv-hero-img" @click="openLightbox(0)">
                    <img :src="`/${blog.images[0].image}`" :alt="blog.images[0].alt_text || 'Blog image'" />
                    <span class="blv-zoom"><Icon icon="mdi:magnify-plus-outline" width="22" height="22" /></span>
                </figure>

                <!-- Description 1 -->
                <div class="blv-prose" v-html="blog.description1"></div>

                <!-- Remaining images -->
                <div v-if="blog.images && blog.images.length > 1" class="blv-gallery">
                    <figure v-for="(img, index) in blog.images.slice(1)" :key="index" @click="openLightbox(index + 1)">
                        <img :src="`/${img.image}`" :alt="img.alt_text || 'Blog image'" />
                        <span class="blv-zoom"><Icon icon="mdi:magnify-plus-outline" width="22" height="22" /></span>
                    </figure>
                </div>

                <!-- Description 2 -->
                <div v-if="blog.description2" class="blv-prose" v-html="blog.description2">
                </div>

                <!-- Tags -->
                <div v-if="blog.tags && blog.tags.length" class="blv-tags">
                    <span v-for="tag in blog.tags" :key="tag" class="blv-tag">
                        #{{ tag }}
                    </span>
                </div>

                <!-- Share -->
                <div class="blv-share">
                    <span class="blv-share-label"><Icon icon="mdi:share-variant-outline" width="18" height="18" /> Share this article</span>
                    <div class="blv-share-btns">
                        <a v-for="s in shareLinks" :key="s.name" :href="s.href()" target="_blank" rel="noopener"
                            class="blv-share-btn" :style="{ '--c': s.color }" :title="`Share on ${s.name}`"
                            @mouseenter="$event.currentTarget.href = s.href()">
                            <Icon :icon="s.icon" width="20" height="20" />
                        </a>
                        <button type="button" class="blv-share-btn" :class="{ 'is-done': copied }" style="--c: #FF7010"
                            @click="copyLink" title="Copy link">
                            <Icon :icon="copied ? 'mdi:check' : 'mdi:link-variant'" width="20" height="20" />
                        </button>
                    </div>
                    <Transition name="blv-pop"><span v-if="copied" class="blv-copied">Link copied!</span></Transition>
                </div>

                <!-- CTA -->
                <div class="blv-cta">
                    <span class="blv-cta-ring"></span>
                    <div>
                        <h3>Have a question about your own chart?</h3>
                        <p>Talk to our expert astrologers and get personal guidance on love, career and health.</p>
                    </div>
                    <div class="blv-cta-btns">
                        <Link :href="route('user.chat-with-astrologers')" class="blv-cta-btn is-chat">
                            <Icon icon="mdi:wechat" width="20" height="20" /> Chat Now
                        </Link>
                        <Link :href="route('user.talk-to-astrologers')" class="blv-cta-btn is-call">
                            <Icon icon="mdi:phone-in-talk-outline" width="20" height="20" /> Talk Now
                        </Link>
                    </div>
                </div>

                <Link :href="route('blog')" class="blv-back">
                    <Icon icon="mdi:arrow-left" width="18" height="18" /> Back to all blogs
                </Link>
            </article>

        </div>
    </section>

    <!-- Lightbox -->
    <Transition name="blv-lb">
        <div v-if="lightbox !== null" class="blv-lb" @click.self="closeLightbox">
            <button class="blv-lb-close" @click="closeLightbox" aria-label="Close"><Icon icon="mdi:close" width="26" height="26" /></button>
            <button v-if="images.length > 1" class="blv-lb-nav is-prev" @click="step(-1)" aria-label="Previous"><Icon icon="mdi:chevron-left" width="30" height="30" /></button>
            <img :key="lightbox" :src="images[lightbox]" alt="Blog image" />
            <button v-if="images.length > 1" class="blv-lb-nav is-next" @click="step(1)" aria-label="Next"><Icon icon="mdi:chevron-right" width="30" height="30" /></button>
            <span v-if="images.length > 1" class="blv-lb-count">{{ lightbox + 1 }} / {{ images.length }}</span>
        </div>
    </Transition>

    <Footer />
</template>

<style scoped>
/* ---------- progress ---------- */
.blv-progress {
    position: fixed;
    inset: 0 0 auto 0;
    z-index: 60;
    height: 3px;
    background: transparent;
    pointer-events: none;
}

.blv-progress div {
    height: 100%;
    background: linear-gradient(90deg, #FF7010, #fcd34d);
    transform-origin: left;
    transition: transform 0.1s linear;
    box-shadow: 0 0 10px rgba(255, 112, 16, 0.6);
}

/* ---------- hero ---------- */
.blv-hero {
    position: relative;
    overflow: hidden;
    padding: 3rem 1.5rem 2.75rem;
    background: #fdf6f0;
    text-align: center;
}

.blv-glow {
    position: absolute;
    border-radius: 9999px;
    filter: blur(64px);
    pointer-events: none;
}

.blv-glow-a {
    top: -6rem;
    left: -5rem;
    width: 22rem;
    height: 22rem;
    background: rgba(253, 186, 116, 0.4);
    animation: blv-float 9s ease-in-out infinite;
}

.blv-glow-b {
    right: -6rem;
    bottom: -8rem;
    width: 26rem;
    height: 26rem;
    background: rgba(252, 211, 77, 0.32);
    animation: blv-float 6s ease-in-out infinite;
}

.blv-hero-inner {
    position: relative;
    max-width: 52rem;
    margin: 0 auto;
}

.blv-crumbs {
    display: inline-flex;
    max-width: 100%;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 1.1rem;
    padding: 0.3rem 0.9rem;
    border: 1px solid #ffe8d4;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.8);
    font-size: 0.82rem;
    font-weight: 500;
    color: #6b7280;
    animation: blv-rise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blv-crumbs a {
    flex-shrink: 0;
    color: #FF7010;
    text-decoration: none;
}

.blv-crumbs a:hover {
    text-decoration: underline;
}

.blv-crumbs span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.blv-crumbs svg {
    flex-shrink: 0;
}

.blv-hero h1 {
    margin: 0;
    font-size: clamp(1.75rem, 4.5vw, 2.9rem);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.02em;
    color: #1f2937;
    animation: blv-rise 0.7s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blv-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.6rem 1.1rem;
    margin-top: 1.1rem;
    font-size: 0.88rem;
    color: #6b7280;
    animation: blv-rise 0.7s 0.2s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blv-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.blv-meta svg {
    color: #FF7010;
}

.blv-chip {
    padding: 0.2rem 0.8rem;
    border-radius: 9999px;
    background: #FF7010;
    font-weight: 700;
    color: #fff;
}

/* ---------- layout ---------- */
.blv-section {
    padding: 2.5rem 1rem 4rem;
    background: #fffdfb;
}

.blv-layout {
    display: grid;
    max-width: 80rem;
    grid-template-columns: minmax(0, 1fr);
    gap: 2rem;
    margin: 0 auto;
}

.blv-layout > * {
    min-width: 0;
}

.blv-article {
    order: 1;
    min-width: 0;
}

.blv-aside {
    order: 2;
}

@media (min-width: 900px) {
    .blv-layout {
        grid-template-columns: 16rem minmax(0, 1fr);
        align-items: start;
    }

    .blv-aside {
        order: 1;
        position: sticky;
        top: 8.5rem;
    }

    .blv-article {
        order: 2;
    }
}

.blv-aside {
    padding: 1.25rem;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 12px 32px -14px rgba(124, 45, 18, 0.14);
}

.blv-aside-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #ffedd5;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.blv-aside-title svg {
    color: #FF7010;
}

.blv-cats {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.blv-cat {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.6rem 0.8rem;
    border-radius: 0.7rem;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.blv-cat svg {
    opacity: 0;
    transform: translateX(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.blv-cat:hover {
    background: #fff4ea;
    color: #FF7010;
    transform: translateX(3px);
}

.blv-cat:hover svg {
    opacity: 1;
    transform: none;
}

.blv-cat.is-active {
    background: linear-gradient(135deg, #FF7010, #FF9248);
    color: #fff;
    box-shadow: 0 8px 18px rgba(255, 112, 16, 0.3);
}

/* ---------- article ---------- */
.blv-article {
    max-width: 52rem;
    margin: 0 auto;
    width: 100%;
    animation: blv-rise 0.8s 0.15s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blv-hero-img,
.blv-gallery figure {
    position: relative;
    margin: 0;
    overflow: hidden;
    border-radius: 1.1rem;
    background: #ffedd5;
    cursor: zoom-in;
    box-shadow: 0 20px 40px -22px rgba(124, 45, 18, 0.45);
}

.blv-hero-img img {
    display: block;
    width: 100%;
    max-height: 26rem;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.blv-gallery {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin: 2rem 0;
}

@media (min-width: 640px) {
    .blv-gallery {
        grid-template-columns: 1fr 1fr;
    }
}

.blv-gallery img {
    display: block;
    width: 100%;
    height: 16rem;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.blv-hero-img:hover img,
.blv-gallery figure:hover img {
    transform: scale(1.06);
}

.blv-zoom {
    position: absolute;
    right: 0.8rem;
    bottom: 0.8rem;
    display: flex;
    width: 2.6rem;
    height: 2.6rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.92);
    color: #FF7010;
    opacity: 0;
    transform: translateY(8px) scale(0.9);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.blv-hero-img:hover .blv-zoom,
.blv-gallery figure:hover .blv-zoom {
    opacity: 1;
    transform: none;
}

/* ---------- Quill content ---------- */
.blv-prose {
    margin: 2rem 0;
    font-size: 1.06rem;
    line-height: 1.85;
    color: #374151;
    overflow-wrap: anywhere;
}

/* Text blocks */
.blv-prose :deep(p) {
    margin: 1.1em 0;
    line-height: 1.85;
}

.blv-prose :deep(br) {
    display: block;
    margin-bottom: 0.5em;
}

/* Headings */
.blv-prose :deep(h1) {
    margin: 1.4em 0 0.6em;
    font-size: 2em;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.blv-prose :deep(h2) {
    position: relative;
    margin: 1.6em 0 0.8em;
    padding-bottom: 0.5em;
    font-size: 1.55em;
    font-weight: 800;
    line-height: 1.3;
    color: #1f2937;
}

.blv-prose :deep(h2)::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 3rem;
    height: 3px;
    border-radius: 9999px;
    background: linear-gradient(90deg, #FF7010, #fcd34d);
}

.blv-prose :deep(h3) {
    margin: 1.4em 0 0.6em;
    font-size: 1.25em;
    font-weight: 700;
    color: #9a3412;
}

/* Lists */
.blv-prose :deep(ul) {
    list-style: disc;
    margin: 1em 0 1em 1.5em;
}

.blv-prose :deep(ol) {
    list-style: decimal;
    margin: 1em 0 1em 1.5em;
}

.blv-prose :deep(li) {
    margin: 0.5em 0;
    padding-left: 0.25em;
}

.blv-prose :deep(li::marker) {
    color: #FF7010;
    font-weight: 700;
}

/* Formatting */
.blv-prose :deep(strong) {
    font-weight: 700;
    color: #1f2937;
}

.blv-prose :deep(em) {
    font-style: italic;
}

.blv-prose :deep(u) {
    text-decoration: underline;
    text-decoration-color: #fdba74;
    text-underline-offset: 3px;
}

.blv-prose :deep(s) {
    text-decoration: line-through;
}

/* Links */
.blv-prose :deep(a) {
    color: #FF7010;
    font-weight: 500;
    text-decoration: underline;
    text-decoration-color: rgba(255, 112, 16, 0.35);
    text-underline-offset: 3px;
    transition: color 0.2s ease, text-decoration-color 0.2s ease;
}

.blv-prose :deep(a:hover) {
    color: #c2410c;
    text-decoration-color: #c2410c;
}

/* Images */
.blv-prose :deep(img) {
    max-width: 100%;
    height: auto;
    margin: 1.4em 0;
    border-radius: 0.9rem;
    box-shadow: 0 16px 32px -18px rgba(124, 45, 18, 0.45);
}

/* Blockquotes */
.blv-prose :deep(blockquote) {
    margin: 1.6em 0;
    padding: 1rem 1.25rem;
    border-left: 4px solid #FF7010;
    border-radius: 0 0.9rem 0.9rem 0;
    background: #fff7ed;
    font-style: italic;
    color: #7c2d12;
}

.blv-prose :deep(blockquote p) {
    margin: 0.4em 0;
}

/* Code blocks */
.blv-prose :deep(pre) {
    margin: 1.4em 0;
    padding: 1em;
    overflow-x: auto;
    border-radius: 0.8rem;
    background: #1f2937;
    font-size: 0.9em;
    color: #f9fafb;
}

.blv-prose :deep(code) {
    padding: 0.2em 0.4em;
    border-radius: 0.3rem;
    background: #ffedd5;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.9em;
    color: #9a3412;
}

.blv-prose :deep(pre code) {
    padding: 0;
    background: none;
    color: inherit;
}

/* Quill custom classes */
.blv-prose :deep(.ql-align-center) { text-align: center; }
.blv-prose :deep(.ql-align-right) { text-align: right; }
.blv-prose :deep(.ql-align-justify) { text-align: justify; }
.blv-prose :deep(.ql-size-small) { font-size: 0.875rem; }
.blv-prose :deep(.ql-size-large) { font-size: 1.5rem; }
.blv-prose :deep(.ql-size-huge) { font-size: 2.25rem; }
.blv-prose :deep(.ql-indent-1) { margin-left: 2em; }
.blv-prose :deep(.ql-indent-2) { margin-left: 4em; }
.blv-prose :deep(.ql-indent-3) { margin-left: 6em; }

/* ---------- tags / share / cta ---------- */
.blv-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 2rem;
}

.blv-tag {
    padding: 0.3rem 0.9rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 0.875rem;
    font-weight: 500;
    color: #c2410c;
    cursor: default;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.blv-tag:hover {
    transform: translateY(-2px);
    background: #FF7010;
    color: #fff;
}

.blv-share {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1rem;
    margin-top: 2rem;
    padding: 1rem 1.25rem;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fffaf5;
}

.blv-share-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 600;
    color: #374151;
}

.blv-share-label svg {
    color: #FF7010;
}

.blv-share-btns {
    display: flex;
    gap: 0.5rem;
}

.blv-share-btn {
    display: flex;
    width: 2.5rem;
    height: 2.5rem;
    align-items: center;
    justify-content: center;
    border: 1px solid #ffe8d4;
    border-radius: 9999px;
    background: #fff;
    color: var(--c);
    cursor: pointer;
    transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
}

.blv-share-btn:hover,
.blv-share-btn.is-done {
    transform: translateY(-3px);
    border-color: var(--c);
    background: var(--c);
    color: #fff;
    box-shadow: 0 10px 18px -8px var(--c);
}

.blv-copied {
    font-size: 0.85rem;
    font-weight: 600;
    color: #16a34a;
}

.blv-pop-enter-active,
.blv-pop-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.blv-pop-enter-from,
.blv-pop-leave-to {
    opacity: 0;
    transform: translateX(-8px);
}

.blv-cta {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    margin-top: 2rem;
    padding: 1.75rem;
    overflow: hidden;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, #FF7010 0%, #FF8A3D 55%, #FFB066 100%);
    color: #fff;
    box-shadow: 0 24px 48px -20px rgba(255, 112, 16, 0.6);
}

.blv-cta-ring {
    position: absolute;
    top: -5rem;
    right: -3rem;
    width: 15rem;
    height: 15rem;
    border: 2px dashed rgba(255, 255, 255, 0.35);
    border-radius: 9999px;
    pointer-events: none;
    animation: blv-spin 60s linear infinite;
}

.blv-cta > div:first-of-type {
    position: relative;
    max-width: 26rem;
}

.blv-cta h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.blv-cta p {
    margin: 0.4rem 0 0;
    font-size: 0.92rem;
    opacity: 0.95;
}

.blv-cta-btns {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.blv-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 1.2rem;
    border-radius: 0.75rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    box-shadow: 0 10px 20px -8px rgba(0, 0, 0, 0.35);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.blv-cta-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 26px -8px rgba(0, 0, 0, 0.4);
}

.blv-cta-btn.is-chat { background: #dc2626; }
.blv-cta-btn.is-call { background: #16a34a; }

.blv-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1.75rem;
    font-weight: 600;
    color: #FF7010;
    text-decoration: none;
}

.blv-back svg {
    transition: transform 0.3s ease;
}

.blv-back:hover svg {
    transform: translateX(-5px);
}

/* ---------- lightbox ---------- */
.blv-lb {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: rgba(17, 24, 39, 0.9);
    backdrop-filter: blur(6px);
}

.blv-lb img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 0.9rem;
    box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.6);
    animation: blv-zoom 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blv-lb-close,
.blv-lb-nav {
    position: absolute;
    display: flex;
    width: 2.9rem;
    height: 2.9rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
}

.blv-lb-close:hover,
.blv-lb-nav:hover {
    background: #FF7010;
    transform: scale(1.08);
}

.blv-lb-close { top: 1.25rem; right: 1.25rem; }
.blv-lb-nav { top: 50%; margin-top: -1.45rem; }
.blv-lb-nav.is-prev { left: 1.25rem; }
.blv-lb-nav.is-next { right: 1.25rem; }

.blv-lb-count {
    position: absolute;
    bottom: 1.25rem;
    padding: 0.25rem 0.8rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.15);
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
}

.blv-lb-enter-active,
.blv-lb-leave-active {
    transition: opacity 0.3s ease;
}

.blv-lb-enter-from,
.blv-lb-leave-to {
    opacity: 0;
}

/* ---------- keyframes ---------- */
@keyframes blv-rise {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes blv-zoom {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes blv-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-14px); }
}

@keyframes blv-spin {
    to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce) {
    .blv-glow,
    .blv-cta-ring,
    .blv-crumbs,
    .blv-hero h1,
    .blv-meta,
    .blv-article,
    .blv-lb img {
        animation: none !important;
    }
}
</style>