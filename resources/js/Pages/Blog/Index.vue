<script setup>
import { computed } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import Reveal from '@/Components/Reveal.vue';
import dayjs from 'dayjs';

const props = defineProps({
    user: Object,
    blogs: Object,
    categories: Object
})

// ---------- presentation helpers (no behaviour change) ----------
const page = usePage()

const isActive = (category) => (page.url || '').split('?')[0].replace(/\/$/, '').endsWith('/' + category.slug)
const noCategoryActive = computed(() => !Array.from(props.categories || []).some(isActive))

const imageOf = (blog) => (blog.images && blog.images[0]?.image ? `/${blog.images[0].image}` : null)

const stars = [
    { top: '18%', left: '8%', size: 6, delay: 0 },
    { top: '30%', left: '86%', size: 5, delay: 0.8 },
    { top: '72%', left: '16%', size: 4, delay: 1.5 },
    { top: '66%', left: '92%', size: 6, delay: 2.1 },
    { top: '20%', left: '52%', size: 4, delay: 1.1 },
]
</script>
<template>

    <Head title="Blogs" />

    <Header :user="user" />

    <!-- Hero -->
    <section class="blg-hero">
        <div class="blg-glow blg-glow-a"></div>
        <div class="blg-glow blg-glow-b"></div>
        <span class="blg-ring"></span>
        <span v-for="(s, i) in stars" :key="i" class="blg-star"
            :style="{ top: s.top, left: s.left, width: s.size + 'px', height: s.size + 'px', animationDelay: s.delay + 's' }"></span>

        <div class="blg-hero-inner">
            <nav class="blg-crumbs" aria-label="Breadcrumb">
                <Link href="/">Home</Link>
                <Icon icon="mdi:chevron-right" width="16" height="16" />
                <span>Blogs</span>
            </nav>
            <h1>Astrology <span class="blg-grad">Insights</span></h1>
            <p>Stay updated with astrology insights, remedies, and guides.</p>
        </div>
    </section>

    <section class="blg-section">
        <div class="blg-layout">

            <!-- Categories -->
            <aside class="blg-aside">
                <h3 class="blg-aside-title">
                    <Icon icon="mdi:shape-outline" width="20" height="20" /> Categories
                </h3>
                <ul class="blg-cats">
                    <li>
                        <a :href="route('blog')" class="blg-cat" :class="{ 'is-active': noCategoryActive }">
                            <span>All Blogs</span>
                            <Icon icon="mdi:chevron-right" width="18" height="18" />
                        </a>
                    </li>
                    <li v-for="category in categories" :key="category.id">
                        <a :href="route('category-blog', category.slug)" class="blg-cat"
                            :class="{ 'is-active': isActive(category) }">
                            <span>{{ category.name }}</span>
                            <Icon icon="mdi:chevron-right" width="18" height="18" />
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content: Blogs -->
            <div class="blg-main">
                <div v-if="props.blogs.data.length" class="blg-grid">
                    <Reveal v-for="(blog, i) in props.blogs.data" :key="blog.id" :delay="(i % 3) * 100"
                        style="height: 100%">
                        <article class="blg-card">
                            <!-- Blog image -->
                            <Link :href="route('blog-details', { slug: blog.slug })" class="blg-card-img">
                                <img v-if="imageOf(blog)" :src="imageOf(blog)" alt="Blog image" />
                                <span v-else class="blg-card-ph"><Icon icon="mdi:star-four-points-outline" width="44" height="44" /></span>
                                <span class="blg-date">
                                    <Icon icon="mdi:calendar-blank-outline" width="14" height="14" />
                                    {{ dayjs(blog.created_at).format("D MMM, YYYY") }}
                                </span>
                                <span v-if="blog.category?.name" class="blg-chip">{{ blog.category.name }}</span>
                            </Link>

                            <!-- Blog content -->
                            <div class="blg-card-body">
                                <h3>
                                    <Link :href="route('blog-details', { slug: blog.slug })">{{ blog.title }}</Link>
                                </h3>
                                <p v-if="blog.short_description">{{ blog.short_description }}</p>

                                <Link :href="route('blog-details', { slug: blog.slug })" class="blg-more">
                                    Read More
                                    <Icon icon="mdi:arrow-right" width="18" height="18" />
                                </Link>
                            </div>
                        </article>
                    </Reveal>
                </div>

                <!-- Empty state -->
                <div v-else class="blg-empty">
                    <img src="/images/zodiac-wheel.png" alt="" />
                    <h3>No blogs here yet</h3>
                    <p>New articles are on the way. Please check back soon.</p>
                    <a :href="route('blog')" class="blg-btn">View all blogs</a>
                </div>

                <!-- Pagination -->
                <nav v-if="blogs.links && blogs.links.length > 3" class="blg-pagination" aria-label="Pagination">
                    <template v-for="link in blogs.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="blg-pg"
                            :class="{ 'is-active': link.active }" preserve-scroll />
                        <span v-else v-html="link.label" class="blg-pg is-disabled"></span>
                    </template>
                </nav>
            </div>
        </div>
    </section>


    <Footer />
