<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    karakaData: Object,
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
    form.get(route('tools.karakas'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Atmakaraka &amp; Darakaraka Calculator - Jaimini Astrology" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Atmakaraka &amp; Darakaraka Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Discover your Soul Planet (Atmakaraka) &amp; Spouse Significator (Darakaraka) via Jaimini Chara Karakas.</p>
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
                            {{ form.processing ? 'Calculating...' : 'Calculate Karakas' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div v-if="karakaData" class="space-y-8">
                <h2 class="text-2xl font-extrabold text-gray-900 text-center">
                    {{ karakaData.native_name }}'s Atmakaraka &amp; Darakaraka Report
                </h2>

                <!-- Primary 2 Cards: Atmakaraka & Darakaraka -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Atmakaraka -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-orange-600 space-y-4">
                        <div class="flex items-start justify-between border-b pb-3">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-orange-600">Highest Degree • Soul Significator</span>
                                <h3 class="text-xl font-extrabold text-gray-900 mt-0.5">Atmakaraka: {{ karakaData.atmakaraka.planet }}</h3>
                            </div>
                            <span class="px-3 py-1 rounded-lg bg-orange-50 border border-orange-200 text-xs font-mono font-bold text-orange-800">
                                {{ karakaData.atmakaraka.degree_formatted }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">Sign (Rashi)</span>
                                <strong class="text-gray-900">{{ karakaData.atmakaraka.rashi }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">House</span>
                                <strong class="text-gray-900">House {{ karakaData.atmakaraka.house }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">Nakshatra</span>
                                <strong class="text-gray-900">{{ karakaData.atmakaraka.nakshatra }}</strong>
                            </div>
                        </div>

                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ karakaData.atmakaraka.interpretation }}
                        </p>
                    </div>

                    <!-- Darakaraka -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-rose-600 space-y-4">
                        <div class="flex items-start justify-between border-b pb-3">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-rose-600">Lowest Degree • Spouse Significator</span>
                                <h3 class="text-xl font-extrabold text-gray-900 mt-0.5">Darakaraka: {{ karakaData.darakaraka.planet }}</h3>
                            </div>
                            <span class="px-3 py-1 rounded-lg bg-rose-50 border border-rose-200 text-xs font-mono font-bold text-rose-800">
                                {{ karakaData.darakaraka.degree_formatted }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">Sign (Rashi)</span>
                                <strong class="text-gray-900">{{ karakaData.darakaraka.rashi }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">House</span>
                                <strong class="text-gray-900">House {{ karakaData.darakaraka.house }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-gray-50 border">
                                <span class="text-gray-500 block">Nakshatra</span>
                                <strong class="text-gray-900">{{ karakaData.darakaraka.nakshatra }}</strong>
                            </div>
                        </div>

                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ karakaData.darakaraka.interpretation }}
                        </p>
                    </div>
                </div>

                <!-- Complete 7 Chara Karakas Table -->
                <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 overflow-x-auto">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">Complete 7 Jaimini Chara Karakas Ranking</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Karaka Role</th>
                                <th class="p-3">Planet</th>
                                <th class="p-3">Degree in Sign</th>
                                <th class="p-3">Sign (Rashi)</th>
                                <th class="p-3">House</th>
                                <th class="p-3">Life Area Governed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="k in karakaData.all_karakas" :key="k.karaka_code" class="border-b hover:bg-gray-50">
                                <td class="p-3 font-bold text-orange-600">{{ k.karaka_title }} ({{ k.karaka_code }})</td>
                                <td class="p-3 font-bold text-gray-900">{{ k.planet }}</td>
                                <td class="p-3 font-mono text-xs">{{ k.degree_formatted }}</td>
                                <td class="p-3">{{ k.rashi }}</td>
                                <td class="p-3 font-semibold text-orange-600">House {{ k.house }}</td>
                                <td class="p-3 text-xs text-gray-600">{{ k.life_area }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
