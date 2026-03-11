<script setup>
import { ref } from "vue"
import { Icon } from '@iconify/vue';

const services = ref([
    {
        title: "Vastu Shastra",
        description: "Balance the energies of your home or workplace with ancient Vastu principles. Create harmony, prosperity, and positive vibrations in every space.",
        icon: "mdi:home",
    },
    {
        title: "Birth Journal",
        description: "Discover the story written in your stars from the moment of birth. A detailed journal reveals planetary positions and their lifelong influence.",
        icon: "mdi-book-open-page-variant",
    },
    {
        title: "Manglik Dosha",
        description: "Understand the effects of Manglik Dosha in your Kundli and explore remedies to ensure peace, stability, and happiness in relationships.",
        icon: "mdi-ring",
    },
    {
        title: "Lal Kitab",
        description: "Unlock practical astrological remedies from the Lal Kitab. Simple yet powerful solutions to overcome challenges in health, wealth, and destiny.",
        icon: "mdi-book",
    },
    {
        title: "Crystal Ball",
        description: "Gain clarity about your future with crystal ball readings. A mystical way to uncover hidden truths and guide your decisions.",
        icon: "mdi-crystal-ball",
    },
    {
        title: "Kundli Dosh",
        description: "Identify and resolve doshas in your Kundli that may affect your life path. Remedies help restore balance and attract positive outcomes.",
        icon: "mdi-sun-moon-stars",
    },
    {
        title: "Tarot Reading",
        description: "Explore answers to your deepest questions through Tarot. Each card reveals insights into love, career, and spiritual growth.",
        icon: "mdi-cards-playing-outline",
    },
    {
        title: "Palm Reading",
        description: "Your palms hold the blueprint of your destiny. Palmistry reveals personality traits, strengths, and future possibilities.",
        icon: "mdi-hand",
    },
])
</script>

<template>
    <section class="bg-[#fdf6f0] py-16 px-6 md:px-12 mt-2">
        <!-- Heading -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">
                Our Services
            </h2>
            <p class="text-gray-600 mt-4">
                Explore a wide range of astrology and spiritual guidance services designed to bring clarity,
                balance, and positivity into your life. From Vastu Shastra to Tarot Reading, our experts
                combine ancient wisdom with modern insights to help you navigate love, career, health, and destiny.
            </p>
            <p class="text-gray-500 mt-2">
                Whether you seek remedies for Kundli Doshas, personalized birth charts, or mystical readings,
                each service is crafted to empower you with knowledge and confidence. Step into the world of
                astrology and discover the guidance written in the stars.
            </p>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div v-for="service in services" :key="service.title"
                class="bg-[#f9e9d9] rounded-lg p-6 shadow hover:shadow-lg transition">
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-orange-500 text-white mb-4">
                    <Icon :icon="service.icon" width="32" height="32" class="mr-1" />
                </div>

                <!-- Title -->
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ service.title }}</h3>

                <!-- Description -->
                <p class="text-gray-600 mb-4">
                    {{ service.description }}
                </p>

                <!-- Read More -->
                <a href="#" class="text-orange-500 font-medium hover:underline">READ MORE</a>
            </div>
        </div>
    </section>
</template>
