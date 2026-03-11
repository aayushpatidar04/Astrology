<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import { Icon } from "@iconify/vue"

const testimonials = [
    {
        photo: "/images/rahul-sharma.png",
        text: "Astrology gave me clarity and confidence in my decisions. The guidance was truly life-changing.",
        name: "Rahul Sharma",
        profession: "Entrepreneur",
    },
    {
        photo: "/images/priya-verma.png",
        text: "I was amazed at how accurate the readings were. It helped me understand myself better.",
        name: "Priya Verma",
        profession: "Teacher",
    },
    {
        photo: "/images/amit-patel.png",
        text: "The astrologers are very professional and insightful. Highly recommended!",
        name: "Amit Patel",
        profession: "Software Engineer",
    },
    {
        photo: "/images/sneha-iyer.png",
        text: "I found peace of mind and direction through their services. Truly grateful.",
        name: "Sneha Iyer",
        profession: "Doctor",
    },
]

const currentIndex = ref(0)
const totalSlides = testimonials.length / 2 // showing 2 at a time

const visibleTestimonials = computed(() => {
    const start = currentIndex.value * 2
    return testimonials.slice(start, start + 2)
})

// Auto-slide every 4 seconds
let intervalId = null
onMounted(() => {
    intervalId = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % totalSlides
    }, 4000)
})
onUnmounted(() => {
    clearInterval(intervalId)
})
</script>

<template>
    <section class="py-16 px-6 md:px-12">
        <!-- Heading -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">What Our Customers Say</h2>
            <p class="text-gray-600 mt-4">
                Hear from our clients across India who have experienced the guidance and wisdom of astrology.
            </p>
        </div>

        <!-- Testimonials Carousel -->
        <div class="relative max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-for="(testimonial, index) in visibleTestimonials" :key="index"
                    class="bg-[#fdf6f0] rounded-lg shadow p-6 flex flex-col items-center text-center">
                    <!-- Photo -->
                    <img :src="testimonial.photo" alt="Customer photo"
                        class="w-20 h-20 rounded-full mb-4 object-cover" />

                    <!-- Quote Icon -->
                    <Icon icon="mdi:format-quote-open" width="28" height="28" class="text-orange-500 mb-2" />

                    <!-- Text -->
                    <p class="text-gray-600 mb-4">{{ testimonial.text }}</p>

                    <!-- Name & Profession -->
                    <h3 class="font-semibold text-gray-800">{{ testimonial.name }}</h3>
                    <p class="text-sm text-gray-500">{{ testimonial.profession }}</p>
                </div>
            </div>

            <!-- Dots -->
            <div class="flex justify-center mt-6 space-x-2">
                <span v-for="(dot, i) in totalSlides" :key="i" class="w-3 h-3 rounded-full"
                    :class="currentIndex === i ? 'bg-orange-500' : 'bg-gray-300'"></span>
            </div>
        </div>
    </section>
</template>
