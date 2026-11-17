<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    kundliData: Object,
    filters: Object,
})

const form = useForm({
    name: props.filters?.name ?? 'Aayush',
    dob: props.filters?.dob ?? '2000-05-04',
    tob: props.filters?.tob ?? '09:05',
    birth_place: props.filters?.birth_place ?? 'Chhoti Sadri, Rajasthan, India',
    lat: props.filters?.lat ?? 24.3815,
    lng: props.filters?.lng ?? 74.7012,
})

const citySearch = ref(form.birth_place)
const cityResults = ref([])
const showDropdown = ref(false)

async function searchCities() {
    if (citySearch.value.length < 2) {
        cityResults.value = []
        showDropdown.value = false
        return
    }

    try {
        const res = await axios.get(route('api.cities.autocomplete'), {
            params: { key: citySearch.value }
        })
        cityResults.value = res.data?.data ?? []
        showDropdown.value = cityResults.value.length > 0
    } catch (e) {
        cityResults.value = []
    }
}

function selectCity(city) {
    const fullName = `${city.name}, ${city.state || ''}, ${city.countryName || 'India'}`;
    citySearch.value = fullName
    form.birth_place = fullName
    form.lat = city.latitude
    form.lng = city.longitude
    showDropdown.value = false
}

function submit() {
    form.get(route('tools.kundli'), {
        preserveState: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="Vedic Janam Kundli Report" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic Astrology Kundli Report</h1>
                <p class="mt-2 text-sm text-gray-600">Dynamic astrological chart generator with live city autocomplete & Swiss Ephemeris math.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100 relative">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Full Name</label>
                        <input type="text" v-model="form.name" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Date of Birth</label>
                        <input type="date" v-model="form.dob" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Time of Birth</label>
                        <input type="time" v-model="form.tob" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <!-- City Autocomplete Input -->
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Birth Place</label>
                        <input type="text" v-model="citySearch" @input="searchCities" placeholder="Search city..." required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />

                        <!-- Dropdown Results -->
                        <ul v-if="showDropdown" class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto">
                            <li v-for="city in cityResults" :key="city.id" @click="selectCity(city)"
                                class="px-3 py-2 text-sm hover:bg-orange-50 cursor-pointer border-b last:border-none">
                                <span class="font-bold text-gray-800">{{ city.name }}</span>
                                <span class="text-xs text-gray-500 ml-1">({{ city.state }}, {{ city.countryName }})</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            {{ form.processing ? 'Calculating...' : 'Generate Kundli' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Display -->
            <div v-if="kundliData" class="space-y-8">
                <!-- Birth Details & Panchang -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 border-b gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ kundliData.native_name }}'s Vedic Kundli Report</h2>
                            <p class="text-xs text-gray-500 mt-1">DOB: {{ kundliData.dob }} | TOB: {{ kundliData.tob }} | Place: {{ kundliData.birth_place }}</p>
                        </div>
                        <div class="bg-orange-50 px-4 py-2 rounded-lg border border-orange-200 text-xs font-semibold text-orange-800">
                            Ayanamsa: {{ kundliData.ayanamsa }} | Lat: {{ kundliData.latitude }} | Lon: {{ kundliData.longitude }}
                        </div>
                    </div>

                    <!-- Panchang Grid -->
                    <div class="mt-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Panchang at Birth</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-gray-50 p-4 rounded-lg border">
                            <div><strong class="text-gray-600">Tithi:</strong> {{ kundliData.basic_details.tithi }}</div>
                            <div><strong class="text-gray-600">Karana:</strong> {{ kundliData.basic_details.karan }}</div>
                            <div><strong class="text-gray-600">Yoga:</strong> {{ kundliData.basic_details.yoga }}</div>
                            <div><strong class="text-gray-600">Nakshatra:</strong> {{ kundliData.basic_details.nakshatra }}</div>
                            <div><strong class="text-gray-600">Nakshatra Lord:</strong> {{ kundliData.basic_details.nakshatra_lord }}</div>
                            <div><strong class="text-gray-600">Ascendant:</strong> {{ kundliData.basic_details.lagna }}</div>
                            <div><strong class="text-gray-600">Sunrise:</strong> {{ kundliData.basic_details.sunrise }}</div>
                            <div><strong class="text-gray-600">Sunset:</strong> {{ kundliData.basic_details.sunset }}</div>
                        </div>
                    </div>
                </div>

                <!-- Planetary Positions Table -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">Planetary Positions (Nirayana)</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Planet</th>
                                <th class="p-3">Sign</th>
                                <th class="p-3">Sign Lord</th>
                                <th class="p-3">House</th>
                                <th class="p-3">Degree</th>
                                <th class="p-3">Nakshatra</th>
                                <th class="p-3">Nak. Lord</th>
                                <th class="p-3">Retro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in kundliData.planetary_positions" :key="p.planet" class="border-b hover:bg-gray-50">
                                <td class="p-3 font-bold text-gray-900">{{ p.planet }}</td>
                                <td class="p-3">{{ p.sign }}</td>
                                <td class="p-3 text-gray-600">{{ p.sign_lord }}</td>
                                <td class="p-3 font-semibold text-orange-600">{{ p.house }}</td>
                                <td class="p-3 font-mono text-xs">{{ p.degree }}</td>
                                <td class="p-3">{{ p.nakshatra }}</td>
                                <td class="p-3 text-gray-600">{{ p.nakshatra_lord }}</td>
                                <td class="p-3 text-gray-600">{{ p.status }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
