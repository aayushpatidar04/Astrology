<script setup>
import { Link, usePage } from "@inertiajs/vue3";

defineProps({
    // text shown under "My Astro Sathi" in the hero, e.g. "Terms & Conditions"
    subtitle: { type: String, default: "" },
});

// TODO: set these three to the real URLs of your public pages
const links = [
    { label: "Terms & Conditions", href: "/terms-conditions" },
    { label: "Privacy Policy", href: "/privacy-policy" },
    { label: "Refund & Cancellation", href: "/refund-policy" },
];

const page = usePage();
const isActive = (href) => page.url.split("?")[0].replace(/\/$/, "") === href;

const year = new Date().getFullYear();

function starStyle(n) {
    const size = ((n * 7) % 3) + 1;
    return {
        width: size + "px",
        height: size + "px",
        top: ((n * 137.5) % 100) + "%",
        left: ((n * 97.3) % 100) + "%",
        animationDelay: ((n * 0.3) % 5) + "s",
        animationDuration: 3 + (n % 4) + "s",
    };
}
</script>

<template>
    <div class="pl-page">
        <!-- Soft floating sparkles -->
        <div class="pl-stars" aria-hidden="true">
            <span
                v-for="n in 60"
                :key="n"
                class="pl-star"
                :style="starStyle(n)"
            />
        </div>

        <!-- Constellation decoration -->
        <svg
            class="pl-constellation"
            aria-hidden="true"
            viewBox="0 0 400 400"
            xmlns="http://www.w3.org/2000/svg"
        >
            <line
                x1="50"
                y1="80"
                x2="180"
                y2="140"
                stroke="rgba(255,112,16,0.18)"
                stroke-width="1"
            />
            <line
                x1="180"
                y1="140"
                x2="300"
                y2="90"
                stroke="rgba(255,112,16,0.18)"
                stroke-width="1"
            />
            <line
                x1="300"
                y1="90"
                x2="350"
                y2="200"
                stroke="rgba(255,112,16,0.18)"
                stroke-width="1"
            />
            <circle cx="50" cy="80" r="3" fill="rgba(255,112,16,0.45)" />
            <circle cx="180" cy="140" r="3" fill="rgba(255,112,16,0.45)" />
            <circle cx="300" cy="90" r="3" fill="rgba(255,112,16,0.45)" />
            <circle cx="350" cy="200" r="3" fill="rgba(255,112,16,0.45)" />
        </svg>

        <!-- Common header -->
        <header class="pl-nav">
            <div class="pl-nav-inner">
                <a href="/" class="pl-brand" aria-label="My Astro Sathi home">
                    <img src="/images/logo.png" alt="My Astro Sathi" />
                </a>

                <nav class="pl-links" aria-label="Policies">
                    <Link
                        v-for="l in links"
                        :key="l.href"
                        :href="l.href"
                        class="pl-link"
                        :class="{ 'is-active': isActive(l.href) }"
                    >
                        {{ l.label }}
                    </Link>
                </nav>

                <a href="/" class="pl-home">Home</a>
            </div>
        </header>

        <main class="pl-container">
            <!-- Hero -->
            <section class="pl-hero">
                <span class="pl-ring pl-ring-a" aria-hidden="true"></span>
                <span class="pl-ring pl-ring-b" aria-hidden="true"></span>
                <div class="pl-emblem">✦</div>
                <h1 class="pl-title">My Astro Sathi</h1>
                <p v-if="subtitle" class="pl-subtitle">{{ subtitle }}</p>
                <div class="pl-divider">
                    <span class="pl-divider-line" />
                    <span class="pl-divider-star">★</span>
                    <span class="pl-divider-line" />
                </div>
            </section>

            <slot />
        </main>

        <!-- Common footer -->
        <footer class="pl-footer">
            <div class="pl-footer-inner">
                <div class="pl-footer-brand">
                    <img src="/images/logo.png" alt="My Astro Sathi" />
                    <p>✦ My Astro Sathi — Written in the Stars ✦</p>
                </div>

                <div class="pl-footer-col">
                    <h4>Policies</h4>
                    <Link v-for="l in links" :key="l.href" :href="l.href">{{
                        l.label
                    }}</Link>
                </div>

                <div class="pl-footer-col">
                    <h4>Contact</h4>
                    <a href="mailto:support@myastrosathi.com"
                        >support@myastrosathi.com</a
                    >
                    <a href="tel:+919201191929">+91 9201191929</a>
                    <a href="https://myastrosathi.com">myastrosathi.com</a>
                </div>
            </div>

            <div class="pl-footer-bar">
                © {{ year }} My Astro Sathi. All rights reserved.
            </div>
        </footer>
    </div>
</template>

