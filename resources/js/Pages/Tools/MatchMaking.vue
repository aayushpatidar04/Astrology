<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    milanData: Object,
    filters: Object,
});

const form = useForm({
    boy_name: props.filters?.boy_name ?? '',
    boy_dob: props.filters?.boy_dob ?? '1998-08-15',
    boy_tob: props.filters?.boy_tob ?? '10:30',
    boy_birth_place: props.filters?.boy_birth_place ?? 'Jaipur, Rajasthan, India',
    boy_lat: props.filters?.boy_lat ?? 26.9124,
    boy_lng: props.filters?.boy_lng ?? 75.7873,

    girl_name: props.filters?.girl_name ?? '',
    girl_dob: props.filters?.girl_dob ?? '2000-05-04',
    girl_tob: props.filters?.girl_tob ?? '09:05',
    girl_birth_place: props.filters?.girl_birth_place ?? 'Chhoti Sadri, Rajasthan, India',
    girl_lat: props.filters?.girl_lat ?? 24.3815,
    girl_lng: props.filters?.girl_lng ?? 74.7012,
});

// Boy City Autocomplete State
const boyCitySearch = ref(form.boy_birth_place);
const boyCityResults = ref([]);
const showBoyDropdown = ref(false);

// Girl City Autocomplete State
const girlCitySearch = ref(form.girl_birth_place);
const girlCityResults = ref([]);
const showGirlDropdown = ref(false);

async function searchCities(type) {
    const query = type === 'boy' ? boyCitySearch.value : girlCitySearch.value;

    if (query.length < 2) {
        if (type === 'boy') {
            boyCityResults.value = [];
            showBoyDropdown.value = false;
        } else {
            girlCityResults.value = [];
            showGirlDropdown.value = false;
        }
        return;
    }

    try {
        const res = await axios.get(route('api.cities.autocomplete'), {
            params: { key: query },
        });
        const results = res.data?.data ?? [];

        if (type === 'boy') {
            boyCityResults.value = results;
            showBoyDropdown.value = results.length > 0;
        } else {
            girlCityResults.value = results;
            showGirlDropdown.value = results.length > 0;
        }
    } catch (e) {
        if (type === 'boy') boyCityResults.value = [];
        else girlCityResults.value = [];
    }
}

function selectCity(type, city) {
    const parts = [city.name, city.state, city.countryName || 'India'].filter(Boolean);
    const fullName = parts.join(', ');

    if (type === 'boy') {
        boyCitySearch.value = fullName;
        form.boy_birth_place = fullName;
        form.boy_lat = city.latitude;
        form.boy_lng = city.longitude;
        showBoyDropdown.value = false;
    } else {
        girlCitySearch.value = fullName;
        form.girl_birth_place = fullName;
        form.girl_lat = city.latitude;
        form.girl_lng = city.longitude;
        showGirlDropdown.value = false;
    }
}

function submit() {
    form.get(route('tools.matchmaking'), {
        preserveState: true,
        replace: true,
    });
}

const scorePercentage = computed(() => {
    if (!props.milanData) return 0;
    return Math.round((props.milanData.total_guna / props.milanData.max_guna) * 100);
});

const scoreBadgeClass = computed(() => {
    if (!props.milanData) return 'bg-gray-100 text-gray-800';
    const score = props.milanData.total_guna;
    if (score >= 25) return 'bg-green-100 text-green-800 border-green-300';
    if (score >= 18) return 'bg-amber-100 text-amber-800 border-amber-300';
    return 'bg-red-100 text-red-800 border-red-300';
});
</script>

