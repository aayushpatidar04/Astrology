<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    manglikData: Object,
    filters: Object,
});

const form = useForm({
    name: props.filters?.name ?? 'Aayush Patidar',
    gender: props.filters?.gender ?? 'male',
    dob: props.filters?.dob ?? '2000-05-04',
    tob: props.filters?.tob ?? '09:05',
    birth_place: props.filters?.birth_place ?? 'Chhoti Sadri, Rajasthan, India',
    lat: props.filters?.lat ?? 24.3815,
    lng: props.filters?.lng ?? 74.7012,
});

const citySearch = ref(form.birth_place);
const cityResults = ref([]);
const showDropdown = ref(false);

async function searchCities() {
    if (citySearch.value.length < 2) {
        cityResults.value = [];
        showDropdown.value = false;
        return;
    }

    try {
        const res = await axios.get(route('api.cities.autocomplete'), {
            params: { key: citySearch.value },
        });
        cityResults.value = res.data?.data ?? [];
        showDropdown.value = cityResults.value.length > 0;
    } catch (e) {
        cityResults.value = [];
    }
}

function selectCity(city) {
    const parts = [city.name, city.state, city.countryName || 'India'].filter(Boolean);
    const fullName = parts.join(', ');
    citySearch.value = fullName;
    form.birth_place = fullName;
    form.lat = city.latitude;
    form.lng = city.longitude;
    showDropdown.value = false;
}

function submit() {
    form.get(route('tools.manglik'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Manglik Dosha Calculator - Kuja Dosha &amp; Cancellation Check" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Manglik Dosha (Kuja Dosha) Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Check Manglik status across Lagna, Moon &amp; Venus charts along with Vedic cancellation rules &amp; remedies.</p>
            </div>

            <!-- Input Form -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-10 border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Enter your name"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Gender</label>
                        <select
                            v-model="form.gender"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        >
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date of Birth</label>
                        <input
                            type="date"
                            v-model="form.dob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Time of Birth</label>
                        <input
                            type="time"
                            v-model="form.tob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div class="relative md:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Birth Place</label>
                        <input
                            type="text"
                            v-model="citySearch"
                            @input="searchCities"
                            placeholder="Search birth city..."
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />

                        <ul
                            v-if="showDropdown"
                            class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto"
                        >
                            <li
                                v-for="city in cityResults"
                                :key="city.id"
                                @click="selectCity(city)"
                                class="px-3 py-2 text-sm hover:bg-orange-50 cursor-pointer border-b last:border-none"
                            >
                                <span class="font-bold text-gray-800">{{ city.name }}</span>
                                <span v-if="city.state" class="text-xs text-gray-500 ml-1">({{ city.state }}, {{ city.countryName }})</span>
                            </li>
                        </ul>
                    </div>

                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{ form.processing ? 'Checking Chart...' : 'Check Manglik Dosha' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manglik Dosha Report -->
            <div v-if="manglikData" class="space-y-6">
                <!-- Status Hero Card -->
                <div
                    class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4"
                    :class="manglikData.is_manglik ? 'border-amber-600' : 'border-green-600'"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900">
                                {{ manglikData.native_name }}'s Manglik Dosha Report
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                Born on {{ manglikData.dob }} at {{ manglikData.tob }} in {{ manglikData.birth_place }}
                            </p>
                        </div>

                        <div
                            class="px-5 py-2.5 rounded-xl border text-center shrink-0"
                            :class="manglikData.is_manglik ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-green-50 border-green-300 text-green-900'"
                        >
                            <div class="text-xs font-bold uppercase">Result</div>
                            <div class="text-lg font-black">{{ manglikData.status }}</div>
                        </div>
                    </div>

                    <p class="mt-5 text-sm text-gray-800 leading-relaxed font-medium">
                        {{ manglikData.verdict }}
                    </p>

                    <!-- 3-Chart Mars Placement Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                        <div class="p-4 rounded-xl bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">From Lagna (Ascendant)</div>
                            <div class="text-lg font-extrabold text-gray-900 mt-1">
                                House {{ manglikData.mars_details.house_from_lagna }}
                            </div>
                            <div
                                class="text-xs font-semibold mt-0.5"
                                :class="manglikData.mars_details.lagna_manglik ? 'text-amber-700' : 'text-green-700'"
                            >
                                {{ manglikData.mars_details.lagna_manglik ? 'Manglik Position' : 'Non-Manglik Position' }}
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">From Moon (Chandra Kundli)</div>
                            <div class="text-lg font-extrabold text-gray-900 mt-1">
                                House {{ manglikData.mars_details.house_from_moon }}
                            </div>
                            <div
                                class="text-xs font-semibold mt-0.5"
                                :class="manglikData.mars_details.moon_manglik ? 'text-amber-700' : 'text-green-700'"
                            >
                                {{ manglikData.mars_details.moon_manglik ? 'Manglik Position' : 'Non-Manglik Position' }}
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">From Venus (Shukra Kundli)</div>
                            <div class="text-lg font-extrabold text-gray-900 mt-1">
                                House {{ manglikData.mars_details.house_from_venus }}
                            </div>
                            <div
                                class="text-xs font-semibold mt-0.5"
                                :class="manglikData.mars_details.venus_manglik ? 'text-amber-700' : 'text-green-700'"
                            >
                                {{ manglikData.mars_details.venus_manglik ? 'Manglik Position' : 'Non-Manglik Position' }}
                            </div>
                        </div>
                    </div>

                    <!-- Mars Summary Details -->
                    <div class="mt-6 pt-5 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 bg-gray-50 rounded-lg border">
                            <span class="text-gray-500 block">Mars Sign</span>
                            <strong class="text-gray-900 text-sm">{{ manglikData.mars_details.rashi }} ({{ manglikData.mars_details.degree }})</strong>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border">
                            <span class="text-gray-500 block">Mars Nakshatra</span>
                            <strong class="text-gray-900 text-sm">{{ manglikData.mars_details.nakshatra }}</strong>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border">
                            <span class="text-gray-500 block">Ascendant (Lagna)</span>
                            <strong class="text-gray-900 text-sm">{{ manglikData.mars_details.lagna_sign }}</strong>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border">
                            <span class="text-gray-500 block">Moon Sign</span>
                            <strong class="text-gray-900 text-sm">{{ manglikData.mars_details.moon_sign }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Detailed Effect & Cancellations -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Astrological Effect &amp; House Impact</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ manglikData.effect_note }}</p>
                    </div>

                    <div v-if="manglikData.triggers.length > 0">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Detected Chart Factors</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            <li v-for="trigger in manglikData.triggers" :key="trigger">{{ trigger }}</li>
                        </ul>
                    </div>

                    <div v-if="manglikData.cancellations.length > 0">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100 text-green-700">Classical Cancellations &amp; Neutralizing Factors (Mangal Dosha Bhanga)</h3>
                        <ul class="list-disc list-inside text-sm text-green-800 space-y-1 bg-green-50/50 p-4 rounded-xl border border-green-100">
                            <li v-for="cancel in manglikData.cancellations" :key="cancel">{{ cancel }}</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Recommended Vedic Remedies</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            <li v-for="remedy in manglikData.remedies" :key="remedy">{{ remedy }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
