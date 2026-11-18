<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    user: Object,
    readingData: Object,
    allCards: Array,
    filters: Object,
});

const spreads = [
    { id: 'single',     name: 'Daily Single Card',      desc: 'Quick clarity & daily focus (1 Card)', count: 1 },
    { id: 'three_card', name: 'Past, Present & Future', desc: 'Complete timeline insight (3 Cards)', count: 3 },
    { id: 'love',       name: 'Love & Relationship',    desc: 'You, partner & path ahead (3 Cards)', count: 3 },
    { id: 'career',     name: 'Career & Finance',       desc: 'Work momentum & opportunities (3 Cards)', count: 3 },
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
    chosen_cards: [],
});

const deckCards = ref([]);
const selectedIds = ref([]);
const shuffleState = ref('idle'); // 'idle', 'gathering', 'shuffling', 'dealing'

const activeSpread = computed(() => {
    return spreads.find(s => s.id === form.spread_type) || spreads[1];
});

function initDeck() {
    const shuffled = [...props.allCards];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    deckCards.value = shuffled;
    selectedIds.value = [];
}

initDeck();

function shuffleDeck() {
    if (shuffleState.value !== 'idle') return;

    // 1. Gather into center
    shuffleState.value = 'gathering';

    setTimeout(() => {
        // 2. Shuffle stack
        shuffleState.value = 'shuffling';
        initDeck();

        setTimeout(() => {
            // 3. Deal cards one by one back to positions
            shuffleState.value = 'dealing';

            setTimeout(() => {
                shuffleState.value = 'idle';
            }, 800); // match transition duration
        }, 600);
    }, 500);
}

function handleCardClick(cardId) {
    if (shuffleState.value !== 'idle') return;

    if (selectedIds.value.includes(cardId)) {
        selectedIds.value = selectedIds.value.filter(id => id !== cardId);
        return;
    }

    if (selectedIds.value.length < activeSpread.value.count) {
        selectedIds.value.push(cardId);
    }
}

function confirmSelection() {
    if (selectedIds.value.length < activeSpread.value.count) return;
    form.chosen_cards = selectedIds.value;
    form.get(route('tools.tarot'), {
        preserveState: true,
        replace: true,
    });
}

function resetReading() {
    router.get(route('tools.tarot'), {}, {
        preserveState: false,
        replace: true,
    });
}
</script>

<template>
    <Head title="Interactive Tarot Card Reading" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-purple-50 via-indigo-50/40 to-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Interactive Tarot Card Reading</h1>
                <p class="mt-2 text-sm text-gray-600">Select your spread, shuffle the deck, and intuitively pick your cards.</p>
            </div>

            <!-- Step 1: Configuration & Deck Picking -->
            <div v-if="!readingData" class="space-y-8">
                <!-- Spread & Question Form -->
                <div class="bg-white rounded-2xl shadow-md p-6 border border-purple-100">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">1. Choose Your Tarot Spread</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <button
                                    v-for="s in spreads"
                                    :key="s.id"
                                    type="button"
                                    @click="form.spread_type = s.id; selectedIds = []"
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    </div>
                </div>

                <!-- Interactive Shuffling & Deck Dealing Section -->
                <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-purple-100 text-center relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 pb-4 border-b">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Pick {{ activeSpread.count }} Card(s) from the Deck</h2>
                            <p class="text-xs text-purple-700 font-semibold mt-1">
                                Selected: {{ selectedIds.length }} / {{ activeSpread.count }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button
                                @click="shuffleDeck"
                                :disabled="shuffleState !== 'idle'"
                                class="px-4 py-2 bg-purple-100 text-purple-800 hover:bg-purple-200 font-bold rounded-xl text-xs uppercase tracking-wider transition"
                            >
                                🔀 Shuffle &amp; Deal
                            </button>
                            <button
                                @click="confirmSelection"
                                :disabled="selectedIds.length < activeSpread.count || form.processing"
                                class="px-6 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition disabled:opacity-50 shadow"
                            >
                                {{ form.processing ? 'Revealing...' : 'Reveal Reading ✨' }}
                            </button>
                        </div>
                    </div>

                    <!-- Responsive 78-Card Fanned Deck Grid with Authentic Dealing & Gathering Animation -->
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3 py-4 max-h-[520px] overflow-y-auto p-2 relative">
                        <div
                            v-for="(card, index) in deckCards"
                            :key="card.id"
                            @click="handleCardClick(card.id)"
                            class="aspect-[2/3] rounded-lg cursor-pointer transition-all flex flex-col items-center justify-center p-1.5 shadow-md border"
                            :style="{
                                transitionDuration: '500ms',
                                transitionDelay: shuffleState === 'dealing' ? (index * 8) + 'ms' : '0ms'
                            }"
                            :class="{
                                '-translate-y-4 bg-purple-700 border-amber-300 ring-4 ring-purple-400 scale-105': selectedIds.includes(card.id),
                                'bg-gradient-to-br from-indigo-950 via-purple-950 to-indigo-900 border-amber-400/60 hover:-translate-y-2 hover:shadow-xl': !selectedIds.includes(card.id),
                                'translate-x-[calc(50vw-150px)] -translate-y-40 scale-50 opacity-0': shuffleState === 'gathering',
                                'translate-x-[calc(50vw-150px)] -translate-y-40 scale-75 opacity-80 rotate-12': shuffleState === 'shuffling',
                                'translate-x-0 translate-y-0 scale-100 opacity-100': shuffleState === 'dealing' || shuffleState === 'idle'
                            }"
                        >
                            <div class="w-full h-full rounded border border-amber-300/40 bg-indigo-900/60 flex flex-col items-center justify-center text-center p-1">
                                <span class="text-[9px] text-amber-300/70 font-bold">TAROT</span>
                                <span class="text-sm my-1">✨</span>
                                <span class="text-[8px] text-white font-semibold">{{ index + 1 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Reading Results Output -->
            <div v-if="readingData" class="space-y-8">
                <!-- Overall Theme Banner -->
                <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-purple-600 text-center relative">
                    <button
                        @click="resetReading"
                        class="absolute top-4 right-4 px-3 py-1.5 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-bold uppercase transition"
                    >
                        🔄 Draw Again
                    </button>

                    <span class="text-xs font-bold uppercase tracking-widest text-purple-600">
                        {{ readingData.category }} Reading
                    </span>
                    <h2 class="text-2xl font-extrabold text-gray-900 mt-1">{{ readingData.overall_theme }}</h2>
                    <p class="text-xs text-gray-500 mt-1 italic">Question: "{{ readingData.question }}"</p>
                </div>

                <!-- Drawn Tarot Cards Grid with Flip Animation -->
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
                                class="w-32 h-44 rounded-xl border-2 border-amber-300/80 bg-indigo-900/80 flex flex-col items-center justify-center p-3 text-center shadow-inner transition-transform duration-700"
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
