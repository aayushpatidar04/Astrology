<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    compatibilityData: Object,
    filters: Object,
});

const zodiacSigns = [
    { name: 'Aries', dates: 'Mar 21 - Apr 19', symbol: '♈' },
    { name: 'Taurus', dates: 'Apr 20 - May 20', symbol: '♉' },
    { name: 'Gemini', dates: 'May 21 - Jun 20', symbol: '♊' },
    { name: 'Cancer', dates: 'Jun 21 - Jul 22', symbol: '♋' },
    { name: 'Leo', dates: 'Jul 23 - Aug 22', symbol: '♌' },
    { name: 'Virgo', dates: 'Aug 23 - Sep 22', symbol: '♍' },
    { name: 'Libra', dates: 'Sep 23 - Oct 22', symbol: '♎' },
    { name: 'Scorpio', dates: 'Oct 23 - Nov 21', symbol: '♏' },
    { name: 'Sagittarius', dates: 'Nov 22 - Dec 21', symbol: '♐' },
    { name: 'Capricorn', dates: 'Dec 22 - Jan 19', symbol: '♑' },
    { name: 'Aquarius', dates: 'Jan 20 - Feb 18', symbol: '♒' },
    { name: 'Pisces', dates: 'Feb 19 - Mar 20', symbol: '♓' },
];

const form = useForm({
    sign1: props.filters?.sign1 ?? 'Gemini',
    sign2: props.filters?.sign2 ?? 'Libra',
    mode: props.filters?.mode ?? 'love',
});

const isFriendship = computed(() => form.mode === 'friendship');

function setMode(newMode) {
    form.mode = newMode;
    if (props.compatibilityData) {
        submit();
    }
}

function submit() {
    form.get(route('tools.compatibility'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Zodiac Sign Compatibility Matcher" />
    <Header :user="user" />

    <section
        class="py-12 min-h-screen transition-colors duration-300"
        :class="isFriendship ? 'bg-gradient-to-b from-indigo-50 to-gray-100' : 'bg-gradient-to-b from-rose-50 to-gray-100'"
    >
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Zodiac Compatibility Matcher</h1>
                <p class="mt-2 text-sm text-gray-600">Switch between Love &amp; Friendship modes to explore astrological chemistry and connection.</p>
            </div>

            <!-- Form & Mode Toggle Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <!-- Love vs Friendship Segmented Toggle -->
                <div class="flex justify-center mb-6">
                    <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                        <button
                            type="button"
                            @click="setMode('love')"
                            class="px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2"
                            :class="!isFriendship ? 'bg-rose-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span>❤️</span> Love Match
                        </button>
                        <button
                            type="button"
                            @click="setMode('friendship')"
                            class="px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2"
                            :class="isFriendship ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span>🤝</span> Friendship Match
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Your Zodiac Sign</label>
                        <select
                            v-model="form.sign1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2"
                            :class="isFriendship ? 'focus:ring-indigo-500' : 'focus:ring-rose-500'"
                        >
                            <option v-for="z in zodiacSigns" :key="z.name" :value="z.name">
                                {{ z.symbol }} {{ z.name }} ({{ z.dates }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            {{ isFriendship ? "Friend's Zodiac Sign" : "Partner's Zodiac Sign" }}
                        </label>
                        <select
                            v-model="form.sign2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2"
                            :class="isFriendship ? 'focus:ring-indigo-500' : 'focus:ring-rose-500'"
                        >
                            <option v-for="z in zodiacSigns" :key="z.name" :value="z.name">
                                {{ z.symbol }} {{ z.name }} ({{ z.dates }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full text-white font-semibold py-2.5 px-4 rounded-lg shadow transition text-sm"
                            :class="isFriendship ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-rose-600 hover:bg-rose-700'"
                        >
                            {{ form.processing ? 'Checking...' : (isFriendship ? 'Check Friendship' : 'Check Love Match') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Compatibility Report -->
            <div v-if="compatibilityData" class="bg-white rounded-xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-6 border-b">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full"
                                :class="compatibilityData.mode === 'friendship' ? 'bg-indigo-100 text-indigo-800' : 'bg-rose-100 text-rose-800'"
                            >
                                {{ compatibilityData.mode === 'friendship' ? 'Friendship Mode' : 'Love Mode' }}
                            </span>
                            <span class="text-xs font-semibold text-gray-500">{{ compatibilityData.aspect }}</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-gray-900 mt-2">
                            {{ compatibilityData.partner1.sign }} &amp; {{ compatibilityData.partner2.sign }}
                        </h2>
                        <p class="text-sm font-semibold text-gray-600 mt-0.5">{{ compatibilityData.verdict }}</p>
                    </div>

                    <div
                        class="text-center border px-6 py-3 rounded-2xl"
                        :class="compatibilityData.mode === 'friendship' ? 'bg-indigo-50 border-indigo-200' : 'bg-rose-50 border-rose-200'"
                    >
                        <div
                            class="text-3xl font-extrabold"
                            :class="compatibilityData.mode === 'friendship' ? 'text-indigo-600' : 'text-rose-600'"
                        >
                            {{ compatibilityData.overall_score }}%
                        </div>
                        <div
                            class="text-xs font-bold uppercase"
                            :class="compatibilityData.mode === 'friendship' ? 'text-indigo-800' : 'text-rose-800'"
                        >
                            {{ compatibilityData.mode === 'friendship' ? 'Friend Score' : 'Love Score' }}
                        </div>
                    </div>
                </div>

                <!-- Partner Traits Comparison -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-lg bg-gray-50 border">
                        <div class="font-bold text-gray-900 text-base mb-2">{{ compatibilityData.partner1.sign }}</div>
                        <div class="text-gray-600">Element: <strong class="text-gray-900">{{ compatibilityData.partner1.element }}</strong></div>
                        <div class="text-gray-600">Modality: <strong class="text-gray-900">{{ compatibilityData.partner1.quality }}</strong></div>
                        <div class="text-gray-600">Ruler: <strong class="text-gray-900">{{ compatibilityData.partner1.ruler }}</strong></div>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50 border">
                        <div class="font-bold text-gray-900 text-base mb-2">{{ compatibilityData.partner2.sign }}</div>
                        <div class="text-gray-600">Element: <strong class="text-gray-900">{{ compatibilityData.partner2.element }}</strong></div>
                        <div class="text-gray-600">Modality: <strong class="text-gray-900">{{ compatibilityData.partner2.quality }}</strong></div>
                        <div class="text-gray-600">Ruler: <strong class="text-gray-900">{{ compatibilityData.partner2.ruler }}</strong></div>
                    </div>
                </div>

                <!-- Summary -->
                <div
                    class="p-4 rounded-lg border text-sm text-gray-800 leading-relaxed"
                    :class="compatibilityData.mode === 'friendship' ? 'bg-indigo-50/60 border-indigo-100' : 'bg-rose-50/60 border-rose-100'"
                >
                    {{ compatibilityData.summary }}
                </div>

                <!-- Breakdown -->
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-4">Compatibility Breakdown</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="item in compatibilityData.breakdown"
                            :key="item.label"
                            class="p-4 rounded-lg bg-gray-50 border"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-gray-800">{{ item.label }}</span>
                                <span class="text-sm font-extrabold" :class="compatibilityData.mode === 'friendship' ? 'text-indigo-600' : 'text-rose-600'">
                                    {{ item.score }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full transition-all duration-500"
                                    :class="compatibilityData.mode === 'friendship' ? 'bg-indigo-600' : 'bg-rose-600'"
                                    :style="{ width: item.score + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