<template>
    <Head title="Kundli Match Making - Ashtakoota Guna Milan" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic Kundli Match Making</h1>
                <p class="mt-2 text-sm text-gray-600">Complete 36-Guna Ashtakoota Milan & Manglik Compatibility Analysis</p>
            </div>

            <!-- Dual Partner Input Form -->
            <form @submit.prevent="submit" class="space-y-6 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Boy's Birth Details Card -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-600">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b">
                            <h2 class="text-lg font-bold text-gray-900">Boy's Birth Details</h2>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded bg-blue-50 text-blue-700">Groom</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Full Name</label>
                                <input
                                    type="text"
                                    v-model="form.boy_name"
                                    placeholder="Enter boy's name"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Date of Birth</label>
                                    <input
                                        type="date"
                                        v-model="form.boy_dob"
                                        required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Time of Birth</label>
                                    <input
                                        type="time"
                                        v-model="form.boy_tob"
                                        required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Birth Place</label>
                                <input
                                    type="text"
                                    v-model="boyCitySearch"
                                    @input="searchCities('boy')"
                                    placeholder="Search boy's birth city..."
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                />

                                <ul
                                    v-if="showBoyDropdown"
                                    class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto"
                                >
                                    <li
                                        v-for="city in boyCityResults"
                                        :key="city.id"
                                        @click="selectCity('boy', city)"
                                        class="px-3 py-2 text-sm hover:bg-blue-50 cursor-pointer border-b last:border-none"
                                    >
                                        <span class="font-bold text-gray-800">{{ city.name }}</span>
                                        <span v-if="city.state" class="text-xs text-gray-500 ml-1">({{ city.state }}, {{ city.countryName }})</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Girl's Birth Details Card -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-pink-600">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b">
                            <h2 class="text-lg font-bold text-gray-900">Girl's Birth Details</h2>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded bg-pink-50 text-pink-700">Bride</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Full Name</label>
                                <input
                                    type="text"
                                    v-model="form.girl_name"
                                    placeholder="Enter girl's name"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Date of Birth</label>
                                    <input
                                        type="date"
                                        v-model="form.girl_dob"
                                        required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Time of Birth</label>
                                    <input
                                        type="time"
                                        v-model="form.girl_tob"
                                        required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Birth Place</label>
                                <input
                                    type="text"
                                    v-model="girlCitySearch"
                                    @input="searchCities('girl')"
                                    placeholder="Search girl's birth city..."
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500"
                                />

                                <ul
                                    v-if="showGirlDropdown"
                                    class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto"
                                >
                                    <li
                                        v-for="city in girlCityResults"
                                        :key="city.id"
                                        @click="selectCity('girl', city)"
                                        class="px-3 py-2 text-sm hover:bg-pink-50 cursor-pointer border-b last:border-none"
                                    >
                                        <span class="font-bold text-gray-800">{{ city.name }}</span>
                                        <span v-if="city.state" class="text-xs text-gray-500 ml-1">({{ city.state }}, {{ city.countryName }})</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition text-base"
                    >
                        {{ form.processing ? 'Matching Kundlis...' : 'Match Kundli Now' }}
                    </button>
                </div>
            </form>

            <!-- Matchmaking Results Display -->
            <div v-if="milanData" class="space-y-8">
                <!-- Score Summary Card -->
                <div class="bg-white rounded-xl shadow-md p-8 border border-gray-100 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Ashtakoota Horoscope Compatibility Report</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ milanData.boy.name }} & {{ milanData.girl.name }}</p>

                    <div class="inline-flex items-center justify-center w-36 h-36 rounded-full bg-orange-50 border-4 border-orange-500 text-4xl font-extrabold text-orange-600 mb-4 shadow-inner">
                        {{ milanData.total_guna }} <span class="text-sm text-gray-400 font-normal">/{{ milanData.max_guna }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="scoreBadgeClass">
                            {{ scorePercentage }}% Match
                        </span>
                    </div>

                    <p class="text-base font-semibold text-gray-800 max-w-2xl mx-auto">{{ milanData.conclusion }}</p>
                </div>

                <!-- Partner Comparison Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Groom Summary -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-600">
                        <h4 class="font-bold text-gray-900 text-base mb-3">{{ milanData.boy.name }} (Groom)</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong class="text-gray-500">Moon Sign (Rashi):</strong> {{ milanData.boy.moon_sign }}</div>
                            <div><strong class="text-gray-500">Nakshatra:</strong> {{ milanData.boy.nakshatra }}</div>
                            <div><strong class="text-gray-500">Manglik Status:</strong> <span class="font-semibold">{{ milanData.boy.manglik }}</span></div>
                        </div>
                    </div>

                    <!-- Bride Summary -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-pink-600">
                        <h4 class="font-bold text-gray-900 text-base mb-3">{{ milanData.girl.name }} (Bride)</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong class="text-gray-500">Moon Sign (Rashi):</strong> {{ milanData.girl.moon_sign }}</div>
                            <div><strong class="text-gray-500">Nakshatra:</strong> {{ milanData.girl.nakshatra }}</div>
                            <div><strong class="text-gray-500">Manglik Status:</strong> <span class="font-semibold">{{ milanData.girl.manglik }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Manglik Compatibility Verdict -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Manglik Milan Status</h3>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded text-xs font-bold" :class="milanData.manglik_comparison.is_compatible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            {{ milanData.manglik_comparison.is_compatible ? 'COMPATIBLE' : 'MISMATCH WARNING' }}
                        </span>
                        <p class="text-sm text-gray-600">
                            {{ milanData.manglik_comparison.is_compatible
                                ? 'Both charts show harmonious Mars placement or natural Manglik cancellation.'
                                : 'One partner has Manglik Dosha while the other does not. Astrological guidance is recommended.' }}
                        </p>
                    </div>
                </div>

                <!-- Detailed Ashta Koota Table -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">Ashtakoota 8-Point Breakdown</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Koota</th>
                                <th class="p-3">Boy Attribute</th>
                                <th class="p-3">Girl Attribute</th>
                                <th class="p-3">Obtained</th>
                                <th class="p-3">Max</th>
                                <th class="p-3">Area of Compatibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="k in milanData.kootas" :key="k.name" class="border-b hover:bg-gray-50">
                                <td class="p-3 font-bold text-gray-900">{{ k.name }}</td>
                                <td class="p-3 text-gray-700">{{ k.boy }}</td>
                                <td class="p-3 text-gray-700">{{ k.girl }}</td>
                                <td class="p-3 font-semibold text-orange-600">{{ k.score }}</td>
                                <td class="p-3 text-gray-500">{{ k.max }}</td>
                                <td class="p-3 text-gray-600 text-xs">{{ k.description }}</td>
                            </tr>
                            <tr class="bg-orange-50 font-bold">
                                <td colspan="3" class="p-3 text-right">Total Ashtakoota Score:</td>
                                <td class="p-3 text-orange-600">{{ milanData.total_guna }}</td>
                                <td class="p-3">{{ milanData.max_guna }}</td>
                                <td class="p-3 text-xs text-gray-500">{{ milanData.total_guna >= 18 ? 'Minimum 18 Gunas Requirement Passed' : 'Below 18 Gunas Threshold' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
