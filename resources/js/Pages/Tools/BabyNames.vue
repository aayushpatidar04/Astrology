<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    babyNameData: Object,
    filters: Object,
});

const namingStyles = [
    'Modern & Sanskrit',
    'Traditional Vedic',
    'Short & Trendy (2 Syllables)',
    'Mythological & Spiritual',
];

const form = useForm({
    dob: props.filters?.dob ?? new Date().toISOString().slice(0, 10),
    tob: props.filters?.tob ?? '08:30',
    birth_place: props.filters?.birth_place ?? 'Jaipur, Rajasthan, India',
    lat: props.filters?.lat ?? 26.9124,
    lng: props.filters?.lng ?? 75.7873,
    gender: props.filters?.gender ?? 'boy',
    style: props.filters?.style ?? 'Modern & Sanskrit',
});

const citySearch = ref(form.birth_place);
const cityResults = ref([]);
const showDropdown = ref(false);
const favoriteNames = ref([]);

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

function toggleFavorite(name) {
    if (favoriteNames.value.includes(name)) {
        favoriteNames.value = favoriteNames.value.filter(n => n !== name);
    } else {
        favoriteNames.value.push(name);
    }
}

function submit() {
    form.get(route('tools.baby-names'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Vedic Baby Name Generator by Nakshatra &amp; DOB" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-sky-50 via-orange-50/40 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic Baby Name Generator (Namakaran)</h1>
                <p class="mt-2 text-sm text-gray-600">Find auspicious starting syllables (Namakshar) from your baby's exact Nakshatra &amp; Pada.</p>
            </div>

            <!-- Birth Details Form -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8 border border-gray-100">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Baby's Date of Birth</label>
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

                        <div class="relative md:col-span-2">
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Gender</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    type="button"
                                    @click="form.gender = 'boy'"
                                    class="py-2 rounded-lg text-xs font-bold border transition"
                                    :class="form.gender === 'boy' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                >
                                    👦 Boy
                                </button>
                                <button
                                    type="button"
                                    @click="form.gender = 'girl'"
                                    class="py-2 rounded-lg text-xs font-bold border transition"
                                    :class="form.gender === 'girl' ? 'bg-pink-600 text-white border-pink-600' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                >
                                    👧 Girl
                                </button>
                                <button
                                    type="button"
                                    @click="form.gender = 'unisex'"
                                    class="py-2 rounded-lg text-xs font-bold border transition"
                                    :class="form.gender === 'unisex' ? 'bg-purple-600 text-white border-purple-600' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                >
                                    ✨ Unisex
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Naming Style</label>
                            <select
                                v-model="form.style"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                            >
                                <option v-for="st in namingStyles" :key="st" :value="st">{{ st }}</option>
                            </select>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm"
                            >
                                {{ form.processing ? 'Calculating Nakshatra...' : 'Find Auspicious Names' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div v-if="babyNameData" class="space-y-8">
                <!-- Nakshatra & Primary Syllable Highlight Card -->
                <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-orange-600 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-orange-600">Vedic Birth Star Analysis</div>
                        <h2 class="text-2xl font-extrabold text-gray-900 mt-1">
                            {{ babyNameData.astrology_profile.nakshatra }} Nakshatra (Pada {{ babyNameData.astrology_profile.pada }})
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Moon Sign (Rashi): <strong>{{ babyNameData.astrology_profile.moon_sign }}</strong> •
                            Ascendant: <strong>{{ babyNameData.astrology_profile.lagna }}</strong> •
                            Lord: <strong>{{ babyNameData.astrology_profile.nakshatra_lord }}</strong>
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-bold text-gray-700">All 4 Pada Syllables:</span>
                            <span v-for="(syl, pNum) in babyNameData.astrology_profile.all_syllables" :key="pNum"
                                class="px-2.5 py-1 rounded border font-bold"
                                :class="pNum === babyNameData.astrology_profile.pada ? 'bg-orange-600 text-white border-orange-600' : 'bg-gray-50 text-gray-700'">
                                Pada {{ pNum }}: {{ syl }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-orange-50 border-2 border-orange-300 p-6 rounded-2xl text-center shadow-inner min-w-[200px]">
                        <div class="text-xs font-bold uppercase tracking-wider text-orange-800">Primary Namakshar</div>
                        <div class="text-4xl font-black text-orange-600 my-1">"{{ babyNameData.astrology_profile.primary_syllable }}"</div>
                        <div class="text-xs text-gray-500 font-semibold">Starting Sound</div>
                    </div>
                </div>

                <!-- Shortlisted Names Banner -->
                <div v-if="favoriteNames.length > 0" class="bg-pink-50 border border-pink-200 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-bold text-pink-900">
                        <span>❤️</span> Shortlisted Names ({{ favoriteNames.length }}):
                        <span class="font-normal text-pink-700">{{ favoriteNames.join(', ') }}</span>
                    </div>
                </div>

                <!-- Names Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="item in babyNameData.names"
                        :key="item.name"
                        class="bg-white rounded-2xl shadow-md p-5 border border-gray-100 flex flex-col justify-between hover:shadow-lg transition"
                    >
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-xl font-extrabold text-gray-900">{{ item.name }}</h3>
                                    <p class="text-xs text-orange-600 font-semibold mt-0.5">Starts with "{{ item.starting_syllable }}" • {{ item.origin }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleFavorite(item.name)"
                                    class="text-lg transition transform hover:scale-110"
                                    :title="favoriteNames.includes(item.name) ? 'Remove from shortlist' : 'Shortlist name'"
                                >
                                    {{ favoriteNames.includes(item.name) ? '❤️' : '🤍' }}
                                </button>
                            </div>

                            <p class="text-sm text-gray-700 leading-relaxed my-3">{{ item.meaning }}</p>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="text-gray-500 italic">{{ item.rashi_note }}</span>
                            <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-700 font-bold">
                                Namank #{{ item.numerology_number }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
