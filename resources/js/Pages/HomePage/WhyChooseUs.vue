<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Reveal from '@/Components/Reveal.vue'

const stats = [
    { value: 512, suffix: "+", label: "Qualified Astrologers" },
    { value: 62, suffix: "+", label: "Success Horoscope" },
    { value: 94, suffix: "+", label: "Offices Worldwide" },
    { value: 452, suffix: "+", label: "Trusted by Million Clients" },
    { value: 12, suffix: "+", label: "Years of Experience" },
    { value: 652, suffix: "+", label: "Types of Horoscopes" },
]

const sectionRef = ref(null)
const displayed = ref(stats.map(() => 0))
let observer = null

// count-up animation, triggered once when the section scrolls into view
function countUp() {
    const duration = 1600
    const startTime = performance.now()

    const tick = (now) => {
        const progress = Math.min((now - startTime) / duration, 1)
        const eased = 1 - Math.pow(1 - progress, 3) // ease-out cubic
        displayed.value = stats.map((s) => Math.round(s.value * eased))
        if (progress < 1) requestAnimationFrame(tick)
    }

    requestAnimationFrame(tick)
}

onMounted(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (!('IntersectionObserver' in window) || reduceMotion) {
        displayed.value = stats.map((s) => s.value)
        return
    }

    observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            countUp()
            observer.disconnect()
        }
    }, { threshold: 0.3 })

    observer.observe(sectionRef.value)
})

onBeforeUnmount(() => observer?.disconnect())
</script>

<template>
    <section ref="sectionRef" class="relative overflow-hidden bg-cream py-20 px-6 md:px-12">
        <div class="pointer-events-none absolute -top-24 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-orange-200/40 blur-3xl"></div>

        <!-- Heading -->
        <Reveal class="relative text-center max-w-2xl mx-auto mb-14">
            <h2 class="section-heading section-heading-accent">Why Choose Us</h2>
            <p class="text-gray-600 mt-6">
                Trusted by millions, our astrology services combine experience, accuracy, and global reach.
            </p>
        </Reveal>

        <!-- Stats Grid -->
        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 max-w-5xl mx-auto">
            <Reveal v-for="(item, index) in stats" :key="index" direction="scale" :delay="(index % 3) * 120">
                <div
                    class="group mx-auto aspect-square w-full max-w-[17rem] rounded-full bg-gradient-to-br from-primary to-amber-300 p-1 shadow-lg shadow-orange-900/10 transition duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-orange-500/30">
                    <div class="flex h-full w-full flex-col items-center justify-center rounded-full bg-white p-6 text-center">
                        <div class="mb-2 text-4xl font-extrabold text-primary tabular-nums transition-transform duration-300 group-hover:scale-110">
                            {{ displayed[index] }}{{ item.suffix }}
                        </div>
                        <p class="text-gray-700 font-medium leading-snug">{{ item.label }}</p>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>