<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Header from "@/Pages/Layouts/Header.vue";
import Footer from "../Layouts/Footer.vue";
import { ref, computed } from "vue";
import axios from "axios";

const props = defineProps({
    user: Object,
    kundliData: Object,
    filters: Object,
});

const form = useForm({
    name: props.filters?.name ?? "Aayush",
    dob: props.filters?.dob ?? "2000-05-04",
    tob: props.filters?.tob ?? "09:05",
    birth_place: props.filters?.birth_place ?? "Chhoti Sadri, Rajasthan, India",
    lat: props.filters?.lat ?? 24.3815,
    lng: props.filters?.lng ?? 74.7012,
});

const hoveredHouse = ref(null);

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
        const res = await axios.get(route("api.cities.autocomplete"), {
            params: { key: citySearch.value },
        });
        cityResults.value = res.data?.data ?? [];
        showDropdown.value = cityResults.value.length > 0;
    } catch (e) {
        cityResults.value = [];
    }
}

function selectCity(city) {
    const parts = [city.name, city.state, city.countryName || "India"].filter(
        Boolean,
    );
    const fullName = parts.join(", ");
    citySearch.value = fullName;
    form.birth_place = fullName;
    form.lat = city.latitude;
    form.lng = city.longitude;
    showDropdown.value = false;
}

function submit() {
    form.get(route("tools.kundli"), {
        preserveState: true,
        replace: true,
    });
}

// Compute North Indian Diamond Chart houses (1 to 12) with compact formatting
const chartHouses = computed(() => {
    if (!props.kundliData?.planetary_positions) return {};

    const asc = props.kundliData.planetary_positions.find(
        (p) =>
            p.planet === "Ascendant" ||
            p.planet === "Ascendant (Lagna)" ||
            p.planet.includes("Ascendant"),
    );
    const ascSignNum = asc?.rashi_number ?? 3;

    const shortNames = {
        Ascendant: "Asc",
        "Ascendant (Lagna)": "Asc",
        Sun: "Su",
        Moon: "Mo",
        Mars: "Ma",
        Mercury: "Me",
        Jupiter: "Ju",
        Venus: "Ve",
        Saturn: "Sa",
        Rahu: "Ra",
        Ketu: "Ke",
    };

    const houses = {};
    for (let h = 1; h <= 12; h++) {
        const rashiNum = ((ascSignNum - 1 + (h - 1)) % 12) + 1;
        const planetsInHouse = props.kundliData.planetary_positions
            .filter((p) => p.house === h)
            .map((p) => {
                const shortName = shortNames[p.planet] || p.planet;
                let degShort = "";
                if (p.degree) {
                    const parts = p.degree.split("°");
                    degShort = parts[0] + "°";
                }
                const isAsc = shortName === "Asc";
                return {
                    text: `${shortName} ${degShort}`,
                    color: isAsc
                        ? "#7c2d12"
                        : p.planet === "Sun"
                          ? "#b45309"
                          : p.planet === "Moon"
                            ? "#1d4ed8"
                            : p.planet === "Mars"
                              ? "#b91c1c"
                              : p.planet === "Saturn"
                                ? "#4b5563"
                                : "#1f2937",
                };
            });

        houses[h] = {
            rashiNum,
            planets: planetsInHouse,
        };
    }
    return houses;
});

