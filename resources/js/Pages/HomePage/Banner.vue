<script setup>
import { Icon } from '@iconify/vue'

// decorative twinkling stars
const stars = [
    { top: '10%', left: '6%', size: 6, delay: 0 },
    { top: '22%', left: '44%', size: 4, delay: 0.8 },
    { top: '70%', left: '12%', size: 5, delay: 1.6 },
    { top: '14%', left: '88%', size: 7, delay: 0.4 },
    { top: '82%', left: '58%', size: 4, delay: 2.1 },
    { top: '48%', left: '94%', size: 5, delay: 1.2 },
]
</script>

<template>
    <section class="relative overflow-hidden bg-cream mt-2 py-16 md:py-24 px-6 md:px-12">
        <!-- Background glows -->
        <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-orange-300/30 blur-3xl animate-float-slow"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 h-[28rem] w-[28rem] rounded-full bg-amber-300/30 blur-3xl animate-float"></div>

        <!-- Stars -->
        <span v-for="(s, i) in stars" :key="i" class="pointer-events-none absolute rounded-full bg-primary animate-twinkle"
            :style="{ top: s.top, left: s.left, width: s.size + 'px', height: s.size + 'px', animationDelay: s.delay + 's' }"></span>

        <div class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12">
            <!-- Left Content -->
            <div class="md:w-1/2 space-y-6 text-center md:text-left">
                <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight text-gray-800 animate-fade-up">
                    What's Your
                    <span class="bg-gradient-to-r from-primary to-amber-500 bg-clip-text text-transparent">Sign?</span>
                </h2>

                <p class="text-xl text-gray-700 font-medium animate-fade-up" style="animation-delay: 0.1s">
                    Read Your Daily Horoscope Today
                </p>

                <p class="text-gray-600 animate-fade-up" style="animation-delay: 0.2s">
                    Discover the secrets written in the stars and unlock guidance for love, career, and personal growth.
                    Every zodiac sign carries unique energy — let today’s horoscope inspire your decisions and bring clarity
                    to your journey.
                </p>

                <p class="text-gray-500 animate-fade-up" style="animation-delay: 0.3s">
                    Whether you’re seeking motivation, reassurance, or simply curious about what the universe has planned,
                    our astrologers craft daily insights tailored to your sign. Explore cosmic wisdom, align with your
                    destiny,
                    and embrace the opportunities waiting for you.
                </p>

                <p class="text-gray-500 animate-fade-up" style="animation-delay: 0.4s">
                    Astrology isn’t just prediction — it’s a mirror to your inner self. Step into a world of celestial
                    guidance,
                    where ancient knowledge meets modern life, and let your horoscope be the spark that lights your path
                    today.
                </p>

                <!-- Buttons -->
                <div class="flex flex-wrap justify-center md:justify-start gap-4 pt-2 animate-fade-up"
                    style="animation-delay: 0.5s">
                    <a href="/about-us" class="btn-primary">
                        READ MORE
                        <Icon icon="mdi:arrow-right" width="18" />
                    </a>
                    <a href="/login" class="btn-outline">GET STARTED</a>
                </div>
            </div>

            <!-- Right Zodiac Wheel -->
            <div class="md:w-1/2 flex justify-center animate-fade-up" style="animation-delay: 0.3s">
                <div class="relative animate-float-slow">
                    <div class="absolute inset-0 rounded-full bg-primary/25 blur-3xl animate-pulse-glow"></div>
                    <div class="absolute -inset-5 rounded-full border border-dashed border-orange-300 animate-spin-reverse"></div>
                    <img src="/images/zodiac-wheel.png" alt="Zodiac Wheel"
                        class="relative w-72 sm:w-96 lg:w-[28rem] drop-shadow-2xl animate-spin-slow" />
                </div>
            </div>
        </div>
    </section>
</template>