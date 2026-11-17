<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
    milanData: Object,
    filters: Object,
})

const form = useForm({
    boy_name: props.filters?.boy_name ?? 'Rahul',
    boy_dob: props.filters?.boy_dob ?? '1992-05-10',
    girl_name: props.filters?.girl_name ?? 'Priya',
    girl_dob: props.filters?.girl_dob ?? '1994-08-20',
})

function submit() {
    form.get(route('tools.matchmaking'), {
        preserveState: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="Kundli Milan & Matchmaking" />
    <Header :user="user" />

    <section class="py-12 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">Kundli Milan (Gun Milan for Marriage)</h1>
                <p class="mt-2 text-sm text-gray-600">Check compatibility score out of 36 points based on Ashta Koota matching.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Boy's Name</label>
                        <input type="text" v-model="form.boy_name" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Boy's DOB</label>
                        <input type="date" v-model="form.boy_dob" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Girl's Name</label>
                        <input type="text" v-model="form.girl_name" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Girl's DOB</label>
                        <input type="date" v-model="form.girl_dob" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500" />
                    </div>
                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            {{ form.processing ? 'Matching...' : 'Match Kundli' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Matchmaking Results -->
            <div v-if="milanData" class="space-y-8">
                <!-- Score Summary Card -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Total Guna Milan Score</h3>
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-orange-100 border-4 border-orange-500 text-3xl font-extrabold text-orange-600 mb-4">
                        {{ milanData.total_guna }} / {{ milanData.max_guna }}
                    </div>
                    <p class="text-base font-semibold text-gray-800">{{ milanData.conclusion }}</p>
                </div>

                <!-- Detailed Ashta Koota Table -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 overflow-x-auto">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b">Ashta Koota Breakdown (36 Points)</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3">Koota</th>
                                <th class="p-3">Obtained / Max</th>
                                <th class="p-3">Significance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="koota in milanData.kootas" :key="koota.name" class="border-b hover:bg-gray-50">
                                <td class="p-3 font-bold text-gray-900">{{ koota.name }}</td>
                                <td class="p-3 font-semibold text-orange-600">{{ koota.score }} / {{ koota.max }}</td>
                                <td class="p-3 text-gray-600">{{ koota.description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
