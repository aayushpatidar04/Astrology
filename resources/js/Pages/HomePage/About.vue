<script setup>
import { ref, onMounted, onUnmounted } from "vue"
import { Icon } from '@iconify/vue';
import Reveal from '@/Components/Reveal.vue'

const images = [
    "/images/about1.jpg",
    "/images/about2.jpg",
    "/images/about3.jpg",
    "/images/about4.jpg",
]

const currentIndex = ref(0)
let intervalId = null

function start() {
    stop()
    intervalId = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % images.length
    }, 3500)
}

function stop() {
    clearInterval(intervalId)
}

onMounted(start)
onUnmounted(stop)
</script>
<template>
    <section class="bg-white py-16 px-6 md:px-12">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12">
            <!-- Left Carousel -->
            <Reveal direction="left" class="md:w-1/3 w-full" @mouseenter="stop" @mouseleave="start">
                <div class="relative mx-auto aspect-[4/5] w-full max-w-sm">
                    <!-- offset frame -->
                    <div class="absolute inset-0 translate-x-3 translate-y-3 rounded-2xl border-2 border-primary/30"></div>

                    <div class="relative h-full w-full overflow-hidden rounded-2xl shadow-xl shadow-orange-900/10">
                        <img v-for="(src, i) in images" :key="src" :src="src" alt="Astrology"
                            class="absolute inset-0 h-full w-full object-cover transition-all duration-1000 ease-out"
                            :class="i === currentIndex ? 'opacity-100 scale-100' : 'opacity-0 scale-110'" />
                    </div>

                    <!-- dots -->
                    <div class="absolute -bottom-7 left-0 right-0 flex justify-center gap-2">
                        <button v-for="(img, i) in images" :key="i" @click="currentIndex = i" :aria-label="`Show image ${i + 1}`"
                            class="h-2 rounded-full transition-all duration-300"
                            :class="currentIndex === i ? 'w-6 bg-primary' : 'w-2 bg-gray-300 hover:bg-orange-300'"></button>
                    </div>
                </div>
            </Reveal>

            <!-- Right Content -->
            <div class="md:w-2/3 mt-8 md:mt-0 md:pl-8 space-y-6 text-center md:text-left">
                <Reveal direction="right">
                    <h2 class="section-heading">Know About Astrology</h2>
                    <div class="mt-3 mx-auto md:mx-0 h-1 w-16 rounded-full bg-gradient-to-r from-primary to-amber-300"></div>
                </Reveal>
                <Reveal direction="right" :delay="100">
                    <p class="text-gray-600 leading-relaxed">
                        Astrology is more than predictions — it’s a guide to self-discovery. Explore how the stars
                        influence your personality, relationships, and future. Our expert astrologers bring ancient
                        wisdom into modern life, helping you align with cosmic energy.
                    </p>
                </Reveal>
                <Reveal direction="right" :delay="200">
                    <p class="text-gray-500 leading-relaxed">
                        From zodiac insights to personalized horoscopes, astrology offers clarity and direction.
                        Whether you’re curious about love, career, or health, the universe has messages waiting for
                        you. Let us help you uncover them.
                    </p>
                </Reveal>

                <!-- Buttons -->
                <Reveal direction="right" :delay="300" class="flex justify-center md:justify-start">
                    <a href="/about-us" class="btn-primary">
                        READ MORE
                        <Icon icon="mdi:arrow-right" width="18" />
                    </a>
                </Reveal>

                <!-- Contact -->
                <Reveal direction="right" :delay="400">
                    <a href="tel:+911800124105"
                        class="group inline-flex items-center gap-4 rounded-2xl border border-orange-100 bg-orange-50/60 p-4 text-left text-gray-700 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-orange-900/10">
                        <span class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-orange-100">
                            <span class="absolute inset-0 rounded-full bg-primary/30 animate-ping-soft"></span>
                            <Icon icon="mdi:phone-in-talk-outline" width="28" height="28"
                                class="relative text-primary transition-transform duration-300 group-hover:scale-110" />
                        </span>
                        <span class="text-sm">
                            Contact Our Expert Astrologers:<br>
                            <span class="text-lg text-primary font-bold tracking-wide">+ (91) 9691-565-883</span>
                        </span>
                    </a>
                </Reveal>
            </div>
        </div>
    </section>
</template>