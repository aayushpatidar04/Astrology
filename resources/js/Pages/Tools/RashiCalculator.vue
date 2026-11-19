<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    rashiData: Object,
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
    form.get(route('tools.rashi'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Rashi Calculator - Vedic Moon Sign Finder" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Rashi (Moon Sign) Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Enter your birth details to find your exact Vedic Janma Rashi &amp; Nakshatra.</p>
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
                            {{ form.processing ? 'Calculating...' : 'Calculate Rashi' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rashi Report Output -->
            <div v-if="rashiData" class="space-y-6">
                <!-- Header Card -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4 border-orange-600">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900">
                                {{ rashiData.native_name }}'s Rashi (Moon Sign) Report
                            </h2>
                            <div class="mt-2">
                                <span class="text-3xl font-black text-orange-600">{{ rashiData.rashi }}</span>
                                <span class="block text-sm font-semibold text-gray-500">{{ rashiData.rashi }} ({{ rashiData.hindi_name }})</span>
                            </div>
                        </div>

                        <div class="bg-orange-50 border border-orange-200 px-4 py-3 rounded-xl text-xs space-y-1 text-gray-800">
                            <div><strong>Ruling Planet:</strong> {{ rashiData.ruler }}</div>
                            <div><strong>Element:</strong> {{ rashiData.element }}</div>
                            <div><strong>Nakshatra:</strong> {{ rashiData.nakshatra }}</div>
                            <div><strong>Moon Degree:</strong> {{ rashiData.degree }}</div>
                        </div>
                    </div>

                    <!-- About Your Rashi -->
                    <div class="mt-6">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2">About Your Janma Rashi</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ rashiData.about }}</p>
                        <div class="mt-3 inline-block px-3.5 py-1.5 rounded-lg bg-orange-50 border border-orange-200 text-sm font-bold text-orange-900">
                            Your Rashi is {{ rashiData.rashi }} ({{ rashiData.hindi_name }})
                        </div>
                    </div>

                    <!-- Auspicious Summary Strip -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-gray-100 text-xs">
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Name Syllables (Namakshar)</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ rashiData.syllables }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Lucky Colors</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ rashiData.lucky_color }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Recommended Gemstone</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ rashiData.lucky_stone }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Detailed Sections -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Personality &amp; Emotional Nature</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ rashiData.personality }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Career &amp; Strengths</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ rashiData.career }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Relationships &amp; Compatibility</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ rashiData.relationships }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