<!-- Global on purpose: Terms, Privacy and Refund pages reuse the "pl-" classes below -->
<style>
/* ─── Page ─────────────────────────────────────── */
.pl-page {
    position: relative;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    overflow-x: hidden;
    background: linear-gradient(180deg, #fdf6f0 0, #fffdfb 420px);
    color: #1f2937;
}

.pl-stars {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
}

.pl-star {
    position: absolute;
    border-radius: 50%;
    background: #ff9248;
    opacity: 0;
    animation: pl-twinkle linear infinite;
}

.pl-constellation {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 0;
    width: 400px;
    height: 400px;
    opacity: 0.8;
    pointer-events: none;
}

/* ─── Header ───────────────────────────────────── */
.pl-nav {
    position: sticky;
    top: 0;
    z-index: 30;
    border-bottom: 1px solid #ffe8d4;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
}

.pl-nav-inner {
    display: flex;
    max-width: 64rem;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem 1rem;
    margin: 0 auto;
    padding: 0.6rem 1.25rem;
}

.pl-brand img {
    display: block;
    height: 2.5rem;
    width: auto;
    transition: transform 0.3s ease;
}

.pl-brand:hover img {
    transform: scale(1.05);
}

.pl-links {
    display: flex;
    order: 3;
    width: 100%;
    gap: 0.25rem;
    overflow-x: auto;
    padding-bottom: 0.15rem;
}

.pl-link {
    padding: 0.45rem 0.9rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    color: #4b5563;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        box-shadow 0.2s ease;
}

.pl-link:hover {
    background: #fff4ea;
    color: #ff7010;
}

.pl-link.is-active {
    background: linear-gradient(135deg, #ff7010, #ff9248);
    color: #fff;
    box-shadow: 0 8px 18px rgba(255, 112, 16, 0.3);
}

.pl-home {
    padding: 0.45rem 1rem;
    border: 1px solid #ff7010;
    border-radius: 0.65rem;
    background: #ff7010;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.28);
    transition:
        transform 0.25s ease,
        background 0.25s ease,
        box-shadow 0.25s ease;
}

.pl-home:hover {
    transform: translateY(-2px);
    background: #e85f00;
    box-shadow: 0 10px 20px rgba(255, 112, 16, 0.38);
}

@media (min-width: 820px) {
    .pl-links {
        order: 0;
        width: auto;
        padding-bottom: 0;
    }
}

/* ─── Container + hero ─────────────────────────── */
.pl-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 52rem;
    flex: 1;
    margin: 0 auto;
    padding: 2rem 1.25rem 4rem;
}

.pl-hero {
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
    padding: 2.5rem 1.5rem;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, #ff7010 0%, #ff8a3d 55%, #ffb066 100%);
    text-align: center;
    color: #fff;
    box-shadow: 0 24px 48px -20px rgba(255, 112, 16, 0.6);
    animation: pl-rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.pl-ring {
    position: absolute;
    border-radius: 9999px;
    pointer-events: none;
}

.pl-ring-a {
    top: -6rem;
    right: -4rem;
    width: 18rem;
    height: 18rem;
    border: 2px dashed rgba(255, 255, 255, 0.35);
    animation: pl-spin 60s linear infinite;
}

.pl-ring-b {
    bottom: -7rem;
    left: -3rem;
    width: 15rem;
    height: 15rem;
    background: rgba(255, 255, 255, 0.12);
}

.pl-emblem {
    position: relative;
    margin-bottom: 10px;
    font-size: 2.2rem;
    animation: pl-pulse 3s ease-in-out infinite;
}

.pl-title {
    position: relative;
    margin: 0 0 6px;
    font-size: clamp(1.8rem, 5vw, 2.6rem);
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #fff;
}

.pl-subtitle {
    position: relative;
    margin: 0 0 18px;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.92);
}

.pl-divider {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.pl-divider-line {
    display: block;
    width: 80px;
    height: 1px;
    background: linear-gradient(
        to right,
        transparent,
        rgba(255, 255, 255, 0.8),
        transparent
    );
}

.pl-divider-star {
    font-size: 0.7rem;
}

@media (min-width: 640px) {
    .pl-container {
        padding-top: 2.75rem;
    }

    .pl-hero {
        padding: 3rem 2rem;
    }
}

/* ─── Shared content blocks ────────────────────── */
.pl-intro {
    margin: 0 0 1.5rem;
    padding: 22px 26px;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    font-size: 0.95rem;
    line-height: 1.85;
    color: #4b5563;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 12px 32px -14px rgba(124, 45, 18, 0.14);
    animation: pl-rise 0.6s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.pl-card,
.pl-section,
.pl-contact {
    margin-bottom: 1.25rem;
    padding: 24px 26px;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 12px 32px -14px rgba(124, 45, 18, 0.14);
    animation: pl-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.pl-card:hover,
.pl-section:hover {
    transform: translateY(-2px);
    border-color: #fdba74;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 18px 36px -14px rgba(124, 45, 18, 0.22);
}

.pl-doc-title {
    margin: 0 0 0.25rem;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.pl-meta {
    margin: 0 0 1.5rem;
    font-size: 0.85rem;
    color: #9ca3af;
}

.pl-card-title {
    margin: 0 0 0.6rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.pl-card-text {
    margin: 0 0 0.6rem;
    font-size: 0.93rem;
    line-height: 1.8;
    color: #4b5563;
}

.pl-card-text:last-child {
    margin-bottom: 0;
}

.pl-card-text strong,
.pl-text strong {
    font-weight: 700;
    color: #1f2937;
}

.pl-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.pl-number {
    min-width: 2rem;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-align: center;
    color: #c2410c;
}

.pl-icon {
    display: flex;
    width: 2.4rem;
    height: 2.4rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.8rem;
    background: #fff4ea;
    font-size: 1.2rem;
}

.pl-icon-lg {
    width: 2.8rem;
    height: 2.8rem;
    border-radius: 0.9rem;
    background: #ffedd5;
    font-size: 1.4rem;
}

.pl-section-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.pl-text {
    margin: 0 0 10px;
    font-size: 0.93rem;
    line-height: 1.85;
    color: #4b5563;
}

.pl-text:last-child {
    margin-bottom: 0;
}

.pl-list {
    margin: 10px 0 0;
    padding: 0;
    list-style: none;
}

.pl-list-item {
    position: relative;
    padding: 9px 0 9px 26px;
    border-bottom: 1px solid #fff0e1;
    font-size: 0.92rem;
    line-height: 1.8;
    color: #4b5563;
    transition: background 0.2s ease;
}

.pl-list-item:last-child {
    border-bottom: none;
}

.pl-list-item:hover {
    background: #fffaf5;
}

.pl-list-item::before {
    content: "✦";
    position: absolute;
    left: 4px;
    top: 12px;
    font-size: 0.6rem;
    color: #ff7010;
}

.pl-notice {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin: 2rem 0 1.5rem;
    padding: 22px 26px;
    border: 1px solid #fed7aa;
    border-left: 4px solid #ff7010;
    border-radius: 1rem;
    background: linear-gradient(90deg, #fff4ea, #fffaf5);
}

.pl-notice-icon {
    min-width: 24px;
    margin-top: 2px;
    font-size: 1.4rem;
    text-align: center;
    color: #ff7010;
    animation: pl-pulse 3s ease-in-out infinite;
}

.pl-notice-text {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.8;
    color: #9a3412;
}

.pl-contact {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.pl-contact:hover {
    transform: none;
}

.pl-a {
    border-bottom: 1px solid rgba(255, 112, 16, 0.35);
    color: #ff7010;
    text-decoration: none;
    transition:
        color 0.2s ease,
        border-color 0.2s ease;
}

.pl-a:hover {
    border-color: #e85f00;
    color: #e85f00;
}

.pl-fade-enter-active,
.pl-fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.pl-fade-enter-from,
.pl-fade-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

/* policy tabs (shown only when a page has more than one) */
.pl-tabs {
    display: flex;
    width: fit-content;
    max-width: 100%;
    gap: 0.25rem;
    margin: 0 auto 1.5rem;
    padding: 0.3rem;
    overflow-x: auto;
    border: 1px solid #ffe8d4;
    border-radius: 0.9rem;
    background: #fff;
}

.pl-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border: 0;
    border-radius: 0.65rem;
    background: transparent;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    color: #6b7280;
    cursor: pointer;
    transition:
        background 0.25s ease,
        color 0.25s ease,
        box-shadow 0.25s ease;
}

.pl-tab:hover {
    background: #fff4ea;
    color: #ff7010;
}

.pl-tab.is-active {
    background: #ff7010;
    color: #fff;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.3);
}

/* ─── Footer ───────────────────────────────────── */
.pl-footer {
    position: relative;
    z-index: 1;
    border-top: 1px solid #ffe8d4;
    background: #fff;
}

.pl-footer-inner {
    display: grid;
    max-width: 64rem;
    grid-template-columns: 1fr;
    gap: 1.75rem;
    margin: 0 auto;
    padding: 2.25rem 1.25rem 1.75rem;
}

.pl-footer-brand img {
    height: 2.75rem;
    width: auto;
}

.pl-footer-brand p {
    margin: 0.75rem 0 0;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    color: #c2410c;
}

.pl-footer-col {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.pl-footer-col h4 {
    margin: 0 0 0.3rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
}

.pl-footer-col a {
    width: fit-content;
    font-size: 0.9rem;
    color: #4b5563;
    text-decoration: none;
    transition:
        color 0.2s ease,
        transform 0.2s ease;
}

.pl-footer-col a:hover {
    color: #ff7010;
    transform: translateX(3px);
}

.pl-footer-bar {
    padding: 1rem 1.25rem;
    border-top: 1px solid #fff0e1;
    background: #fffaf5;
    font-size: 0.8rem;
    text-align: center;
    color: #9ca3af;
}

@media (min-width: 720px) {
    .pl-footer-inner {
        grid-template-columns: 1.4fr 1fr 1fr;
    }
}

/* ─── Keyframes ────────────────────────────────── */
@keyframes pl-twinkle {
    0%,
    100% {
        opacity: 0;
        transform: translateY(0);
    }
    50% {
        opacity: 0.55;
        transform: translateY(-6px);
    }
}

@keyframes pl-pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.75;
        transform: scale(1.15);
    }
}

@keyframes pl-rise {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pl-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .pl-page *,
    .pl-page *::before,
    .pl-page *::after {
        animation: none !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
