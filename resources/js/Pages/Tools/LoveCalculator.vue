<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
    loveData: Object,
    filters: Object,
});

const form = useForm({
    name1: props.filters?.name1 ?? '',
    gender1: props.filters?.gender1 ?? 'male',
    name2: props.filters?.name2 ?? '',
    gender2: props.filters?.gender2 ?? 'female',
});

function swapPartners() {
    const tempName = form.name1;
    const tempGender = form.gender1;
    form.name1 = form.name2;
    form.gender1 = form.gender2;
    form.name2 = tempName;
    form.gender2 = tempGender;
}

function submit() {
    form.get(route('tools.love-calculator'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Love Calculator by Name &amp; Gender - Numerology Match" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-rose-50 via-pink-50/50 to-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic Name Love Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Enter both names and genders to calculate Chaldean Name Numerology &amp; romantic compatibility.</p>
            </div>

            <!-- Input Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 mb-8 border border-rose-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-center">
                        <!-- Your Details -->
                        <div class="md:col-span-5 bg-rose-50/40 p-4 rounded-xl border border-rose-100 space-y-3">
                            <div class="text-xs font-extrabold uppercase tracking-wider text-rose-700">Your Details</div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Your Name</label>
                                <input
                                    type="text"
                                    v-model="form.name1"
                                    placeholder="Enter your name"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-500 bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Your Gender</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="form.gender1 = 'male'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender1 === 'male' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Male
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.gender1 = 'female'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender1 === 'female' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Female
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.gender1 = 'other'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender1 === 'other' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Other
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Center Swap Button -->
                        <div class="md:col-span-1 flex justify-center">
                            <button
                                type="button"
                                @click="swapPartners"
                                title="Swap Names"
                                class="w-10 h-10 rounded-full bg-rose-100 hover:bg-rose-200 text-rose-700 flex items-center justify-center font-bold shadow-sm transition"
                            >
                                ⇄
                            </button>
                        </div>

                        <!-- Partner's Details -->
                        <div class="md:col-span-5 bg-pink-50/40 p-4 rounded-xl border border-pink-100 space-y-3">
                            <div class="text-xs font-extrabold uppercase tracking-wider text-pink-700">Partner's Details</div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Partner's Name</label>
                                <input
                                    type="text"
                                    v-model="form.name2"
                                    placeholder="Enter partner's name"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-500 bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Partner's Gender</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="form.gender2 = 'male'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender2 === 'male' ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Male
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.gender2 = 'female'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender2 === 'female' ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Female
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.gender2 = 'other'"
                                        class="py-1.5 rounded-lg text-xs font-bold border transition"
                                        :class="form.gender2 === 'other' ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 border-gray-200'"
                                    >
                                        Other
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white font-bold py-3 px-10 rounded-xl shadow-md transition text-sm uppercase tracking-wider"
                        >
                            {{ form.processing ? 'Calculating Love Score...' : '❤️ Calculate Love Match' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Love Compatibility Result -->
            <div v-if="loveData" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-rose-100 space-y-6">
                <!-- Score Header -->
                <div class="text-center pb-6 border-b border-gray-100">
                    <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gradient-to-tr from-rose-600 to-pink-500 text-white shadow-lg mb-3">
                        <div>
                            <div class="text-3xl font-black leading-none">{{ loveData.love_percentage }}%</div>
                            <div class="text-[10px] uppercase tracking-widest mt-1 font-bold">Love Match</div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-extrabold text-gray-900">
                        {{ loveData.partner1.name }} <span class="text-rose-500">&amp;</span> {{ loveData.partner2.name }}
                    </h2>
                    <p class="text-sm font-semibold text-rose-600 mt-1">{{ loveData.verdict }}</p>
                </div>

                <!-- Partner Profile Comparison -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100">
                        <div class="text-xs font-bold uppercase text-rose-700 mb-1">{{ loveData.partner1.gender }} • Name Number</div>
                        <div class="text-lg font-bold text-gray-900">{{ loveData.partner1.name }}</div>
                        <div class="text-xs text-gray-600 mt-1">Ruler: <strong>{{ loveData.partner1.planet }}</strong> • {{ loveData.partner1.title }}</div>
                        <div class="mt-2 inline-block px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-extrabold text-xs">
                            Namank #{{ loveData.partner1.name_number }}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-pink-50/50 border border-pink-100">
                        <div class="text-xs font-bold uppercase text-pink-700 mb-1">{{ loveData.partner2.gender }} • Name Number</div>
                        <div class="text-lg font-bold text-gray-900">{{ loveData.partner2.name }}</div>
                        <div class="text-xs text-gray-600 mt-1">Ruler: <strong>{{ loveData.partner2.planet }}</strong> • {{ loveData.partner2.title }}</div>
                        <div class="mt-2 inline-block px-2 py-0.5 rounded bg-pink-100 text-pink-800 font-extrabold text-xs">
                            Namank #{{ loveData.partner2.name_number }}
                        </div>
                    </div>
                </div>

                <!-- Compatibility Message -->
                <div class="p-4 rounded-xl bg-rose-50/40 border border-rose-100 text-sm text-gray-800 leading-relaxed">
                    {{ loveData.message }}
                </div>

                <!-- Breakdown -->
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-4">Compatibility Breakdown</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="item in loveData.breakdown"
                            :key="item.label"
                            class="p-4 rounded-xl bg-gray-50 border"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-gray-800">{{ item.label }}</span>
                                <span class="text-sm font-extrabold text-rose-600">{{ item.score }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full bg-gradient-to-r from-rose-600 to-pink-500 transition-all duration-500"
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
