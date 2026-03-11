<template>
    <section class="bg-white py-16 px-6 md:px-12">
        <!-- Heading -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Choose Zodiac Sign</h2>
            <p class="text-gray-600 mt-4">
                Discover your sign and explore daily horoscopes, personality traits, and cosmic guidance.
            </p>
        </div>
        <br><br>

        <!-- Desktop Layout: circular -->
        <div class="hidden md:flex relative justify-center items-center">
            <!-- Zodiac Wheel -->
            <img src="/images/zodiac-wheel2.png" alt="Zodiac Wheel" class="w-80 md:w-96 animate-spin-slow" />

            <!-- Signs positioned around -->
            <div v-for="(sign, index) in signs" :key="sign.name" class="absolute flex flex-col items-center text-center"
                :style="getPositionStyle(index, signs.length)">
                <div class="bg-orange-100 rounded-lg px-3 py-2 shadow hover:shadow-lg transition">
                    <h3 class="font-semibold text-gray-800 text-sm">{{ sign.name }}</h3>
                    <p class="text-xs text-gray-600">{{ sign.range }}</p>
                </div>
            </div>
        </div>

        <!-- Mobile Layout: stacked -->
        <div class="md:hidden flex flex-col items-center space-y-4">
            <!-- First six signs -->
            <div class="grid grid-cols-2 gap-4 w-full">
                <div v-for="sign in signs.slice(0, 6)" :key="sign.name"
                    class="bg-orange-100 rounded-lg px-3 py-2 shadow text-center">
                    <h3 class="font-semibold text-gray-800 text-sm">{{ sign.name }}</h3>
                    <p class="text-xs text-gray-600">{{ sign.range }}</p>
                </div>
            </div>

            <!-- Wheel -->
            <img src="/images/zodiac-wheel2.png" alt="Zodiac Wheel" class="w-64 animate-spin-slow" />

            <!-- Next six signs -->
            <div class="grid grid-cols-2 gap-4 w-full">
                <div v-for="sign in signs.slice(6)" :key="sign.name"
                    class="bg-orange-100 rounded-lg px-3 py-2 shadow text-center">
                    <h3 class="font-semibold text-gray-800 text-sm">{{ sign.name }}</h3>
                    <p class="text-xs text-gray-600">{{ sign.range }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const signs = [
    { name: "Pisces", range: "Feb 19 - Mar 20" },
    { name: "Aquarius", range: "Jan 20 - Feb 18" },
    { name: "Capricorn", range: "Dec 22 - Jan 19" },
    { name: "Sagittarius", range: "Nov 22 - Dec 21" },
    { name: "Scorpio", range: "Oct 23 - Nov 21" },
    { name: "Libra", range: "Sep 23 - Oct 22" },
    { name: "Virgo", range: "Aug 23 - Sep 22" },
    { name: "Leo", range: "Jul 23 - Aug 22" },
    { name: "Cancer", range: "Jun 21 - Jul 22" },
    { name: "Gemini", range: "May 21 - Jun 20" },
    { name: "Taurus", range: "Apr 20 - May 20" },
    { name: "Aries", range: "Mar 21 - Apr 19" },
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

<style scoped>
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 40s linear infinite;
}
</style>
