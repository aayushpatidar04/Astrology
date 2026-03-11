<script setup>
import { ref, onMounted, onUnmounted } from "vue"
import { Icon } from '@iconify/vue';

const images = [
    "/images/about1.jpg",
    "/images/about2.jpg",
    "/images/about3.jpg",
    "/images/about4.jpg",
]

const currentIndex = ref(0)
let intervalId = null

onMounted(() => {
    intervalId = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % images.length
    }, 2000)
})

onUnmounted(() => {
    clearInterval(intervalId)
})

</script>
<template>
    <section class="bg-white py-12 px-6 md:px-12 flex flex-col md:flex-row items-center mx-10">
        <!-- Left Carousel -->
        <div class="md:w-1/3 relative">
            <div class="overflow-hidden rounded-lg shadow-lg">
                <img :src="images[currentIndex]" alt="Astrology"
                    class="w-auto h-100 object-cover transition-transform duration-700" />
            </div>
        </div>

        <!-- Right Content -->
        <div class="md:w-2/3 mt-8 md:mt-0 md:pl-12 space-y-6 text-center md:text-left">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">
                Know About Astrology
            </h2>
            <p class="text-gray-600">
                Astrology is more than predictions — it’s a guide to self-discovery. Explore how the stars
                influence your personality, relationships, and future. Our expert astrologers bring ancient
                wisdom into modern life, helping you align with cosmic energy.
            </p>
            <p class="text-gray-500">
                From zodiac insights to personalized horoscopes, astrology offers clarity and direction.
                Whether you’re curious about love, career, or health, the universe has messages waiting for
                you. Let us help you uncover them.
            </p>

            <!-- Buttons -->
            <div class="flex justify-center md:justify-start space-x-4">
                <button class="px-5 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                    READ MORE
                </button>
            </div>

            <!-- Contact -->
            <div class="p-5 rounded-lg">
                <div class="flex items-center justify-center md:justify-start space-x-2 text-gray-700">
                    <div class="bg-orange-100 p-2" style="border-radius: 50px;">
                        <Icon icon="mdi:phone-in-talk-outline" width="50" height="50" class="mr-1" style="color: #FF7010;" />
                    </div>
                    <span style="font-family:'Courier New', Courier, monospace;">Contact Our Expert Astrologers:<br><span class="text-primary font-bold"> + (91) 1800-124-105</span></span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.text-primary {
    color: #FF7010;
}
</style>
