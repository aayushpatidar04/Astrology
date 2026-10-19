<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import { Icon } from "@iconify/vue"
import Reveal from '@/Components/Reveal.vue'

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
const totalSlides = Math.ceil(testimonials.length / 2) // showing 2 at a time

const visibleTestimonials = computed(() => {
    const start = currentIndex.value * 2
    return testimonials.slice(start, start + 2)
})

function next() {
    currentIndex.value = (currentIndex.value + 1) % totalSlides
}

function prev() {
    currentIndex.value = (currentIndex.value - 1 + totalSlides) % totalSlides
}

// Auto-slide every 5 seconds (pauses on hover)
let intervalId = null
function start() {
    stop()
    intervalId = setInterval(next, 5000)
}
function stop() {
    clearInterval(intervalId)
}

onMounted(start)
onUnmounted(stop)
</script>

<template>
    <section class="py-20 px-6 md:px-12 overflow-hidden">
        <!-- Heading -->
        <Reveal class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="section-heading section-heading-accent">What Our Customers Say</h2>
            <p class="text-gray-600 mt-6">
                Hear from our clients across India who have experienced the guidance and wisdom of astrology.
            </p>
        </Reveal>

        <!-- Testimonials Carousel -->
        <Reveal :delay="100" class="relative max-w-5xl mx-auto" @mouseenter="stop" @mouseleave="start">
            <Transition name="slide" mode="out-in">
                <div :key="currentIndex" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="testimonial in visibleTestimonials" :key="testimonial.name"
                        class="relative flex flex-col items-center rounded-2xl border border-orange-100 bg-cream p-8 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-900/10">
                        <!-- big faded quote mark -->
                        <Icon icon="mdi:format-quote-close" width="72" height="72"
                            class="absolute top-4 right-4 text-primary/10" />

                        <!-- Photo -->
                        <img :src="testimonial.photo" alt="Customer photo"
                            class="mb-4 h-20 w-20 rounded-full object-cover ring-4 ring-orange-200 ring-offset-2" />

                        <!-- Quote Icon -->
                        <Icon icon="mdi:format-quote-open" width="28" height="28" class="text-primary mb-2" />

                        <!-- Text -->
                        <p class="text-gray-600 mb-4 leading-relaxed">{{ testimonial.text }}</p>

                        <!-- Name & Profession -->
                        <h3 class="font-semibold text-gray-800">{{ testimonial.name }}</h3>
                        <p class="text-sm text-gray-500">{{ testimonial.profession }}</p>
                    </div>
                </div>
            </Transition>

            <!-- Controls -->
            <div class="mt-8 flex items-center justify-center gap-4">
                <button @click="prev" aria-label="Previous testimonials"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-orange-200 text-primary transition duration-300 hover:bg-primary hover:text-white hover:shadow-md hover:shadow-orange-500/30">
                    <Icon icon="mdi:chevron-left" width="22" />
                </button>

                <div class="flex space-x-2">
                    <button v-for="(dot, i) in totalSlides" :key="i" @click="currentIndex = i"
                        :aria-label="`Go to slide ${i + 1}`" class="h-3 rounded-full transition-all duration-300"
                        :class="currentIndex === i ? 'w-8 bg-primary' : 'w-3 bg-gray-300 hover:bg-orange-300'"></button>
                </div>

                <button @click="next" aria-label="Next testimonials"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-orange-200 text-primary transition duration-300 hover:bg-primary hover:text-white hover:shadow-md hover:shadow-orange-500/30">
                    <Icon icon="mdi:chevron-right" width="22" />
                </button>
            </div>
        </Reveal>
    </section>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: opacity 0.45s ease, transform 0.45s ease;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>