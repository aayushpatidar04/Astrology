<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    dashaData: Object,
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
const expandedPlanet = ref(props.dashaData?.current_mahadasha?.planet ?? null);

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

function toggleExpand(planet) {
    expandedPlanet.value = expandedPlanet.value === planet ? null : planet;
}

function submit() {
    form.get(route('tools.dasha'), {
        preserveState: true,
        replace: true,
        onSuccess: () => {
            expandedPlanet.value = props.dashaData?.current_mahadasha?.planet ?? null;
        },
    });
}
</script>

<template>
    <Head title="Vimshottari Dasha Calculator - Mahadasha, Antardasha &amp; Remedies" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vimshottari Dasha Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Complete 120-year Mahadasha &amp; Antardasha timeline with life possibilities, positive strategies &amp; Vedic remedies.</p>
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
                            {{ form.processing ? 'Calculating Dasha...' : 'Calculate Dasha Report' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Dasha Report -->
            <div v-if="dashaData" class="space-y-8">
                <!-- Profile & Current Running Dasha Hero Card -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border-t-4 border-orange-600 space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-5 border-b">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900">{{ dashaData.native_name }}'s Vimshottari Dasha Report</h2>
                            <p class="text-xs text-gray-500 mt-1">
                                Born {{ dashaData.dob }} at {{ dashaData.tob }} • {{ dashaData.birth_place }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <span class="px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-200 text-orange-900">
                                Lagna: {{ dashaData.lagna }}
                            </span>
                            <span class="px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                                Rashi: {{ dashaData.moon_sign }}
                            </span>
                            <span class="px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900">
                                Nakshatra: {{ dashaData.nakshatra }}
                            </span>
                        </div>
                    </div>

                    <!-- Current Active Mahadasha & Antardasha Analytics -->
                    <div v-if="dashaData.current_mahadasha" class="space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-orange-600 to-amber-600 text-white p-5 rounded-xl shadow">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-orange-100">Currently Running Planetary Period</div>
                                <div class="text-2xl font-black mt-0.5">
                                    {{ dashaData.current_mahadasha.planet }} Mahadasha
                                    <span v-if="dashaData.current_antardasha" class="text-lg font-bold text-orange-100">
                                        / {{ dashaData.current_antardasha.planet }} Antardasha
                                    </span>
                                </div>
                                <p class="text-xs text-orange-100 mt-1">{{ dashaData.current_mahadasha.natal_context }}</p>
                            </div>
                            <div class="bg-white/15 px-4 py-2.5 rounded-lg text-xs font-semibold shrink-0">
                                <div>Mahadasha: {{ dashaData.current_mahadasha.start }} – {{ dashaData.current_mahadasha.end }}</div>
                                <div v-if="dashaData.current_antardasha" class="mt-1">Antardasha: {{ dashaData.current_antardasha.start }} – {{ dashaData.current_antardasha.end }}</div>
                            </div>
                        </div>

                        <!-- Deep Insights Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="bg-orange-50/50 border border-orange-100 p-4 rounded-xl space-y-2">
                                <div class="text-xs font-bold uppercase text-orange-700 tracking-wider">✨ Key Possibilities</div>
                                <p class="text-gray-700 leading-relaxed text-xs">{{ dashaData.current_mahadasha.possibilities }}</p>
                            </div>
                            <div class="bg-amber-50/50 border border-amber-100 p-4 rounded-xl space-y-2">
                                <div class="text-xs font-bold uppercase text-amber-700 tracking-wider">🌱 How to Make It Positive</div>
                                <p class="text-gray-700 leading-relaxed text-xs">{{ dashaData.current_mahadasha.make_positive }}</p>
                            </div>
                            <div class="bg-emerald-50/50 border border-emerald-100 p-4 rounded-xl space-y-2">
                                <div class="text-xs font-bold uppercase text-emerald-700 tracking-wider">🕉️ Vedic Remedy &amp; Mantra</div>
                                <p class="text-gray-700 leading-relaxed text-xs">{{ dashaData.current_mahadasha.remedy }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complete 120-Year Mahadasha & Antardasha Timeline -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b">
                        <h3 class="text-xl font-bold text-gray-900">Complete 120-Year Mahadasha &amp; Antardasha Timeline</h3>
                        <span class="text-xs text-gray-500">Click any Mahadasha below to inspect sub-periods</span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="maha in dashaData.mahadashas"
                            :key="maha.planet"
                            class="border border-gray-200 rounded-xl overflow-hidden transition"
                            :class="maha.active ? 'ring-2 ring-orange-500 shadow-sm' : ''"
                        >
                            <!-- Mahadasha Row Button -->
                            <button
                                type="button"
                                @click="toggleExpand(maha.planet)"
                                class="w-full px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-left transition"
                                :class="maha.active ? 'bg-orange-50' : 'bg-gray-50/70 hover:bg-gray-100/70'"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0"
                                         :class="maha.active ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700'"
                                    >
                                        {{ maha.planet.substring(0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-gray-900 flex items-center gap-2">
                                            {{ maha.planet }} Mahadasha
                                            <span class="text-xs font-normal text-gray-500">({{ maha.duration }})</span>
                                            <span v-if="maha.active" class="px-2 py-0.5 rounded bg-orange-600 text-white text-[10px] font-bold uppercase tracking-wider">Active Now</span>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-0.5">{{ maha.start }} — {{ maha.end }}</div>
                                    </div>
                                </div>
                                <div class="text-xs font-bold text-orange-600 flex items-center gap-1 self-end sm:self-center">
                                    {{ expandedPlanet === maha.planet ? '▲ Hide Antardashas' : '▼ View Antardashas' }}
                                </div>
                            </button>

                            <!-- Expanded Sub-Periods & Guidance -->
                            <div v-if="expandedPlanet === maha.planet" class="p-5 bg-white border-t border-gray-200 space-y-5">
                                <div class="text-xs text-gray-700 bg-orange-50/60 p-3 rounded-lg border border-orange-100">
                                    <strong>Natal Placement:</strong> {{ maha.natal_context }}<br/>
                                    <strong>Core Theme:</strong> {{ maha.theme }}
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                    <div class="p-3 bg-gray-50 rounded-lg border space-y-1">
                                        <strong class="text-gray-900 block">✨ Possibilities</strong>
                                        <p class="text-gray-600 leading-relaxed">{{ maha.possibilities }}</p>
                                    </div>
                                    <div class="p-3 bg-gray-50 rounded-lg border space-y-1">
                                        <strong class="text-gray-900 block">🌱 Positive Action &amp; Remedy</strong>
                                        <p class="text-gray-600 leading-relaxed">{{ maha.make_positive }}<br/><em class="text-orange-700 mt-1 block">{{ maha.remedy }}</em></p>
                                    </div>
                                </div>

                                <!-- Antardasha Table -->
                                <div>
                                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-2">Antardasha (Sub-Periods) Inside {{ maha.planet }} Mahadasha</h4>
                                    <div class="overflow-x-auto border rounded-xl">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-gray-100 text-gray-700">
                                                    <th class="p-2.5">Sub-Period</th>
                                                    <th class="p-2.5">Start Date</th>
                                                    <th class="p-2.5">End Date</th>
                                                    <th class="p-2.5">Core Focus</th>
                                                    <th class="p-2.5">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="antar in maha.antardashas"
                                                    :key="antar.pair"
                                                    class="border-b hover:bg-orange-50/40 transition"
                                                    :class="antar.active ? 'bg-orange-50 font-bold' : ''"
                                                >
                                                    <td class="p-2.5 font-bold text-gray-900">{{ antar.pair }}</td>
                                                    <td class="p-2.5 text-gray-600">{{ antar.start }}</td>
                                                    <td class="p-2.5 text-gray-600">{{ antar.end }}</td>
                                                    <td class="p-2.5 text-gray-600">{{ antar.focus }}</td>
                                                    <td class="p-2.5">
                                                        <span v-if="antar.active" class="px-2 py-0.5 rounded bg-orange-600 text-white text-[10px] uppercase font-extrabold">Active</span>
                                                        <span v-else class="text-gray-400">Upcoming</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
