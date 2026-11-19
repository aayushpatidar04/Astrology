<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    risingSignData: Object,
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
    form.get(route('tools.rising-sign'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Rising Sign (Ascendant) Calculator" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Rising Sign (Ascendant) Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Discover your Vedic Ascendant (Lagna) and detailed personality, health, career &amp; relationship insights.</p>
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
                            {{ form.processing ? 'Calculating...' : 'Calculate Rising Sign' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rising Sign Report -->
            <div v-if="risingSignData" class="space-y-6">
                <!-- Report Header -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4 border-orange-600">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900">
                                {{ risingSignData.native_name }}'s Rising Sign Report
                            </h2>
                            <div class="mt-2">
                                <span class="text-3xl font-black text-orange-600">{{ risingSignData.rising_sign }}</span>
                                <span class="block text-sm font-semibold text-gray-500">{{ risingSignData.rising_sign }} ({{ risingSignData.hindi_name }})</span>
                            </div>
                        </div>

                        <div class="bg-orange-50 border border-orange-200 px-4 py-3 rounded-xl text-xs space-y-1 text-gray-800">
                            <div><strong>Ruling Planet:</strong> {{ risingSignData.ruler }}</div>
                            <div><strong>Element:</strong> {{ risingSignData.element }}</div>
                            <div><strong>Degree:</strong> {{ risingSignData.degree }}</div>
                            <div><strong>Nakshatra:</strong> {{ risingSignData.nakshatra }}</div>
                        </div>
                    </div>

                    <!-- About Your Ascendant -->
                    <div class="mt-6">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2">About Your Ascendant</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.about }}</p>
                        <div class="mt-3 inline-block px-3.5 py-1.5 rounded-lg bg-orange-50 border border-orange-200 text-sm font-bold text-orange-900">
                            Your ascendant is {{ risingSignData.rising_sign }}
                        </div>
                    </div>
                </div>

                <!-- 5 Detailed Sections -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Personality Traits</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.personality }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Physical Traits</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.physical }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Health Insights</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.health }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Career Path</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.career }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Relationships &amp; Love</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ risingSignData.relationships }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
