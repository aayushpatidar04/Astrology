<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    choghadiyaData: Object,
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
const detectingGps = ref(false);

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
    submit();
}

function useCurrentLocation() {
    if (!navigator.geolocation) return;
    detectingGps.value = true;

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.lat = Number(pos.coords.latitude.toFixed(4));
            form.lng = Number(pos.coords.longitude.toFixed(4));
            form.city = `Current Location (${form.lat}, ${form.lng})`;
            citySearch.value = form.city;
            detectingGps.value = false;
            submit();
        },
        () => {
            detectingGps.value = false;
        }
    );
}

function submit() {
    form.get(route('tools.choghadiya'), {
        preserveState: true,
        replace: true,
    });
}

function badgeClass(type) {
    if (type === 'good') return 'bg-green-100 text-green-800 border-green-200';
    if (type === 'neutral') return 'bg-sky-100 text-sky-800 border-sky-200';
    return 'bg-red-100 text-red-800 border-red-200';
}
</script>

<template>
    <Head title="Today's Choghadiya Muhurat Timetable" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daily Choghadiya Muhurat Timetable</h1>
                <p class="mt-2 text-sm text-gray-600">Accurate Day &amp; Night Choghadiya timings calculated from exact Sunrise &amp; Sunset for your location.</p>
            </div>

            <!-- Date & Location Filter Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8 border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Select Date</label>
                        <input
                            type="date"
                            v-model="form.date"
                            @change="submit"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div class="relative md:col-span-5">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Location / City</label>
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

                    <div class="md:col-span-2">
                        <button
                            type="button"
                            @click="useCurrentLocation"
                            :disabled="detectingGps"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-3 rounded-lg border border-gray-300 transition text-xs"
                        >
                            {{ detectingGps ? 'Locating...' : '📍 Use My GPS' }}
                        </button>
                    </div>

                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{ form.processing ? 'Loading...' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="choghadiyaData" class="space-y-8">
                <!-- Current Active Choghadiya Spotlight Card -->
                <div
                    v-if="choghadiyaData.current_choghadiya"
                    class="rounded-2xl shadow-lg p-6 sm:p-8 border-2 transition"
                    :class="choghadiyaData.current_choghadiya.type === 'good'
                        ? 'bg-gradient-to-r from-emerald-50 to-green-50 border-green-400'
                        : (choghadiyaData.current_choghadiya.type === 'neutral'
                            ? 'bg-gradient-to-r from-sky-50 to-blue-50 border-sky-400'
                            : 'bg-gradient-to-r from-red-50 to-amber-50 border-red-400')"
                >
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-white shadow-sm text-gray-900">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-ping"></span>
                                    Current Active Choghadiya ({{ choghadiyaData.current_choghadiya.period }})
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold border"
                                    :class="badgeClass(choghadiyaData.current_choghadiya.type)"
                                >
                                    {{ choghadiyaData.current_choghadiya.nature }}
                                </span>
                            </div>

                            <h2 class="text-3xl font-black text-gray-900 mt-3">
                                {{ choghadiyaData.current_choghadiya.name }} Choghadiya
                                <span class="text-base font-semibold text-gray-600">
                                    (Ruled by {{ choghadiyaData.current_choghadiya.ruler }})
                                </span>
                            </h2>

                            <p class="text-sm text-gray-700 mt-1.5">
                                {{ choghadiyaData.current_choghadiya.activity }}
                            </p>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200 text-center shrink-0 min-w-[210px]">
                            <div class="text-xs font-bold uppercase text-gray-500">Active Time Window</div>
                            <div class="text-base font-extrabold font-mono text-gray-900 mt-1">
                                {{ choghadiyaData.current_choghadiya.time_range }}
                            </div>
                            <div class="mt-2 inline-block px-3 py-0.5 rounded-full bg-orange-100 text-orange-800 text-xs font-bold">
                                {{ choghadiyaData.current_choghadiya.minutes_left }} mins remaining
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sunrise / Sunset Info Strip -->
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div>
                        <strong class="text-gray-900 text-sm">{{ choghadiyaData.date }} ({{ choghadiyaData.day_name }})</strong>
                        <span class="text-gray-500 ml-2">• {{ choghadiyaData.city }} ({{ choghadiyaData.latitude }}, {{ choghadiyaData.longitude }})</span>
                    </div>
                    <div class="flex flex-wrap gap-4 font-semibold text-gray-700">
                        <span>🌅 Sunrise: {{ choghadiyaData.sunrise }}</span>
                        <span>🌇 Sunset: {{ choghadiyaData.sunset }}</span>
                        <span>🌅 Next Sunrise: {{ choghadiyaData.next_sunrise }}</span>
                    </div>
                </div>

                <!-- Day & Night Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Day Choghadiya -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">☀️ Day Choghadiya</h3>
                                <p class="text-xs text-gray-500">Sunrise ({{ choghadiyaData.sunrise }}) to Sunset ({{ choghadiyaData.sunset }})</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-800 text-xs font-bold">8 Periods</span>
                        </div>

                        <div class="overflow-x-auto border rounded-xl">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700">
                                        <th class="p-2.5">#</th>
                                        <th class="p-2.5">Choghadiya</th>
                                        <th class="p-2.5">Timings</th>
                                        <th class="p-2.5">Nature</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="slot in choghadiyaData.day_choghadiya"
                                        :key="slot.slot_number"
                                        class="border-b hover:bg-gray-50 transition"
                                        :class="slot.is_current ? 'bg-orange-50 ring-2 ring-orange-500 font-bold' : ''"
                                    >
                                        <td class="p-2.5 font-bold text-gray-500">{{ slot.slot_number }}</td>
                                        <td class="p-2.5">
                                            <div class="font-black text-gray-900 text-sm flex items-center gap-2">
                                                {{ slot.name }}
                                                <span v-if="slot.is_current" class="px-1.5 py-0.5 rounded bg-orange-600 text-white text-[9px] uppercase font-extrabold">Active</span>
                                            </div>
                                            <div class="text-[11px] text-gray-500">{{ slot.activity }}</div>
                                        </td>
                                        <td class="p-2.5 font-mono text-xs whitespace-nowrap">{{ slot.time_range }}</td>
                                        <td class="p-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border" :class="badgeClass(slot.type)">
                                                {{ slot.nature }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Night Choghadiya -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">🌙 Night Choghadiya</h3>
                                <p class="text-xs text-gray-500">Sunset ({{ choghadiyaData.sunset }}) to Next Sunrise ({{ choghadiyaData.next_sunrise }})</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-orange-800 text-xs font-bold">8 Periods</span>
                        </div>

                        <div class="overflow-x-auto border rounded-xl">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700">
                                        <th class="p-2.5">#</th>
                                        <th class="p-2.5">Choghadiya</th>
                                        <th class="p-2.5">Timings</th>
                                        <th class="p-2.5">Nature</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="slot in choghadiyaData.night_choghadiya"
                                        :key="slot.slot_number"
                                        class="border-b hover:bg-gray-50 transition"
                                        :class="slot.is_current ? 'bg-orange-50 ring-2 ring-orange-500 font-bold' : ''"
                                    >
                                        <td class="p-2.5 font-bold text-gray-500">{{ slot.slot_number }}</td>
                                        <td class="p-2.5">
                                            <div class="font-black text-gray-900 text-sm flex items-center gap-2">
                                                {{ slot.name }}
                                                <span v-if="slot.is_current" class="px-1.5 py-0.5 rounded bg-orange-600 text-white text-[9px] uppercase font-extrabold">Active</span>
                                            </div>
                                            <div class="text-[11px] text-gray-500">{{ slot.activity }}</div>
                                        </td>
                                        <td class="p-2.5 font-mono text-xs whitespace-nowrap">{{ slot.time_range }}</td>
                                        <td class="p-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border" :class="badgeClass(slot.type)">
                                                {{ slot.nature }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