</template>

<!-- Plain "blg-" prefixed CSS: does not depend on Tailwind having scanned this file -->
<style scoped>
/* ---------- hero ---------- */
.blg-hero {
    position: relative;
    overflow: hidden;
    padding: 3.5rem 1.5rem 3rem;
    background: #fdf6f0;
    text-align: center;
}

.blg-glow {
    position: absolute;
    border-radius: 9999px;
    filter: blur(64px);
    pointer-events: none;
}

.blg-glow-a {
    top: -6rem;
    left: -5rem;
    width: 22rem;
    height: 22rem;
    background: rgba(253, 186, 116, 0.4);
    animation: blg-float 9s ease-in-out infinite;
}

.blg-glow-b {
    right: -6rem;
    bottom: -8rem;
    width: 26rem;
    height: 26rem;
    background: rgba(252, 211, 77, 0.32);
    animation: blg-float 6s ease-in-out infinite;
}

.blg-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30rem;
    height: 30rem;
    margin: -15rem 0 0 -15rem;
    border: 1.5px dashed rgba(255, 112, 16, 0.25);
    border-radius: 9999px;
    pointer-events: none;
    animation: blg-spin 70s linear infinite;
}

.blg-star {
    position: absolute;
    border-radius: 9999px;
    background: #FF7010;
    pointer-events: none;
    animation: blg-twinkle 3s ease-in-out infinite;
}

.blg-hero-inner {
    position: relative;
    max-width: 40rem;
    margin: 0 auto;
}

