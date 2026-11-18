<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    panchangData: Object,
    filters: Object,
});

const form = useForm({
    date: props.filters?.date ?? new Date().toISOString().slice(0, 10),
    city: props.filters?.city ?? 'Jaipur, Rajasthan, India',
    lat: props.filters?.lat ?? 26.9124,
    lng: props.filters?.lng ?? 75.7873,
});

const citySearch = ref(form.city);
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
    form.city = fullName;
    form.lat = city.latitude;
    form.lng = city.longitude;
    showDropdown.value = false;
}

function submit() {
    form.get(route('tools.panchang'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Daily Hindu Panchang & Muhurat" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daily Hindu Panchang</h1>
                <p class="mt-2 text-sm text-gray-600">Accurate Tithi, Nakshatra, Yoga, Karana, Sunrise/Sunset &amp; Rahu Kaal Timings</p>
            </div>

            <!-- Date & Location Filter Form -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Select Date</label>
                        <input
                            type="date"
                            v-model="form.date"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Location / City</label>
                        <input
                            type="text"
                            v-model="citySearch"
                            @input="searchCities"
                            placeholder="Search city..."
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

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{ form.processing ? 'Calculating...' : 'Get Panchang' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Panchang Output -->
            <div v-if="panchangData" class="space-y-8">
                <!-- Header Banner -->
                <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-orange-600">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ panchangData.date }}</h2>
                            <p class="text-sm text-orange-700 font-semibold mt-0.5">{{ panchangData.day }} • {{ panchangData.city }}</p>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <div class="bg-amber-50 px-4 py-2 rounded-lg border border-amber-200">
                                <span class="text-xs text-amber-800 block uppercase font-bold">Sunrise</span>
                                <span class="font-extrabold text-gray-900">{{ panchangData.sunrise }}</span>
                            </div>
                            <div class="bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-200">
                                <span class="text-xs text-indigo-800 block uppercase font-bold">Sunset</span>
                                <span class="font-extrabold text-gray-900">{{ panchangData.sunset }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Five Limbs (Panchang Elements) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6">
                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Tithi (Lunar Day)</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.tithi }}</div>
                            <div class="text-xs text-orange-600 mt-0.5">Elapsed: {{ panchangData.tithi_pct }}</div>
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Nakshatra (Constellation)</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.nakshatra }}</div>
                            <div class="text-xs text-gray-600 mt-0.5">Lord: {{ panchangData.nakshatra_lord }}</div>
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Yoga</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.yoga }}</div>
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Karana</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.karana }}</div>
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Moon Sign (Chandra Rashi)</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.moon_sign }}</div>
                        </div>

                        <div class="p-4 rounded-lg bg-gray-50 border">
                            <div class="text-xs font-bold uppercase text-gray-500">Sun Sign (Surya Rashi)</div>
                            <div class="text-base font-extrabold text-gray-900 mt-1">{{ panchangData.sun_sign }}</div>
                        </div>
                    </div>
                </div>

                <!-- Auspicious & Inauspicious Timings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Auspicious Muhurats -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-600">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Auspicious Timings (Shubh Muhurat)</h3>
                        <div class="space-y-3">
                            <div
                                v-for="item in panchangData.auspicious_timings"
                                :key="item.name"
                                class="p-3.5 rounded-lg bg-green-50 border border-green-200"
                            >
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-green-900 text-sm">{{ item.name }}</span>
                                    <span class="font-mono font-bold text-green-800 text-xs bg-white px-2.5 py-1 rounded border border-green-200">
                                        {{ item.time }}
                                    </span>
                                </div>
                                <p class="text-xs text-green-700 mt-1.5">{{ item.note }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Inauspicious Muhurats -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-red-600">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Inauspicious Timings (Ashubh Kaal)</h3>
                        <div class="space-y-3">
                            <div
                                v-for="item in panchangData.inauspicious_timings"
                                :key="item.name"
                                class="p-3.5 rounded-lg bg-red-50 border border-red-200"
                            >
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-red-900 text-sm">{{ item.name }}</span>
                                    <span class="font-mono font-bold text-red-800 text-xs bg-white px-2.5 py-1 rounded border border-red-200">
                                        {{ item.time }}
                                    </span>
                                </div>
                                <p class="text-xs text-red-700 mt-1.5">{{ item.note }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
