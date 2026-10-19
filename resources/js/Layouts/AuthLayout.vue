<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    title: String,
    subtitle: String,
})

const year = new Date().getFullYear()

const stars = [
    { top: '12%', left: '10%', size: 6, delay: 0 },
    { top: '20%', left: '84%', size: 5, delay: 0.7 },
    { top: '72%', left: '8%', size: 4, delay: 1.4 },
    { top: '80%', left: '88%', size: 6, delay: 2 },
    { top: '46%', left: '94%', size: 4, delay: 1 },
]
</script>

<template>
    <div class="auth-page">
        <!-- Background: soft glows, slow rotating rings, twinkling stars -->
        <div class="auth-glow auth-glow-a"></div>
        <div class="auth-glow auth-glow-b"></div>
        <div class="auth-ring auth-ring-1"></div>
        <div class="auth-ring auth-ring-2"></div>
        <span v-for="(s, i) in stars" :key="i" class="auth-star"
            :style="{ top: s.top, left: s.left, width: s.size + 'px', height: s.size + 'px', animationDelay: s.delay + 's' }"></span>

        <div class="auth-wrap">
            <Link href="/" class="auth-logo auth-rise">
                <img src="/images/logo.png" alt="MyAstroSathi" />
            </Link>

            <div class="auth-card auth-rise" style="animation-delay: 0.1s">
                <div class="auth-head">
                    <h1 class="auth-title">{{ title }}</h1>
                    <p v-if="subtitle" class="auth-subtitle">{{ subtitle }}</p>
                </div>

                <slot />
            </div>

            <p class="auth-foot auth-rise" style="animation-delay: 0.2s">
                <Link href="/">&larr; Back to home</Link>
                <span class="auth-dot">·</span>
                &copy; {{ year }} MyAstroSathi
            </p>
        </div>
    </div>
</template>

<!--
    Plain CSS on purpose (prefixed "auth-"): these pages look right even if Tailwind
    has not scanned the new files yet.
-->
<style>
.auth-page {
    position: relative;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #fdf6f0;
    padding: 2.5rem 1rem;
}

.auth-glow {
    position: absolute;
    border-radius: 9999px;
    filter: blur(64px);
    pointer-events: none;
}

.auth-glow-a {
    top: -6rem;
    left: -6rem;
    width: 24rem;
    height: 24rem;
    background: rgba(253, 186, 116, 0.35);
    animation: auth-float 9s ease-in-out infinite;
}

.auth-glow-b {
    bottom: -8rem;
    right: -6rem;
    width: 28rem;
    height: 28rem;
    background: rgba(252, 211, 77, 0.3);
    animation: auth-float 6s ease-in-out infinite;
}

.auth-ring {
    position: absolute;
    left: 50%;
    top: 50%;
    border-radius: 9999px;
    pointer-events: none;
}

.auth-ring-1 {
    width: 34rem;
    height: 34rem;
    margin: -17rem 0 0 -17rem;
    border: 1.5px dashed rgba(255, 112, 16, 0.28);
    animation: auth-spin 70s linear infinite;
}

.auth-ring-2 {
    width: 48rem;
    height: 48rem;
    margin: -24rem 0 0 -24rem;
    border: 1.5px solid rgba(255, 112, 16, 0.12);
    animation: auth-spin 110s linear infinite reverse;
}

.auth-star {
    position: absolute;
    border-radius: 9999px;
    background: #FF7010;
    pointer-events: none;
    animation: auth-twinkle 3s ease-in-out infinite;
}

.auth-wrap {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 40rem;
}

.auth-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.auth-logo img {
    height: 3.5rem;
    width: auto;
    transition: transform 0.3s ease;
}

.auth-logo:hover img {
    transform: scale(1.05);
}

.auth-card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.94);
    padding: 2rem;
    box-shadow: 0 25px 50px -12px rgba(124, 45, 18, 0.18);
}

.auth-card::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 4px;
    background: linear-gradient(90deg, #FF7010, #FCD34D, #FF7010);
}

.auth-head {
    margin-bottom: 1.5rem;
    text-align: center;
}

.auth-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.auth-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.auth-foot {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.auth-foot a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.auth-foot a:hover {
    color: #FF7010;
}

.auth-dot {
    margin: 0 0.5rem;
}

/* ---- form pieces shared by Login + Register ---- */
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.auth-form-tight {
    gap: 1rem;
}

.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #bbf7d0;
    border-radius: 0.5rem;
    background: #f0fdf4;
    font-size: 0.875rem;
    font-weight: 500;
    color: #15803d;
}

.auth-alert svg {
    margin-top: 2px;
    flex-shrink: 0;
}

.auth-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.auth-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
    cursor: pointer;
}

.auth-check input {
    width: 1rem;
    height: 1rem;
    accent-color: #FF7010;
    cursor: pointer;
}

.auth-link {
    padding: 0;
    border: 0;
    background: none;
    font-size: 0.875rem;
    font-weight: 600;
    color: #FF7010;
    text-decoration: none;
    cursor: pointer;
    transition: color 0.2s ease;
}

.auth-link:hover:not(:disabled) {
    color: #E85F00;
    text-decoration: underline;
}

.auth-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.auth-center {
    margin: 0;
    text-align: center;
    font-size: 0.875rem;
    color: #4b5563;
}

.auth-hint {
    margin: 0.5rem 0 0;
    text-align: center;
    font-size: 0.75rem;
    color: #6b7280;
}

.auth-btn {
    position: relative;
    display: inline-flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    overflow: hidden;
    padding: 0.75rem 1.5rem;
    border: 2px solid #FF7010;
    border-radius: 0.5rem;
    background: #FF7010;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.auth-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, 0.35) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.7s ease;
}

.auth-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    background: #E85F00;
    border-color: #E85F00;
    box-shadow: 0 10px 20px rgba(255, 112, 16, 0.4);
}

.auth-btn:hover:not(:disabled)::before {
    transform: translateX(100%);
}

.auth-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 112, 16, 0.3);
}

.auth-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    box-shadow: none;
}

.auth-btn-outline {
    width: auto;
    align-self: flex-start;
    margin-top: 0.75rem;
    padding: 0.5rem 1.25rem;
    background: transparent;
    font-size: 0.875rem;
    color: #FF7010;
    box-shadow: none;
}

.auth-btn-outline::before {
    display: none;
}

.auth-btn-outline:hover:not(:disabled) {
    background: #FF7010;
    border-color: #FF7010;
    color: #fff;
    box-shadow: 0 8px 16px rgba(255, 112, 16, 0.3);
}

.auth-spin {
    animation: auth-spin 1s linear infinite;
}

.auth-rise {
    animation: auth-rise 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes auth-rise {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes auth-spin {
    to { transform: rotate(360deg); }
}

@keyframes auth-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-14px); }
}

@keyframes auth-twinkle {
    0%, 100% { opacity: 0.25; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.25); }
}

@media (prefers-reduced-motion: reduce) {
    .auth-page *,
    .auth-page *::before {
        animation: none !important;
    }
}
</style>