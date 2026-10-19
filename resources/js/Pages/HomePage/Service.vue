<script setup>
import { ref } from "vue"
import { Icon } from '@iconify/vue';
import Reveal from '@/Components/Reveal.vue'

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
    <section class="relative overflow-hidden bg-cream py-20 px-6 md:px-12 mt-2">
        <div class="pointer-events-none absolute -top-20 right-0 h-72 w-72 rounded-full bg-orange-200/40 blur-3xl"></div>

        <!-- Heading -->
        <Reveal class="relative text-center max-w-2xl mx-auto mb-14">
            <h2 class="section-heading section-heading-accent">Our Services</h2>
            <p class="text-gray-600 mt-6">
                Explore a wide range of astrology and spiritual guidance services designed to bring clarity,
                balance, and positivity into your life. From Vastu Shastra to Tarot Reading, our experts
                combine ancient wisdom with modern insights to help you navigate love, career, health, and destiny.
            </p>
            <p class="text-gray-500 mt-2">
                Whether you seek remedies for Kundli Doshas, personalized birth charts, or mystical readings,
                each service is crafted to empower you with knowledge and confidence. Step into the world of
                astrology and discover the guidance written in the stars.
            </p>
        </Reveal>

        <!-- Services Grid -->
        <div class="relative max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <Reveal v-for="(service, i) in services" :key="service.title" :delay="(i % 4) * 100" class="h-full">
                <div
                    class="group relative h-full overflow-hidden rounded-2xl border border-orange-100 bg-peach p-6 shadow-sm transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-orange-900/10">
                    <!-- top accent bar -->
                    <div
                        class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-primary to-amber-300 transition-transform duration-500 group-hover:scale-x-100">
                    </div>

                    <!-- Icon -->
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary to-orange-400 text-white shadow-lg shadow-orange-500/30 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6">
                        <Icon :icon="service.icon" width="32" height="32" />
                    </div>

                    <!-- Title -->
                    <h3 class="text-xl font-semibold text-gray-800 mb-2 transition-colors group-hover:text-primary">
                        {{ service.title }}
                    </h3>

                    <!-- Description -->
                    <p class="text-gray-600 mb-5">
                        {{ service.description }}
                    </p>

                    <!-- Price -->
                    <a href="#"
                        class="inline-flex items-center gap-1 rounded-full bg-white/70 px-3 py-1 font-semibold text-primary transition group-hover:bg-primary group-hover:text-white">
                        ₹ 500 /-
                        <Icon icon="mdi:arrow-right" width="16"
                            class="transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                </div>
            </Reveal>
        </div>
    </section>
</template>