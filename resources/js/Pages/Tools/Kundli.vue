<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    kundliData: Object,
    filters: Object,
});

const form = useForm({
    name: props.filters?.name ?? 'Aayush',
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
            params: { key: citySearch.value }
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
    form.get(route('tools.kundli'), {
        preserveState: true,
        replace: true,
    });
}

// Compute North Indian Diamond Chart houses (1 to 12)
const chartHouses = computed(() => {
    if (!props.kundliData?.planetary_positions) return {};

    const asc = props.kundliData.planetary_positions.find(p => p.planet === 'Ascendant' || p.planet === 'Ascendant (Lagna)' || p.planet.includes('Ascendant'));
    const ascSignNum = asc?.sign_number ?? 3; // Default 3 (Gemini) for Aayush

    const houses = {};
    for (let h = 1; h <= 12; h++) {
        const rashiNum = ((ascSignNum - 1 + (h - 1)) % 12) + 1;
        const planetsInHouse = props.kundliData.planetary_positions
            .filter(p => p.house === h)
            .map(p => {
                const shortName = p.planet.includes('Ascendant') ? 'Asc' : (p.planet === 'Sun' ? 'Su' : (p.planet === 'Moon' ? 'Mo' : (p.planet === 'Mars' ? 'Ma' : (p.planet === 'Mercury' ? 'Me' : (p.planet === 'Jupiter' ? 'Ju' : (p.planet === 'Venus' ? 'Ve' : (p.planet === 'Saturn' ? 'Sa' : (p.planet === 'Rahu' ? 'Ra' : (p.planet === 'Ketu' ? 'Ke' : p.planet)))))))));
                const degShort = p.degree ? p.degree.split(' ')[0] : '';
                return `${shortName}-${degShort}`;
            });

        houses[h] = {
            rashiNum,
            planets: planetsInHouse
        };
    }
    return houses;
});
</script>

<template>
    <Head title="Vedic Janam Kundli Report" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic Astrology Kundli Report</h1>
                <p class="mt-2 text-sm text-gray-600">Dynamic astrological chart generator with live city autocomplete & North Indian D1 Chart.</p>
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
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Birth Place</label>
                        <input type="text" v-model="citySearch" @input="searchCities" placeholder="Search city..." required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />

                        <ul v-if="showDropdown" class="absolute z-50 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto">
                            <li v-for="city in cityResults" :key="city.id" @click="selectCity(city)"
                                class="px-3 py-2 text-sm hover:bg-orange-50 cursor-pointer border-b last:border-none">
                                <span class="font-bold text-gray-800">{{ city.name }}</span>
                                <span v-if="city.state" class="text-xs text-gray-500 ml-1">({{ city.state }}, {{ city.countryName }})</span>
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
                            {{ kundliData.ayanamsa }} | Lat: {{ kundliData.latitude }} | Lon: {{ kundliData.longitude }}
                        </div>
                    </div>

                    <!-- Panchang Grid -->
                    <div class="mt-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Panchang & Core Details at Birth</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-gray-50 p-4 rounded-lg border">
                            <div><strong class="text-gray-600">Tithi:</strong> {{ kundliData.basic_details.tithi }}</div>
                            <div><strong class="text-gray-600">Karana:</strong> {{ kundliData.basic_details.karan }}</div>
                            <div><strong class="text-gray-600">Yoga:</strong> {{ kundliData.basic_details.yoga }}</div>
                            <div><strong class="text-gray-600">Nakshatra:</strong> {{ kundliData.basic_details.nakshatra }} (Pada {{ kundliData.basic_details.charan }})</div>
                            <div><strong class="text-gray-600">Nakshatra Lord:</strong> {{ kundliData.basic_details.nakshatra_lord }}</div>
                            <div><strong class="text-gray-600">Ascendant (Lagna):</strong> {{ kundliData.basic_details.lagna }}</div>
                            <div><strong class="text-gray-600">Moon Sign (Rashi):</strong> {{ kundliData.basic_details.moon_sign }}</div>
                            <div><strong class="text-gray-600">Sun Sign:</strong> {{ kundliData.basic_details.sun_sign }}</div>
                            <div><strong class="text-gray-600">Sunrise:</strong> {{ kundliData.basic_details.sunrise }}</div>
                            <div><strong class="text-gray-600">Sunset:</strong> {{ kundliData.basic_details.sunset }}</div>
                            <div><strong class="text-gray-600">Gan / Yoni:</strong> {{ kundliData.basic_details.gan }} / {{ kundliData.basic_details.yoni }}</div>
                            <div><strong class="text-gray-600">Nadi / Tatva:</strong> {{ kundliData.basic_details.nadi }} / {{ kundliData.basic_details.tatva }}</div>
                        </div>
                    </div>
                </div>

                <!-- North Indian Lagna Chart & Avakhada Chakra -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- North Indian Diamond Chart SVG -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 flex flex-col items-center">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 self-start w-full pb-2 border-b">Lagna Kundli (D1 Chart)</h3>
                        <svg viewBox="0 0 400 400" class="w-full max-w-sm bg-[#fcf8f2] border-2 border-orange-600 rounded shadow-inner">
                            <!-- Outer square -->
                            <rect x="0" y="0" width="400" height="400" fill="none" stroke="#ea580c" stroke-width="2" />
                            <!-- Diagonals -->
                            <line x1="0" y1="0" x2="400" y2="400" stroke="#ea580c" stroke-width="1.5" />
                            <line x1="400" y1="0" x2="0" y2="400" stroke="#ea580c" stroke-width="1.5" />
                            <!-- Inner diamond box -->
                            <line x1="200" y1="0" x2="400" y2="200" stroke="#ea580c" stroke-width="1.5" />
                            <line x1="400" y1="200" x2="200" y2="400" stroke="#ea580c" stroke-width="1.5" />
                            <line x1="200" y1="400" x2="0" y2="200" stroke="#ea580c" stroke-width="1.5" />
                            <line x1="0" y1="200" x2="200" y2="0" stroke="#ea580c" stroke-width="1.5" />

                            <!-- House 1 (Top Diamond center) - Rashi Number & Planets -->
                            <text x="200" y="30" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[1]?.rashiNum }}</text>
                            <text x="200" y="55" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[1]?.planets" :key="idx" x="200" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 2 (Top Left Triangle) -->
                            <text x="110" y="70" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[2]?.rashiNum }}</text>
                            <text x="110" y="95" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[2]?.planets" :key="idx" x="110" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 3 (Top Left Outer Triangle) -->
                            <text x="50" y="110" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[3]?.rashiNum }}</text>
                            <text x="50" y="135" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[3]?.planets" :key="idx" x="50" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 4 (Left Diamond center) -->
                            <text x="110" y="190" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[4]?.rashiNum }}</text>
                            <text x="110" y="215" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[4]?.planets" :key="idx" x="110" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 5 (Bottom Left Outer Triangle) -->
                            <text x="50" y="275" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[5]?.rashiNum }}</text>
                            <text x="50" y="300" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[5]?.planets" :key="idx" x="50" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 6 (Bottom Left Triangle) -->
                            <text x="110" y="325" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[6]?.rashiNum }}</text>
                            <text x="110" y="350" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[6]?.planets" :key="idx" x="110" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 7 (Bottom Diamond center) -->
                            <text x="200" y="375" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[7]?.rashiNum }}</text>
                            <text x="200" y="350" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[7]?.planets" :key="idx" x="200" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 8 (Bottom Right Triangle) -->
                            <text x="290" y="325" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[8]?.rashiNum }}</text>
                            <text x="290" y="350" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[8]?.planets" :key="idx" x="290" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 9 (Bottom Right Outer Triangle) -->
                            <text x="350" y="275" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[9]?.rashiNum }}</text>
                            <text x="350" y="300" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[9]?.planets" :key="idx" x="350" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 10 (Right Diamond center) -->
                            <text x="290" y="190" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[10]?.rashiNum }}</text>
                            <text x="290" y="215" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[10]?.planets" :key="idx" x="290" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 11 (Top Right Outer Triangle) -->
                            <text x="350" y="110" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[11]?.rashiNum }}</text>
                            <text x="350" y="135" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[11]?.planets" :key="idx" x="350" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>

                            <!-- House 12 (Top Right Triangle) -->
                            <text x="290" y="70" font-size="14" font-weight="bold" fill="#ea580c" text-anchor="middle">{{ chartHouses[12]?.rashiNum }}</text>
                            <text x="290" y="95" font-size="11" font-weight="600" fill="#1f2937" text-anchor="middle">
                                <tspan v-for="(p, idx) in chartHouses[12]?.planets" :key="idx" x="290" :dy="idx === 0 ? 0 : 14">{{ p }}</tspan>
                            </text>
                        </svg>
                        <p class="text-xs text-gray-500 mt-3 text-center">North Indian style D1 Chart. Numbers represent Rashi signs (1=Aries ... 12=Pisces).</p>
                    </div>

                    <!-- Avakhada & Dosha Summary -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Avakhada Details</h3>
                            <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                                <div><strong class="text-gray-600">Varna:</strong> {{ kundliData.basic_details.varna }}</div>
                                <div><strong class="text-gray-600">Vashya:</strong> {{ kundliData.basic_details.vashya }}</div>
                                <div><strong class="text-gray-600">Yoni:</strong> {{ kundliData.basic_details.yoni }}</div>
                                <div><strong class="text-gray-600">Gan:</strong> {{ kundliData.basic_details.gan }}</div>
                                <div><strong class="text-gray-600">Nadi:</strong> {{ kundliData.basic_details.nadi }}</div>
                                <div><strong class="text-gray-600">Paya:</strong> {{ kundliData.basic_details.paya }}</div>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b">Dosha Analysis</h3>
                            <div class="space-y-3 text-sm">
                                <div class="bg-orange-50 p-3 rounded-lg border">
                                    <span class="font-bold text-gray-800">Manglik Dosha:</span>
                                    <span class="ml-2 font-semibold" :class="kundliData.doshas.manglik_dosha.is_present ? 'text-red-600' : 'text-green-600'">
                                        {{ kundliData.doshas.manglik_dosha.status }}
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1">{{ kundliData.doshas.manglik_dosha.description }}</p>
                                </div>
                                <div class="bg-orange-50 p-3 rounded-lg border">
                                    <span class="font-bold text-gray-800">Kalsarpa Dosha:</span>
                                    <span class="ml-2 font-semibold text-red-600">{{ kundliData.doshas.kalsarpa_dosha.type }}</span>
                                    <p class="text-xs text-gray-600 mt-1">{{ kundliData.doshas.kalsarpa_dosha.description }}</p>
                                </div>
                            </div>
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
                                <th class="p-3">Pada</th>
                                <th class="p-3">Nak. Lord</th>
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
                                <td class="p-3 font-semibold text-center">{{ p.pada ?? 1 }}</td>
                                <td class="p-3 text-gray-600">{{ p.nakshatra_lord }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Vimshottari Mahadasha Table -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">Vimshottari Mahadasha Periods</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Mahadasha Planet</th>
                                <th class="p-3">Duration</th>
                                <th class="p-3">Start Date</th>
                                <th class="p-3">End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dasha in kundliData.vimshottari_dasha" :key="dasha.planet" class="border-b hover:bg-gray-50">
                                <td class="p-3 font-bold text-gray-900">{{ dasha.planet }} <span v-if="dasha.active" class="px-2 mx-5 py-0.5 rounded text-xs bg-amber-100 text-amber-800 font-bold">ACTIVE</span></td>
                                <td class="p-3">{{ dasha.duration }}</td>
                                <td class="p-3">{{ dasha.start }}</td>
                                <td class="p-3">{{ dasha.end }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
