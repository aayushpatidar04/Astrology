<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
    readingData: Object,
    filters: Object,
});

const spreads = [
    { id: 'single',     name: 'Daily Single Card',      desc: 'Quick clarity & daily focus (1 Card)' },
    { id: 'three_card', name: 'Past, Present & Future', desc: 'Complete timeline insight (3 Cards)' },
    { id: 'love',       name: 'Love & Relationship',    desc: 'You, partner & path ahead (3 Cards)' },
    { id: 'career',     name: 'Career & Finance',       desc: 'Work momentum & opportunities (3 Cards)' },
];

const categories = [
    'General',
    'Love & Marriage',
    'Career & Business',
    'Finance & Wealth',
    'Personal Growth',
];

const form = useForm({
    spread_type: props.filters?.spread_type ?? 'three_card',
    category: props.filters?.category ?? 'General',
    question: props.filters?.question ?? '',
});

function submit() {
    form.get(route('tools.tarot'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Tarot Card Reading" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-purple-50 via-indigo-50/40 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Intuitive Tarot Card Reading</h1>
                <p class="mt-2 text-sm text-gray-600">Focus on your intention, select a spread, and draw from the 78-card deck.</p>
            </div>

            <!-- Spread & Question Form -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-10 border border-purple-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">1. Choose Your Tarot Spread</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <button
                                v-for="s in spreads"
                                :key="s.id"
                                type="button"
                                @click="form.spread_type = s.id"
                                class="p-3.5 rounded-xl border text-left transition"
                                :class="form.spread_type === s.id
                                    ? 'bg-purple-600 text-white border-purple-600 shadow-sm'
                                    : 'bg-gray-50 text-gray-800 border-gray-200 hover:border-purple-400'"
                            >
                                <div class="font-bold text-sm">{{ s.name }}</div>
                                <div
                                    class="text-xs mt-1"
                                    :class="form.spread_type === s.id ? 'text-purple-100' : 'text-gray-500'"
                                >
                                    {{ s.desc }}
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">2. Focus Area</label>
                            <select
                                v-model="form.category"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500"
                            >
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">3. Your Question (Optional)</label>
                            <input
                                type="text"
                                v-model="form.question"
                                placeholder="e.g., What energy surrounds my relationship or career right now?"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500"
                            />
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-purple-700 hover:bg-purple-800 text-white font-bold py-3 px-10 rounded-xl shadow-md transition text-sm uppercase tracking-wider"
                        >
                            {{ form.processing ? 'Shuffling & Drawing Cards...' : 'Shuffle & Draw Cards' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reading Output -->
            <div v-if="readingData" class="space-y-8">
                <!-- Overall Theme Banner -->
                <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-purple-600 text-center">
                    <span class="text-xs font-bold uppercase tracking-widest text-purple-600">
                        {{ readingData.category }} Reading
                    </span>
                    <h2 class="text-2xl font-extrabold text-gray-900 mt-1">{{ readingData.overall_theme }}</h2>
                    <p class="text-xs text-gray-500 mt-1 italic">Question: "{{ readingData.question }}"</p>
                </div>

                <!-- Drawn Tarot Cards Grid -->
                <div
                    class="grid gap-6"
                    :class="readingData.cards.length === 1 ? 'grid-cols-1 max-w-md mx-auto' : 'grid-cols-1 md:grid-cols-3'"
                >
                    <div
                        v-for="card in readingData.cards"
                        :key="card.id + '-' + card.position"
                        class="bg-white rounded-2xl shadow-md border border-purple-100 overflow-hidden flex flex-col"
                    >
                        <div class="bg-purple-900 text-white px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider">
                            {{ card.position }}
                        </div>

                        <!-- Visual Card Frame -->
                        <div class="p-6 flex flex-col items-center justify-center bg-gradient-to-b from-indigo-950 to-purple-900 text-amber-100 min-h-[210px]">
                            <div
                                class="w-32 h-44 rounded-xl border-2 border-amber-300/80 bg-indigo-900/80 flex flex-col items-center justify-center p-3 text-center shadow-inner transition-transform duration-500"
                                :class="card.is_reversed ? 'rotate-180' : ''"
                            >
                                <span class="text-[10px] uppercase tracking-widest text-amber-300/80">{{ card.arcana }}</span>
                                <span class="text-2xl my-2">✨</span>
                                <span class="text-xs font-bold leading-snug text-white">{{ card.name }}</span>
                            </div>

                            <span
                                class="mt-3 px-3 py-0.5 rounded-full text-[11px] font-bold uppercase"
                                :class="card.is_reversed
                                    ? 'bg-amber-500/20 text-amber-200 border border-amber-400/40'
                                    : 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40'"
                            >
                                {{ card.orientation }}
                            </span>
                        </div>

                        <!-- Consistent Card Meaning -->
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <h3 class="text-lg font-extrabold text-gray-900">{{ card.name }}</h3>
                                <p class="text-xs text-purple-600 font-semibold">
                                    {{ card.arcana }} Arcana • {{ card.suit }} ({{ card.element }})
                                </p>
                                <div class="mt-2 inline-block text-[11px] bg-purple-50 text-purple-800 px-2.5 py-1 rounded font-medium">
                                    Keywords: {{ card.keywords }}
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed mt-3">{{ card.interpretation }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic AI Synthesis & Actionable Advice -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-5">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2">Complete Reading Synthesis</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ readingData.synthesis }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-purple-50 border border-purple-200">
                        <div class="text-xs font-bold uppercase tracking-wider text-purple-800 mb-1">Guidance &amp; Next Step</div>
                        <p class="text-sm text-purple-950 font-medium">{{ readingData.actionable_advice }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
