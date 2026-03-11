<script setup>
const stats = [
    { value: "512+", label: "Qualified Astrologers" },
    { value: "62+", label: "Success Horoscope" },
    { value: "94+", label: "Offices Worldwide" },
    { value: "452+", label: "Trusted by Million Clients" },
    { value: "12+", label: "Years of Experience" },
    { value: "652+", label: "Types of Horoscopes" },
]
</script>

<template>
    <section class="bg-[#fdf6f0] py-16 px-6 md:px-12">
        <!-- Heading -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Why Choose Us</h2>
            <p class="text-gray-600 mt-4">
                Trusted by millions, our astrology services combine experience, accuracy, and global reach.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div v-for="(item, index) in stats" :key="index"
                class="bg-white rounded-full shadow-lg p-6 flex flex-col items-center justify-center text-center border-4 border-orange-500">
                <div class="text-3xl font-bold text-orange-500 mb-2">{{ item.value }}</div>
                <p class="text-gray-700 font-medium">{{ item.label }}</p>
            </div>
        </div>
    </section>
</template>
