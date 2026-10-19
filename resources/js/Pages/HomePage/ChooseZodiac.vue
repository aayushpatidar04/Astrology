<script setup>
import Reveal from '@/Components/Reveal.vue'

// "\uFE0E" forces the plain-text glyph so phones don't swap in colour emoji
const signs = [
    { name: "Pisces", range: "Feb 19 - Mar 20", symbol: "♓\uFE0E" },
    { name: "Aquarius", range: "Jan 20 - Feb 18", symbol: "♒\uFE0E" },
    { name: "Capricorn", range: "Dec 22 - Jan 19", symbol: "♑\uFE0E" },
    { name: "Sagittarius", range: "Nov 22 - Dec 21", symbol: "♐\uFE0E" },
    { name: "Scorpio", range: "Oct 23 - Nov 21", symbol: "♏\uFE0E" },
    { name: "Libra", range: "Sep 23 - Oct 22", symbol: "♎\uFE0E" },
    { name: "Virgo", range: "Aug 23 - Sep 22", symbol: "♍\uFE0E" },
    { name: "Leo", range: "Jul 23 - Aug 22", symbol: "♌\uFE0E" },
    { name: "Cancer", range: "Jun 21 - Jul 22", symbol: "♋\uFE0E" },
    { name: "Gemini", range: "May 21 - Jun 20", symbol: "♊\uFE0E" },
    { name: "Taurus", range: "Apr 20 - May 20", symbol: "♉\uFE0E" },
    { name: "Aries", range: "Mar 21 - Apr 19", symbol: "♈\uFE0E" },
]

// Function to calculate circular positions (desktop only)
function getPositionStyle(index, total) {
    const angle = (index / total) * 2 * Math.PI
    const radius = 240 // smaller radius so it fits better
    const x = Math.cos(angle) * radius
    const y = Math.sin(angle) * radius
    return {
        transform: `translate(${x}px, ${y}px)`,
    }
}
</script>

<template>
    <section class="relative overflow-hidden bg-white py-20 px-6 md:px-12">
        <!-- Heading -->
        <Reveal class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="section-heading section-heading-accent">Choose Zodiac Sign</h2>
            <p class="text-gray-600 mt-6">
                Discover your sign and explore daily horoscopes, personality traits, and cosmic guidance.
            </p>
        </Reveal>

        <!-- Desktop Layout: circular -->
        <div class="hidden md:flex relative justify-center items-center h-[40rem]">
            <!-- Wheel with glow -->
            <Reveal direction="scale" class="relative">
                <div class="absolute inset-0 rounded-full bg-primary/20 blur-3xl animate-pulse-glow"></div>
                <img src="/images/zodiac-wheel2.png" alt="Zodiac Wheel"
                    class="relative w-80 md:w-96 drop-shadow-xl animate-spin-slower" />
            </Reveal>

            <!-- Signs positioned around -->
            <div v-for="(sign, index) in signs" :key="sign.name" class="absolute flex flex-col items-center text-center"
                :style="getPositionStyle(index, signs.length)">
                <Reveal direction="scale" :delay="index * 70">
                    <div
                        class="group cursor-pointer rounded-xl border border-orange-200 bg-orange-100 px-4 py-2 shadow transition duration-300 hover:-translate-y-1 hover:scale-110 hover:border-primary hover:bg-primary hover:shadow-xl hover:shadow-orange-500/30">
                        <span class="block text-lg leading-none text-primary transition-colors group-hover:text-white">{{ sign.symbol }}</span>
                        <h3 class="font-semibold text-gray-800 text-sm transition-colors group-hover:text-white">{{ sign.name }}</h3>
                        <p class="text-xs text-gray-600 transition-colors group-hover:text-orange-50">{{ sign.range }}</p>
                    </div>
                </Reveal>
            </div>
        </div>

        <!-- Mobile Layout: stacked -->
        <div class="md:hidden flex flex-col items-center space-y-6">
            <!-- First six signs -->
            <div class="grid grid-cols-2 gap-4 w-full">
                <Reveal v-for="(sign, i) in signs.slice(0, 6)" :key="sign.name" direction="scale" :delay="(i % 2) * 80">
                    <div class="rounded-xl border border-orange-200 bg-orange-100 px-3 py-2 text-center shadow transition duration-300 active:scale-95">
                        <span class="block text-lg leading-none text-primary">{{ sign.symbol }}</span>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ sign.name }}</h3>
                        <p class="text-xs text-gray-600">{{ sign.range }}</p>
                    </div>
                </Reveal>
            </div>

            <!-- Wheel -->
            <div class="relative">
                <div class="absolute inset-0 rounded-full bg-primary/20 blur-2xl animate-pulse-glow"></div>
                <img src="/images/zodiac-wheel2.png" alt="Zodiac Wheel" class="relative w-64 animate-spin-slower" />
            </div>

            <!-- Next six signs -->
            <div class="grid grid-cols-2 gap-4 w-full">
                <Reveal v-for="(sign, i) in signs.slice(6)" :key="sign.name" direction="scale" :delay="(i % 2) * 80">
                    <div class="rounded-xl border border-orange-200 bg-orange-100 px-3 py-2 text-center shadow transition duration-300 active:scale-95">
                        <span class="block text-lg leading-none text-primary">{{ sign.symbol }}</span>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ sign.name }}</h3>
                        <p class="text-xs text-gray-600">{{ sign.range }}</p>
                    </div>
                </Reveal>
            </div>
        </div>
    </section>
</template>