const GEOM = {
    1: {
        pts: "100,100 200,0 300,100 200,200",
        o: [200, 100],
        plan: [200, 95],
        num: [200, 176],
        cols: 3,
        rows: 5,
        fs: 11,
    },
    2: {
        pts: "0,0 200,0 100,100",
        o: [100, 33],
        plan: [100, 36],
        num: [100, 84],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    3: {
        pts: "0,0 100,100 0,200",
        o: [33, 100],
        plan: [40, 100],
        num: [84, 100],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    4: {
        pts: "0,200 100,100 200,200 100,300",
        o: [100, 200],
        plan: [100, 190],
        num: [100, 252],
        cols: 3,
        rows: 4,
        fs: 11,
    },
    5: {
        pts: "0,200 100,300 0,400",
        o: [33, 300],
        plan: [40, 300],
        num: [84, 300],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    6: {
        pts: "0,400 100,300 200,400",
        o: [100, 367],
        plan: [100, 364],
        num: [100, 316],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    7: {
        pts: "100,300 200,200 300,300 200,400",
        o: [200, 300],
        plan: [200, 305],
        num: [200, 224],
        cols: 3,
        rows: 5,
        fs: 11,
    },
    8: {
        pts: "200,400 300,300 400,400",
        o: [300, 367],
        plan: [300, 364],
        num: [300, 316],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    9: {
        pts: "400,400 300,300 400,200",
        o: [367, 300],
        plan: [360, 300],
        num: [316, 300],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    10: {
        pts: "400,200 300,300 200,200 300,100",
        o: [300, 200],
        plan: [300, 190],
        num: [300, 252],
        cols: 3,
        rows: 4,
        fs: 11,
    },
    11: {
        pts: "400,200 300,100 400,0",
        o: [367, 100],
        plan: [360, 100],
        num: [316, 100],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
    12: {
        pts: "400,0 300,100 200,0",
        o: [300, 33],
        plan: [300, 36],
        num: [300, 84],
        cols: 2,
        rows: 3,
        fs: 9.5,
    },
};

function layoutPlanets(planets = [], g) {
    const n = planets.length;
    if (!n) return [];
    let fs = g.fs,
        rows = g.rows;
    const cols = Math.min(g.cols, Math.ceil(n / rows));
    // too many planets: shrink font and allow more rows, never leave the house
    while (cols * rows < n && fs > 6.5) {
        fs *= 0.9;
        rows++;
    }
    const usedRows = Math.ceil(n / cols);
    const lh = fs * 1.28;
    const maxLen = Math.max(...planets.map((p) => p.text.length));
    const colW = Math.max(fs * 3.6, maxLen * fs * 0.6);
    return planets.map((p, i) => {
        const r = Math.floor(i / cols),
            c = i % cols;
        const inRow = r === usedRows - 1 ? n - r * cols : cols; // centre the last row
        return {
            text: p.text,
            color: p.color,
            fs,
            x: g.plan[0] + (c - (inRow - 1) / 2) * colW,
            y: g.plan[1] + (r - (usedRows - 1) / 2) * lh,
        };
    });
}

// if chartHouses is a prop, use props.chartHouses instead of chartHouses.value
const houseViews = computed(() =>
    Object.entries(GEOM).map(([k, g]) => {
        const n = Number(k);
        const h = chartHouses.value?.[n];
        return {
            n,
            pts: g.pts,
            o: g.o,
            num: g.num,
            rashi: h?.rashiNum,
            planets: layoutPlanets(h?.planets, g),
        };
    }),
);

// base layer + a zoomed copy of the hovered house on top (no flicker, no DOM reordering)
const chartLayers = computed(() => {
    const base = houseViews.value.map((h) => ({
        ...h,
        key: `b${h.n}`,
        zoom: false,
    }));
    const hv = houseViews.value.find((h) => h.n === hoveredHouse.value);
    return hv ? [...base, { ...hv, key: `z${hv.n}`, zoom: true }] : base;
});

const zoomTransform = (o, s = 1.18) =>
    `translate(${o[0]} ${o[1]}) scale(${s}) translate(${-o[0]} ${-o[1]})`;
</script>

<template>
    <Head title="Vedic Janam Kundli Report" />
    <Header :user="user" />

    <section
        class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen"
    >
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1
                    class="text-3xl font-extrabold text-gray-900 tracking-tight"
                >
                    Vedic Astrology Kundli Report
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Dynamic astrological chart generator with interactive D1
                    Chart & live city autocomplete.
                </p>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100 relative"
            >
                <form
                    @submit.prevent="submit"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end"
                >
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-700 uppercase mb-1"
                            >Full Name</label
                        >
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-700 uppercase mb-1"
                            >Date of Birth</label
                        >
                        <input
                            type="date"
                            v-model="form.dob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-700 uppercase mb-1"
                            >Time of Birth</label
                        >
                        <input
                            type="time"
                            v-model="form.tob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div class="relative">
                        <label
                            class="block text-xs font-semibold text-gray-700 uppercase mb-1"
                            >Birth Place</label
                        >
                        <input
                            type="text"
                            v-model="citySearch"
                            @input="searchCities"
                            placeholder="Search city..."
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
                                <span class="font-bold text-gray-800">{{
                                    city.name
                                }}</span>
                                <span
                                    v-if="city.state"
                                    class="text-xs text-gray-500 ml-1"
                                    >({{ city.state }},
                                    {{ city.countryName }})</span
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{
                                form.processing
                                    ? "Calculating..."
                                    : "Generate Kundli"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Display -->
            <div v-if="kundliData" class="space-y-8">
                <!-- Birth Details & Panchang -->
                <div
                    class="bg-white rounded-xl shadow-md p-6 border border-gray-100"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 border-b gap-4"
                    >
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ kundliData.native_name }}'s Vedic Kundli
                                Report
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                DOB: {{ kundliData.dob }} | TOB:
                                {{ kundliData.tob }} | Place:
                                {{ kundliData.birth_place }}
                            </p>
                        </div>
                        <div
                            class="bg-orange-50 px-4 py-2 rounded-lg border border-orange-200 text-xs font-semibold text-orange-800"
                        >
                            {{ kundliData.ayanamsa }} | Lat:
                            {{ kundliData.latitude }} | Lon:
                            {{ kundliData.longitude }}
                        </div>
                    </div>

                    <!-- Panchang Grid -->
                    <div class="mt-6">
                        <h3
                            class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3"
                        >
                            Panchang & Core Details at Birth
                        </h3>
                        <div
                            class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-gray-50 p-4 rounded-lg border"
                        >
                            <div>
                                <strong class="text-gray-600">Tithi:</strong>
                                {{ kundliData.basic_details.tithi }}
                            </div>
                            <div>
                                <strong class="text-gray-600">Karana:</strong>
                                {{ kundliData.basic_details.karan }}
                            </div>
                            <div>
                                <strong class="text-gray-600">Yoga:</strong>
                                {{ kundliData.basic_details.yoga }}
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Nakshatra:</strong
                                >
                                {{ kundliData.basic_details.nakshatra }} (Pada
                                {{ kundliData.basic_details.charan }})
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Nakshatra Lord:</strong
                                >
                                {{ kundliData.basic_details.nakshatra_lord }}
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Ascendant (Lagna):</strong
                                >
                                {{ kundliData.basic_details.lagna }}
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Moon Sign (Rashi):</strong
                                >
                                {{ kundliData.basic_details.moon_sign }}
                            </div>
                            <div>
                                <strong class="text-gray-600">Sun Sign:</strong>
                                {{ kundliData.basic_details.sun_sign }}
                            </div>
                            <div>
                                <strong class="text-gray-600">Sunrise:</strong>
                                {{ kundliData.basic_details.sunrise }}
                            </div>
                            <div>
                                <strong class="text-gray-600">Sunset:</strong>
                                {{ kundliData.basic_details.sunset }}
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Gan / Yoni:</strong
                                >
                                {{ kundliData.basic_details.gan }} /
                                {{ kundliData.basic_details.yoni }}
                            </div>
                            <div>
                                <strong class="text-gray-600"
                                    >Nadi / Tatva:</strong
                                >
                                {{ kundliData.basic_details.nadi }} /
                                {{ kundliData.basic_details.tatva }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- North Indian Lagna Chart & Avakhada Chakra -->
                <div
                    class="bg-white rounded-xl shadow-md p-6 border border-gray-100 flex flex-col items-center"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 mb-4 self-start w-full pb-2 border-b"
                    >
                        Lagna Kundli (D1 Chart)
                    </h3>

                    <!-- the ONLY outer border lives here -->
                    <div
                        class="w-full max-w-sm overflow-hidden rounded-lg border-2 border-orange-600 bg-[#fefae0] shadow-md"
                    >
                        <svg
                            viewBox="0 0 400 400"
                            class="block w-full h-auto select-none"
                            @pointerleave="hoveredHouse = null"
                        >
                            <defs>
                                <clipPath id="d1-chart-clip">
                                    <rect
                                        x="0"
                                        y="0"
                                        width="400"
                                        height="400"
                                    />
                                </clipPath>
                            </defs>

                            <g
                                v-for="h in chartLayers"
                                :key="h.key"
                                :clip-path="
                                    h.zoom ? 'url(#d1-chart-clip)' : null
                                "
                                :class="h.zoom ? 'house-zoom' : 'house-base'"
                                @pointerenter="!h.zoom && (hoveredHouse = h.n)"
                                @click="!h.zoom && (hoveredHouse = h.n)"
                            >
                                <g
                                    :transform="
                                        h.zoom ? zoomTransform(h.o, 1.15) : null
                                    "
                                >
                                    <polygon
                                        :points="h.pts"
                                        :fill="
                                            h.zoom
                                                ? '#fff4d6'
                                                : h.n === 1
                                                  ? '#fdecc8'
                                                  : '#fefae0'
                                        "
                                        :stroke="h.zoom ? '#c2410c' : '#ea580c'"
                                        :stroke-width="h.zoom ? 2 : 1.25"
                                        stroke-linejoin="round"
                                    />
                                    <text
                                        :x="h.num[0]"
                                        :y="h.num[1]"
                                        font-size="13"
                                        font-weight="700"
                                        fill="#ea580c"
                                        text-anchor="middle"
                                        dominant-baseline="central"
                                    >
                                        {{ h.rashi }}
                                    </text>
                                    <text
                                        v-for="(p, i) in h.planets"
                                        :key="i"
                                        :x="p.x"
                                        :y="p.y"
                                        :font-size="p.fs"
                                        font-weight="600"
                                        :fill="p.color"
                                        text-anchor="middle"
                                        dominant-baseline="central"
                                    >
                                        {{ p.text }}
                                    </text>
                                </g>
                            </g>
                        </svg>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            <template v-if="hoveredHouse">
                                House {{ hoveredHouse }} · Rashi
                                {{ chartHouses[hoveredHouse]?.rashiNum }}
                            </template>
                            <template v-else
                                >North Indian style D1 Chart. Hover (or tap) a
                                house to zoom it. Orange numbers are Rashi signs
                                (1=Aries ... 12=Pisces).</template
                            >
                        </p>
                    </div>

                    <!-- Avakhada & Dosha Summary -->
                    <div
                        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 flex flex-col justify-between"
                    >
                        <div>
                            <h3
                                class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b"
                            >
                                Avakhada Details
                            </h3>
                            <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                                <div>
                                    <strong class="text-gray-600"
                                        >Varna:</strong
                                    >
                                    {{ kundliData.basic_details.varna }}
                                </div>
                                <div>
                                    <strong class="text-gray-600"
                                        >Vashya:</strong
                                    >
                                    {{ kundliData.basic_details.vashya }}
                                </div>
                                <div>
                                    <strong class="text-gray-600">Yoni:</strong>
                                    {{ kundliData.basic_details.yoni }}
                                </div>
                                <div>
                                    <strong class="text-gray-600">Gan:</strong>
                                    {{ kundliData.basic_details.gan }}
                                </div>
                                <div>
                                    <strong class="text-gray-600">Nadi:</strong>
                                    {{ kundliData.basic_details.nadi }}
                                </div>
                                <div>
                                    <strong class="text-gray-600">Paya:</strong>
                                    {{ kundliData.basic_details.paya }}
                                </div>
                            </div>

                            <h3
                                class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b"
                            >
                                Dosha Analysis
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="bg-orange-50 p-3 rounded-lg border">
                                    <span class="font-bold text-gray-800"
                                        >Manglik Dosha:</span
                                    >
                                    <span
                                        class="ml-2 font-semibold"
                                        :class="
                                            kundliData.doshas.manglik_dosha
                                                .is_present
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{
                                            kundliData.doshas.manglik_dosha
                                                .status
                                        }}
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{
                                            kundliData.doshas.manglik_dosha
                                                .description
                                        }}
                                    </p>
                                </div>
                                <div class="bg-orange-50 p-3 rounded-lg border">
                                    <span class="font-bold text-gray-800"
                                        >Kalsarpa Dosha:</span
                                    >
                                    <span
                                        class="ml-2 font-semibold text-red-600"
                                        >{{
                                            kundliData.doshas.kalsarpa_dosha
                                                .type
                                        }}</span
                                    >
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{
                                            kundliData.doshas.kalsarpa_dosha
                                                .description
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Planetary Positions Table -->
                <div
                    class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto"
                >
                    <h3
                        class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b"
                    >
                        Planetary Positions (Nirayana)
                    </h3>
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
                            <tr
                                v-for="p in kundliData.planetary_positions"
                                :key="p.planet"
                                class="border-b hover:bg-gray-50"
                            >
                                <td class="p-3 font-bold text-gray-900">
                                    {{ p.planet }}
                                </td>
                                <td class="p-3">{{ p.sign }}</td>
                                <td class="p-3 text-gray-600">
                                    {{ p.sign_lord }}
                                </td>
                                <td class="p-3 font-semibold text-orange-600">
                                    {{ p.house }}
                                </td>
                                <td class="p-3 font-mono text-xs">
                                    {{ p.degree }}
                                </td>
                                <td class="p-3">{{ p.nakshatra }}</td>
                                <td class="p-3 font-semibold text-center">
                                    {{ p.pada ?? 1 }}
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ p.nakshatra_lord }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Vimshottari Mahadasha Table -->
                <div
                    class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto"
                >
                    <h3
                        class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b"
                    >
                        Vimshottari Mahadasha Periods
                    </h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Mahadasha Planet</th>
                                <th class="p-3">Duration</th>
                                <th class="p-3">Start Date</th>
                                <th class="p-3">End Date</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="dasha in kundliData.vimshottari_dasha"
                                :key="dasha.planet"
                                class="border-b hover:bg-gray-50"
                            >
                                <td class="p-3 font-bold text-gray-900">
                                    {{ dasha.planet }}
                                </td>
                                <td class="p-3">{{ dasha.duration }}</td>
                                <td class="p-3">{{ dasha.start }}</td>
                                <td class="p-3">{{ dasha.end }}</td>
                                <td class="p-3">
                                    <span
                                        v-if="dasha.active"
                                        class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800 font-bold"
                                        >ACTIVE</span
                                    >
                                    <span v-else class="text-gray-500 text-xs"
                                        >Completed</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
<style scoped>
.house-base {
    cursor: pointer;
}
.house-zoom {
    pointer-events: none;
    animation: houseIn 0.15s ease-out;
}
.house-zoom polygon {
    filter: drop-shadow(0 3px 6px rgba(124, 45, 18, 0.3));
}
@keyframes houseIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
