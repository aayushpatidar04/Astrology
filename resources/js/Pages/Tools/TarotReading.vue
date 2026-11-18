<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import Header from "@/Pages/Layouts/Header.vue";
import Footer from "../Layouts/Footer.vue";
import { ref, computed, nextTick } from "vue";

const props = defineProps({
    user: Object,
    readingData: Object,
    allCards: Array,
    filters: Object,
});

const spreads = [
    {
        id: "single",
        name: "Daily Single Card",
        desc: "Quick clarity & daily focus (1 Card)",
        count: 1,
    },
    {
        id: "three_card",
        name: "Past, Present & Future",
        desc: "Complete timeline insight (3 Cards)",
        count: 3,
    },
    {
        id: "love",
        name: "Love & Relationship",
        desc: "You, partner & path ahead (3 Cards)",
        count: 3,
    },
    {
        id: "career",
        name: "Career & Finance",
        desc: "Work momentum & opportunities (3 Cards)",
        count: 3,
    },
];

const categories = [
    "General",
    "Love & Marriage",
    "Career & Business",
    "Finance & Wealth",
    "Personal Growth",
];

const form = useForm({
    spread_type: props.filters?.spread_type ?? "three_card",
    category: props.filters?.category ?? "General",
    question: props.filters?.question ?? "",
    chosen_cards: [],
});

const deckCards = ref([]);
const selectedIds = ref([]);
const shuffleState = ref("idle"); // 'idle' | 'gathering' | 'shuffling' | 'dealing'
const gridRef = ref(null);
const slotEls = []; // card wrapper elements, by slot index
const stackOffsets = ref([]); // [{ dx, dy }] distance of each slot from slot 0

const setSlotRef = (el, index) => {
    if (el) slotEls[index] = el;
};

