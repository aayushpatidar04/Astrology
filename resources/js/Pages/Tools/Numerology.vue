<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
    numerologyData: Object,
    filters: Object,
});

const form = useForm({
    name: props.filters?.name ?? 'Aayush Sharma',
    dob: props.filters?.dob ?? '2000-05-04',
    system: props.filters?.system ?? 'chaldean',
});

function submit() {
    form.get(route('tools.numerology'), {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <Head title="Vedic &amp; Chaldean Numerology Calculator" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-amber-50 via-orange-50/40 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Vedic &amp; Chaldean Numerology Calculator</h1>
                <p class="mt-2 text-sm text-gray-600">Discover your Mulank (Psychic Number), Bhagyank (Life Path), Name Number &amp; Personal Year.</p>
            </div>

            <!-- Input Form -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8 border border-amber-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name (As Commonly Used)</label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Enter your full name"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date of Birth</label>
                        <input
                            type="date"
                            v-model="form.dob"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Numerology System</label>
                        <select
                            v-model="form.system"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 bg-white"
                        >
                            <option value="chaldean">Chaldean System</option>
                            <option value="pythagorean">Pythagorean System</option>
                        </select>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm"
                        >
                            {{ form.processing ? 'Calculating...' : 'Calculate Numerology' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Numerology Report -->
            <div v-if="numerologyData" class="space-y-8">
                <!-- Header Summary -->
                <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-amber-600 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900">{{ numerologyData.native_name }}'s Numerology Profile</h2>
                        <p class="text-xs text-gray-500 mt-1">Date of Birth: {{ numerologyData.dob }} | System: {{ numerologyData.system }}</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl text-xs font-bold text-amber-900">
                        {{ numerologyData.core_numbers.personal_year.label }}: Number {{ numerologyData.core_numbers.personal_year.number }}
                    </div>
                </div>

                <!-- Primary 3 Numbers (Mulank, Bhagyank, Name Number) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Mulank -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                {{ numerologyData.core_numbers.mulank.label }}
                            </span>
                            <span class="w-11 h-11 rounded-xl bg-amber-600 text-white font-black text-xl flex items-center justify-center shadow">
                                {{ numerologyData.core_numbers.mulank.number }}
                            </span>
                        </div>
                        <div class="text-sm font-extrabold text-gray-900">{{ numerologyData.core_numbers.mulank.title }}</div>
                        <div class="text-xs text-amber-700 font-semibold mb-2">Ruler: {{ numerologyData.core_numbers.mulank.planet }}</div>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ numerologyData.core_numbers.mulank.description }}</p>
                    </div>

                    <!-- Bhagyank -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-orange-700">
                                {{ numerologyData.core_numbers.bhagyank.label }}
                            </span>
                            <span class="w-11 h-11 rounded-xl bg-orange-600 text-white font-black text-xl flex items-center justify-center shadow">
                                {{ numerologyData.core_numbers.bhagyank.number }}
                            </span>
                        </div>
                        <div class="text-sm font-extrabold text-gray-900">{{ numerologyData.core_numbers.bhagyank.title }}</div>
                        <div class="text-xs text-orange-700 font-semibold mb-2">Ruler: {{ numerologyData.core_numbers.bhagyank.planet }}</div>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ numerologyData.core_numbers.bhagyank.description }}</p>
                    </div>

                    <!-- Name Number -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">
                                {{ numerologyData.core_numbers.name_number.label }}
                            </span>
                            <span class="w-11 h-11 rounded-xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center shadow">
                                {{ numerologyData.core_numbers.name_number.number }}
                            </span>
                        </div>
                        <div class="text-sm font-extrabold text-gray-900">
                            Compound Total: {{ numerologyData.core_numbers.name_number.compound_total }}
                        </div>
                        <div
                            class="text-xs font-semibold mb-2"
                            :class="numerologyData.core_numbers.name_number.is_aligned ? 'text-green-600' : 'text-amber-600'"
                        >
                            {{ numerologyData.core_numbers.name_number.is_aligned ? '✓ Harmonious with Mulank' : '• Contrasting Vibration' }}
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ numerologyData.core_numbers.name_number.description }}</p>
                    </div>
                </div>

                <!-- Secondary Numbers & Personal Year -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold uppercase text-gray-500">{{ numerologyData.core_numbers.soul_urge.label }}</span>
                            <span class="text-lg font-extrabold text-rose-600">#{{ numerologyData.core_numbers.soul_urge.number }}</span>
                        </div>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ numerologyData.core_numbers.soul_urge.description }}</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold uppercase text-gray-500">{{ numerologyData.core_numbers.personality.label }}</span>
                            <span class="text-lg font-extrabold text-purple-600">#{{ numerologyData.core_numbers.personality.number }}</span>
                        </div>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ numerologyData.core_numbers.personality.description }}</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-5 border border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold uppercase text-gray-500">{{ numerologyData.core_numbers.personal_year.label }}</span>
                            <span class="text-lg font-extrabold text-emerald-600">#{{ numerologyData.core_numbers.personal_year.number }}</span>
                        </div>
                        <p class="text-xs font-semibold text-gray-800">{{ numerologyData.core_numbers.personal_year.theme }}</p>
                    </div>
                </div>

                <!-- Lucky Factors -->
                <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Auspicious &amp; Lucky Factors</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <span class="text-xs font-bold text-amber-800 uppercase block mb-1">Lucky Colors</span>
                            <span class="font-bold text-gray-900 text-xs">{{ numerologyData.lucky_factors.colors }}</span>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <span class="text-xs font-bold text-amber-800 uppercase block mb-1">Lucky Days</span>
                            <span class="font-bold text-gray-900 text-xs">{{ numerologyData.lucky_factors.days }}</span>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <span class="text-xs font-bold text-amber-800 uppercase block mb-1">Friendly Numbers</span>
                            <span class="font-bold text-gray-900 text-xs">{{ numerologyData.lucky_factors.friendly_numbers }}</span>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <span class="text-xs font-bold text-amber-800 uppercase block mb-1">Recommended Gemstone</span>
                            <span class="font-bold text-gray-900 text-xs">{{ numerologyData.lucky_factors.gemstone }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