.blg-crumbs {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 1rem;
    padding: 0.3rem 0.9rem;
    border: 1px solid #ffe8d4;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.8);
    font-size: 0.82rem;
    font-weight: 500;
    color: #6b7280;
    animation: blg-rise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blg-crumbs a {
    color: #FF7010;
    text-decoration: none;
}

.blg-crumbs a:hover {
    text-decoration: underline;
}

.blg-hero h1 {
    margin: 0;
    font-size: clamp(2rem, 5vw, 3.25rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1f2937;
    animation: blg-rise 0.7s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.blg-grad {
    background: linear-gradient(90deg, #FF7010, #f59e0b);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.blg-hero p {
    margin: 0.75rem 0 0;
    font-size: 1.05rem;
    color: #6b7280;
    animation: blg-rise 0.7s 0.2s cubic-bezier(0.22, 1, 0.36, 1) both;
}

/* ---------- layout ---------- */
.blg-section {
    padding: 2.5rem 1rem 4rem;
    background: #fffdfb;
}

.blg-layout {
    display: grid;
    max-width: 80rem;
    grid-template-columns: minmax(0, 1fr);
    gap: 2rem;
    margin: 0 auto;
}

.blg-layout > * {
    min-width: 0;
}

@media (min-width: 900px) {
    .blg-layout {
        grid-template-columns: 16rem minmax(0, 1fr);
        align-items: start;
    }
}

/* ---------- sidebar ---------- */
.blg-aside {
    padding: 1.25rem;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 12px 32px -14px rgba(124, 45, 18, 0.14);
    animation: blg-rise 0.7s 0.15s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@media (min-width: 900px) {
    .blg-aside {
        position: sticky;
        top: 8.5rem;
    }
}

.blg-aside-title {
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

.blg-aside-title svg {
    color: #FF7010;
}

.blg-cats {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.blg-cat {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.6rem 0.8rem;
    border-radius: 0.7rem;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.blg-cat svg {
    opacity: 0;
    transform: translateX(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.blg-cat:hover {
    background: #fff4ea;
    color: #FF7010;
    transform: translateX(3px);
}

.blg-cat:hover svg {
    opacity: 1;
    transform: translateX(0);
}

.blg-cat.is-active {
    background: linear-gradient(135deg, #FF7010, #FF9248);
    color: #fff;
    box-shadow: 0 8px 18px rgba(255, 112, 16, 0.3);
}

.blg-cat.is-active svg {
    opacity: 1;
    transform: none;
}

.blg-cat.is-active:hover {
    transform: none;
    color: #fff;
}

/* phones: categories become a swipeable chip row */
@media (max-width: 899px) {
    .blg-aside-title {
        display: none;
    }

    .blg-aside {
        padding: 0.6rem;
    }

    .blg-cats {
        flex-direction: row;
        gap: 0.4rem;
        overflow-x: auto;
        padding-bottom: 0.15rem;
        scrollbar-width: none;
    }

    .blg-cats::-webkit-scrollbar {
        display: none;
    }

    .blg-cat {
        white-space: nowrap;
        padding: 0.45rem 0.9rem;
        border-radius: 9999px;
        background: #fff7ed;
    }

    .blg-cat svg {
        display: none;
    }
}

/* ---------- cards ---------- */
.blg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(16.5rem, 1fr));
    gap: 1.5rem;
}

.blg-card {
    position: relative;
    display: flex;
    height: 100%;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #ffe8d4;
    border-radius: 1.1rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 12px 32px -14px rgba(124, 45, 18, 0.14);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.blg-card::after {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, #FF7010, #fcd34d);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.5s ease;
}

.blg-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 26px 44px -16px rgba(124, 45, 18, 0.32);
}

.blg-card:hover::after {
    transform: scaleX(1);
}

.blg-card-img {
    position: relative;
    display: block;
    height: 12rem;
    overflow: hidden;
    background: linear-gradient(135deg, #ffedd5, #fed7aa);
}

.blg-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.blg-card:hover .blg-card-img img {
    transform: scale(1.1);
}

.blg-card-ph {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    color: #FF7010;
    opacity: 0.55;
}

.blg-date {
    position: absolute;
    left: 0.75rem;
    top: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.7rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.92);
    font-size: 0.75rem;
    font-weight: 600;
    color: #c2410c;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.blg-chip {
    position: absolute;
    right: 0.75rem;
    bottom: 0.75rem;
    padding: 0.2rem 0.7rem;
    border-radius: 9999px;
    background: #FF7010;
    font-size: 0.72rem;
    font-weight: 700;
    color: #fff;
}

.blg-card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 1.15rem 1.25rem 1.3rem;
}

.blg-card-body h3 {
    margin: 0 0 0.5rem;
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.35;
}

.blg-card-body h3 a {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.2s ease;
}

.blg-card:hover h3 a {
    color: #FF7010;
}

.blg-card-body p {
    display: -webkit-box;
    margin: 0 0 1rem;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    font-size: 0.9rem;
    line-height: 1.55;
    color: #6b7280;
}

.blg-more {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: auto;
    font-weight: 600;
    color: #FF7010;
    text-decoration: none;
}

.blg-more svg {
    transition: transform 0.3s ease;
}

.blg-card:hover .blg-more svg {
    transform: translateX(5px);
}

/* ---------- empty ---------- */
.blg-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 1rem;
    text-align: center;
}

.blg-empty img {
    width: 9rem;
    opacity: 0.18;
    animation: blg-spin 50s linear infinite;
}

.blg-empty h3 {
    margin: 1rem 0 0.35rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.blg-empty p {
    margin: 0 0 1.25rem;
    color: #6b7280;
}

.blg-btn {
    padding: 0.65rem 1.5rem;
    border-radius: 0.7rem;
    background: #FF7010;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.3);
    transition: transform 0.25s ease, background 0.25s ease;
}

.blg-btn:hover {
    transform: translateY(-2px);
    background: #E85F00;
}

/* ---------- pagination ---------- */
.blg-pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 2.5rem;
}

.blg-pg {
    min-width: 2.4rem;
    padding: 0.5rem 0.9rem;
    border: 1px solid #fed7aa;
    border-radius: 0.7rem;
    background: #fff;
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
    color: #4b5563;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.blg-pg:hover {
    transform: translateY(-2px);
    background: #fff4ea;
    color: #FF7010;
}

.blg-pg.is-active {
    border-color: #FF7010;
    background: #FF7010;
    color: #fff;
    box-shadow: 0 8px 16px rgba(255, 112, 16, 0.3);
}

.blg-pg.is-disabled {
    border-color: #e5e7eb;
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.blg-pg.is-disabled:hover {
    transform: none;
}

/* ---------- keyframes ---------- */
@keyframes blg-rise {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes blg-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-14px); }
}

@keyframes blg-spin {
    to { transform: rotate(360deg); }
}

@keyframes blg-twinkle {
    0%, 100% { opacity: 0.25; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.25); }
}

@media (prefers-reduced-motion: reduce) {
    .blg-glow,
    .blg-ring,
    .blg-star,
    .blg-crumbs,
    .blg-hero h1,
    .blg-hero p,
    .blg-aside,
    .blg-empty img {
        animation: none !important;
    }
}
</style>