const activeSpread = computed(() => {
    return spreads.find((s) => s.id === form.spread_type) || spreads[1];
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

// Measure how far each slot is from the first slot (layout positions, ignores transforms)
function measureOffsets() {
    const first = slotEls[0];
    if (!first) return;
    stackOffsets.value = deckCards.value.map((_, i) => {
        const el = slotEls[i];
        return {
            dx: first.offsetLeft - el.offsetLeft,
            dy: first.offsetTop - el.offsetTop,
        };
    });
}

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

async function shuffleDeck() {
    if (shuffleState.value !== "idle") return;

    // make sure the first card slot is visible
    if (gridRef.value) gridRef.value.scrollTo({ top: 0, behavior: "smooth" });
    await wait(150);

    selectedIds.value = [];
    measureOffsets();

    // 1. Gather: every card slides to the first card's position
    shuffleState.value = "gathering";
    await wait(900);
    // 2. Shuffle: reorder data while the deck is stacked (swap is invisible), then riffle
    shuffleState.value = "shuffling";
    initDeck();
    await nextTick();
    await wait(1000);

    // 3. Deal: cards go back one by one
    shuffleState.value = "dealing";
    await wait(500 + deckCards.value.length * 22 + 50);

    shuffleState.value = "idle";
}

// Per-card style for the animation wrapper
function slotStyle(index) {
    const o = stackOffsets.value[index] || { dx: 0, dy: 0 };
    const state = shuffleState.value;
    const total = deckCards.value.length;
    const stackShift = index * 0.12; // tiny offset so the stack looks thick

    const base = {
        "--dx": `${o.dx + stackShift}px`,
        "--dy": `${o.dy - stackShift}px`,
        "--dir": index % 2 === 0 ? 1 : -1,
        zIndex: total - index, // top of the deck is dealt first
    };

    if (state === "gathering") {
        return {
            ...base,
            transform: "translate(var(--dx), var(--dy))",
            transition: "transform 600ms cubic-bezier(.4,0,.2,1)",
            transitionDelay: (index % 12) * 15 + "ms",
        };
    }
    if (state === "shuffling") {
        return {
            ...base,
            transform: "translate(var(--dx), var(--dy))",
            animation: "riffle 0.53s ease-in-out 3",
            animationDelay: (index % 8) * 18 + "ms",
        };
    }
    if (state === "dealing") {
        return {
            ...base,
            transform: "translate(0, 0)",
            transition: "transform 500ms cubic-bezier(.22,1,.36,1)",
            transitionDelay: index * 22 + "ms",
        };
    }
    return { transform: "translate(0, 0)" };
}

function handleCardClick(cardId) {
    if (shuffleState.value !== "idle") return;

    if (selectedIds.value.includes(cardId)) {
        selectedIds.value = selectedIds.value.filter((id) => id !== cardId);
        return;
    }

    if (selectedIds.value.length < activeSpread.value.count) {
        selectedIds.value.push(cardId);
    }
}

function confirmSelection() {
    if (selectedIds.value.length < activeSpread.value.count) return;
    form.chosen_cards = selectedIds.value;
    form.get(route("tools.tarot"), {
        preserveState: true,
        replace: true,
    });
}

function resetReading() {
    router.get(
        route("tools.tarot"),
        {},
        {
            preserveState: false,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="Interactive Tarot Card Reading" />
    <Header :user="user" />

    <section
        class="py-12 bg-gradient-to-b from-purple-50 via-indigo-50/40 to-gray-100 min-h-screen"
    >
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1
                    class="text-3xl font-extrabold text-gray-900 tracking-tight"
                >
                    Interactive Tarot Card Reading
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Select your spread, shuffle the deck, and intuitively pick
                    your cards.
                </p>
            </div>

            <!-- Step 1: Configuration & Deck Picking -->
            <div v-if="!readingData" class="space-y-8">
                <!-- Spread & Question Form -->
                <div
                    class="bg-white rounded-2xl shadow-md p-6 border border-purple-100"
                >
                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3"
                                >1. Choose Your Tarot Spread</label
                            >
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3"
                            >
                                <button
                                    v-for="s in spreads"
                                    :key="s.id"
                                    type="button"
                                    @click="
                                        form.spread_type = s.id;
                                        selectedIds = [];
                                    "
                                    class="p-3.5 rounded-xl border text-left transition"
                                    :class="
                                        form.spread_type === s.id
                                            ? 'bg-purple-600 text-white border-purple-600 shadow-sm'
                                            : 'bg-gray-50 text-gray-800 border-gray-200 hover:border-purple-400'
                                    "
                                >
                                    <div class="font-bold text-sm">
                                        {{ s.name }}
                                    </div>
                                    <div
                                        class="text-xs mt-1"
                                        :class="
                                            form.spread_type === s.id
                                                ? 'text-purple-100'
                                                : 'text-gray-500'
                                        "
                                    >
                                        {{ s.desc }}
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-700 uppercase mb-1"
                                    >2. Focus Area</label
                                >
                                <select
                                    v-model="form.category"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500"
                                >
                                    <option
                                        v-for="cat in categories"
                                        :key="cat"
                                        :value="cat"
                                    >
                                        {{ cat }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-bold text-gray-700 uppercase mb-1"
                                    >3. Your Question (Optional)</label
                                >
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
                <div
                    class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-purple-100 text-center relative overflow-hidden"
                >
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 pb-4 border-b"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                Pick {{ activeSpread.count }} Card(s) from the
                                Deck
                            </h2>
                            <p
                                class="text-xs text-purple-700 font-semibold mt-1"
                            >
                                Selected: {{ selectedIds.length }} /
                                {{ activeSpread.count }}
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
                                :disabled="
                                    selectedIds.length < activeSpread.count ||
                                    form.processing
                                "
                                class="px-6 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition disabled:opacity-50 shadow"
                            >
                                {{
                                    form.processing
                                        ? "Revealing..."
                                        : "Reveal Reading ✨"
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- Responsive 78-Card Fanned Deck Grid with Authentic Dealing & Gathering Animation -->
                    <div
                        ref="gridRef"
                        class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3 py-4 max-h-[520px] p-2 relative"
                        :class="
                            shuffleState === 'idle'
                                ? 'overflow-y-auto'
                                : 'overflow-hidden'
                        "
                    >
                        <div
                            v-for="(card, index) in deckCards"
                            :key="card.id"
                            :ref="(el) => setSlotRef(el, index)"
                            class="aspect-[2/3] relative"
                            :style="slotStyle(index)"
                            @click="handleCardClick(card.id)"
                        >
                            <!-- inner card: hover / selected styles live here -->
                            <div
                                class="w-full h-full rounded-lg cursor-pointer transition-all duration-300 flex flex-col items-center justify-center p-1.5 shadow-md border"
                                :class="
                                    selectedIds.includes(card.id)
                                        ? '-translate-y-4 bg-purple-700 border-amber-300 ring-4 ring-purple-400 scale-105'
                                        : [
                                              'bg-gradient-to-br from-indigo-950 via-purple-950 to-indigo-900 border-amber-400/60',
                                              shuffleState === 'idle'
                                                  ? 'hover:-translate-y-2 hover:shadow-xl'
                                                  : '',
                                          ]
                                "
                            >
                                <div
                                    class="w-full h-full rounded border border-amber-300/40 bg-indigo-900/60 flex flex-col items-center justify-center text-center p-1"
                                >
                                    <span
                                        class="text-[9px] text-amber-300/70 font-bold"
                                        >TAROT</span
                                    >
                                    <span class="text-sm my-1">✨</span>
                                    <span
                                        class="text-[8px] text-white font-semibold"
                                        >{{ index + 1 }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Reading Results Output -->
            <div v-if="readingData" class="space-y-8">
                <!-- Overall Theme Banner -->
                <div
                    class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-purple-600 text-center relative"
                >
                    <button
                        @click="resetReading"
                        class="absolute top-4 right-4 px-3 py-1.5 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-xs font-bold uppercase transition"
                    >
                        🔄 Draw Again
                    </button>

                    <span
                        class="text-xs font-bold uppercase tracking-widest text-purple-600"
                    >
                        {{ readingData.category }} Reading
                    </span>
                    <h2 class="text-2xl font-extrabold text-gray-900 mt-1">
                        {{ readingData.overall_theme }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1 italic">
                        Question: "{{ readingData.question }}"
                    </p>
                </div>

                <!-- Drawn Tarot Cards Grid with Flip Animation -->
                <div
                    class="grid gap-6"
                    :class="
                        readingData.cards.length === 1
                            ? 'grid-cols-1 max-w-md mx-auto'
                            : 'grid-cols-1 md:grid-cols-3'
                    "
                >
                    <div
                        v-for="card in readingData.cards"
                        :key="card.id + '-' + card.position"
                        class="bg-white rounded-2xl shadow-md border border-purple-100 overflow-hidden flex flex-col"
                    >
                        <div
                            class="bg-purple-900 text-white px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider"
                        >
                            {{ card.position }}
                        </div>

                        <!-- Visual Card Frame -->
                        <div
                            class="p-6 flex flex-col items-center justify-center bg-gradient-to-b from-indigo-950 to-purple-900 text-amber-100 min-h-[210px]"
                        >
                            <div
                                class="w-32 h-44 rounded-xl border-2 border-amber-300/80 bg-indigo-900/80 flex flex-col items-center justify-center p-3 text-center shadow-inner transition-transform duration-700"
                                :class="card.is_reversed ? 'rotate-180' : ''"
                            >
                                <span
                                    class="text-[10px] uppercase tracking-widest text-amber-300/80"
                                    >{{ card.arcana }}</span
                                >
                                <span class="text-2xl my-2">✨</span>
                                <span
                                    class="text-xs font-bold leading-snug text-white"
                                    >{{ card.name }}</span
                                >
                            </div>

                            <span
                                class="mt-3 px-3 py-0.5 rounded-full text-[11px] font-bold uppercase"
                                :class="
                                    card.is_reversed
                                        ? 'bg-amber-500/20 text-amber-200 border border-amber-400/40'
                                        : 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40'
                                "
                            >
                                {{ card.orientation }}
                            </span>
                        </div>

                        <!-- Consistent Card Meaning -->
                        <div
                            class="p-5 flex-1 flex flex-col justify-between space-y-3"
                        >
                            <div>
                                <h3
                                    class="text-lg font-extrabold text-gray-900"
                                >
                                    {{ card.name }}
                                </h3>
                                <p
                                    class="text-xs text-purple-600 font-semibold"
                                >
                                    {{ card.arcana }} Arcana •
                                    {{ card.suit }} ({{ card.element }})
                                </p>
                                <div
                                    class="mt-2 inline-block text-[11px] bg-purple-50 text-purple-800 px-2.5 py-1 rounded font-medium"
                                >
                                    Keywords: {{ card.keywords }}
                                </div>
                                <p
                                    class="text-sm text-gray-700 leading-relaxed mt-3"
                                >
                                    {{ card.interpretation }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic AI Synthesis & Actionable Advice -->
                <div
                    class="bg-white rounded-2xl shadow-md p-6 sm:p-8 border border-gray-100 space-y-5"
                >
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2">
                            Complete Reading Synthesis
                        </h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ readingData.synthesis }}
                        </p>
                    </div>

                    <div
                        class="p-4 rounded-xl bg-purple-50 border border-purple-200"
                    >
                        <div
                            class="text-xs font-bold uppercase tracking-wider text-purple-800 mb-1"
                        >
                            Guidance &amp; Next Step
                        </div>
                        <p class="text-sm text-purple-950 font-medium">
                            {{ readingData.actionable_advice }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
<style scoped>
@keyframes riffle {
    0%,
    100% {
        transform: translate(var(--dx), var(--dy)) rotate(0deg);
    }
    25% {
        transform: translate(calc(var(--dx) + var(--dir) * -26px), var(--dy))
            rotate(calc(var(--dir) * -6deg));
    }
    75% {
        transform: translate(
                calc(var(--dx) + var(--dir) * 26px),
                calc(var(--dy) - 4px)
            )
            rotate(calc(var(--dir) * 6deg));
    }
}
</style>
