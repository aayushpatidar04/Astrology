<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    muhuratData: Object,
    filters: Object,
});

const form = useForm({
    event_type: props.filters?.event_type ?? 'business',
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
    form.get(route('tools.muhurat'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Auspicious Muhurat Finder for Business &amp; Ceremonies" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Auspicious Muhurat Finder</h1>
                <p class="mt-2 text-sm text-gray-600">Select an event type, date, and location to find the top auspicious time windows for your new beginnings.</p>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8 border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Event Type</label>
                        <select
                            v-model="form.event_type"
                            @change="submit"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        >
                            <option value="business">New Business &amp; Investment</option>
                            <option value="property">Buying Property / Griha Pravesh</option>
                            <option value="vehicle">Vehicle Purchase &amp; Travel</option>
                            <option value="marriage">Marriage &amp; Engagement</option>
                            <option value="general">General Auspicious Start</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Select Date</label>
                        <input
                            type="date"
                            v-model="form.date"
                            @change="submit"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>

                    <div class="relative md:col-span-4">
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

                    <div class="md:col-span-1">
                        <button
                            type="button"
                            @click="useCurrentLocation"
                            :disabled="detectingGps"
                            title="Use GPS"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-2 rounded-lg border border-gray-300 transition text-xs"
                        >
                            {{ detectingGps ? '...' : '📍 GPS' }}
                        </button>
                    </div>

                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{ form.processing ? 'Scanning...' : 'Find Muhurat' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div v-if="muhuratData" class="space-y-8">
                <!-- Header Hero Card -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4 border-orange-600 space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-orange-600">Auspicious Time Windows</span>
                            <h2 class="text-2xl font-extrabold text-gray-900 mt-0.5">
                                {{ muhuratData.event_type }} Muhurat for {{ muhuratData.date }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                Location: {{ muhuratData.city }} | Sunrise: {{ muhuratData.sunrise }} | Sunset: {{ muhuratData.sunset }}
                            </p>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 px-4 py-3 rounded-xl text-xs space-y-1 text-gray-800 shrink-0">
                            <div><strong>Tithi:</strong> {{ muhuratData.panchang.tithi }}</div>
                            <div><strong>Nakshatra:</strong> {{ muhuratData.panchang.nakshatra }}</div>
                            <div><strong>Yoga:</strong> {{ muhuratData.panchang.yoga }}</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-orange-50/70 border border-orange-100 text-sm text-orange-900 font-medium">
                        💡 <strong>Expert Guidance:</strong> {{ muhuratData.general_advice }}
                    </div>
                </div>

                <!-- Windows List -->
                <div class="space-y-4">
                    <h3 class="text-xl font-extrabold text-gray-900">Top Favorable Time Windows</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="win in muhuratData.windows"
                            :key="win.time_range"
                            class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex flex-col justify-between space-y-4 hover:shadow-lg transition"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        {{ win.nature }}
                                    </span>
                                    <h4 class="text-lg font-black text-gray-900 mt-2">{{ win.window_name }}</h4>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-black text-orange-600">{{ win.score }}%</div>
                                    <div class="text-[10px] uppercase font-bold text-gray-400">Suitability</div>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-gray-50 border font-mono font-bold text-sm text-gray-900 text-center">
                                ⏰ {{ win.time_range }}
                            </div>

                            <p class="text-xs text-gray-700 leading-relaxed">
                                {{ win.recommendation }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
