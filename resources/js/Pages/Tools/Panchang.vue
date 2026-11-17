<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
    panchangData: Object,
})
</script>

<template>
    <Head title="Today's Hindu Panchang" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">Daily Hindu Panchang</h1>
                <p class="mt-2 text-sm text-gray-600">Auspicious timings, Tithi, Nakshatra, Yoga, and Rahu Kaal for today.</p>
            </div>

            <div v-if="panchangData" class="space-y-8">
                <!-- Today Summary Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 text-center">
                    <h2 class="text-2xl font-bold text-orange-600">{{ panchangData.day }}, {{ panchangData.date }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <span class="text-xs text-gray-500 uppercase">Sunrise</span>
                            <p class="text-base font-bold text-gray-800">{{ panchangData.sunrise }}</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <span class="text-xs text-gray-500 uppercase">Sunset</span>
                            <p class="text-base font-bold text-gray-800">{{ panchangData.sunset }}</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <span class="text-xs text-gray-500 uppercase">Tithi</span>
                            <p class="text-base font-bold text-gray-800">{{ panchangData.tithi }}</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <span class="text-xs text-gray-500 uppercase">Nakshatra</span>
                            <p class="text-base font-bold text-gray-800">{{ panchangData.nakshatra }}</p>
                        </div>
                    </div>
                </div>

                <!-- Auspicious & Inauspicious Timings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Auspicious -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-green-100">
                        <h3 class="text-lg font-bold text-green-700 mb-4 pb-2 border-b">✨ Auspicious Timings (Shubh Muhurat)</h3>
                        <ul class="space-y-3">
                            <li v-for="item in panchangData.auspicious_timings" :key="item.name" class="flex justify-between items-center bg-green-50 p-3 rounded-lg">
                                <span class="font-medium text-gray-800">{{ item.name }}</span>
                                <span class="font-bold text-green-700">{{ item.time }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Inauspicious -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-red-100">
                        <h3 class="text-lg font-bold text-red-700 mb-4 pb-2 border-b">⚠️ Inauspicious Timings (Avoid)</h3>
                        <ul class="space-y-3">
                            <li v-for="item in panchangData.inauspicious_timings" :key="item.name" class="flex justify-between items-center bg-red-50 p-3 rounded-lg">
                                <span class="font-medium text-gray-800">{{ item.name }}</span>
                                <span class="font-bold text-red-700">{{ item.time }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
