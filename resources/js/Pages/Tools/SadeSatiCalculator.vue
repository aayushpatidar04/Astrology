<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    sadeSatiData: Object,
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
    form.get(route('tools.sade-sati'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Shani Sade Sati Calculator - Phase Status &amp; Remedies" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Shani Sade Sati Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Check your live Saturn transit phase (Rising, Peak, or Setting), 7.5-year phase timeline &amp; remedies.</p>
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
                            {{ form.processing ? 'Checking Transit...' : 'Check Sade Sati' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sade Sati Report -->
            <div v-if="sadeSatiData" class="space-y-6">
                <!-- Status Card -->
                <div
                    class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4"
                    :class="sadeSatiData.is_active ? 'border-amber-600' : 'border-green-600'"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900">
                                {{ sadeSatiData.native_name }}'s Shani Sade Sati Report
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                Born on {{ sadeSatiData.dob }} at {{ sadeSatiData.tob }} in {{ sadeSatiData.birth_place }}
                            </p>
                        </div>

                        <div
                            class="px-5 py-2.5 rounded-xl border text-center shrink-0"
                            :class="sadeSatiData.is_active ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-green-50 border-green-300 text-green-900'"
                        >
                            <div class="text-xs font-bold uppercase">Current Status</div>
                            <div class="text-base font-black">{{ sadeSatiData.status }}</div>
                        </div>
                    </div>

                    <!-- Natal vs Transit Summary -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 text-xs">
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Natal Moon Sign (Rashi)</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ sadeSatiData.moon_sign }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Birth Nakshatra</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ sadeSatiData.nakshatra }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Live Transit Saturn</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">{{ sadeSatiData.transit_saturn_sign }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-gray-500 block font-semibold">Saturn House from Moon</span>
                            <strong class="text-gray-900 text-sm mt-0.5 block">House {{ sadeSatiData.house_from_moon }}</strong>
                        </div>
                    </div>

                    <div class="mt-5 p-4 rounded-xl bg-orange-50/70 border border-orange-100">
                        <h3 class="text-sm font-extrabold text-orange-900 mb-1">{{ sadeSatiData.phase_title }}</h3>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ sadeSatiData.phase_description }}</p>
                    </div>
                </div>

                <!-- 3 Phases of Sade Sati -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-6">
                    <h3 class="text-xl font-extrabold text-gray-900 pb-2 border-b">3 Phases of Your 7.5-Year Sade Sati Cycle</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="p in sadeSatiData.phases"
                            :key="p.phase"
                            class="p-5 rounded-2xl border transition relative"
                            :class="p.is_current ? 'bg-orange-50 border-orange-300 shadow-sm ring-2 ring-orange-500' : 'bg-gray-50 border-gray-200'"
                        >
                            <div v-if="p.is_current" class="absolute -top-3 right-4 px-2.5 py-0.5 rounded-full bg-orange-600 text-white text-[10px] font-extrabold uppercase tracking-wide">
                                Current Phase
                            </div>
                            <h4 class="text-sm font-black text-gray-900 mb-1">{{ p.phase }}</h4>
                            <div class="text-xs font-bold text-orange-700 mb-2">{{ p.transit }}</div>
                            <div class="text-xs font-semibold text-gray-600 mb-3">📅 {{ p.start }} – {{ p.end }}</div>
                            <p class="text-xs text-gray-700 leading-relaxed">{{ p.focus }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 pb-1.5 border-b border-gray-100">Recommended Shani Remedies &amp; Guidance</h3>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            <li v-for="rem in sadeSatiData.remedies" :key="rem">{{ rem }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
