<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Create from './Create.vue';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import { ref, computed } from 'vue'

const props = defineProps({
    horoscopes: Array
})

const columns = [
    { label: "Type", field: "type" },
    { label: "Sign", field: "sign" },
    { label: "Actions", field: "actions", sortable: false },
]

// active tab state
const activeTab = ref('daily')

// group horoscopes by type
const groupedHoroscopes = computed(() => {
    return {
        daily: props.horoscopes.filter(h => h.type === 'daily'),
        weekly: props.horoscopes.filter(h => h.type === 'weekly'),
        monthly: props.horoscopes.filter(h => h.type === 'monthly'),
        yearly: props.horoscopes.filter(h => h.type === 'yearly'),
    }
})
</script>

<template>

    <Head title="Horoscopes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Horoscopes
            </h2>
        </template>

        <div class="container p-4 mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold"></h2>
                <Create />
            </div>

            <!-- Tabs -->
            <div class="flex space-x-4 border-b mb-4">
                <button v-for="tab in ['daily', 'weekly', 'monthly', 'yearly']" :key="tab" @click="activeTab = tab" :class="['px-4 py-2 font-semibold',
                    activeTab === tab ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500']">
                    {{ tab.toUpperCase() }} ({{ groupedHoroscopes[tab].length }})
                </button>
            </div>

            <!-- Table for active tab -->
            <VueGoodTable v-if="groupedHoroscopes[activeTab].length" :columns="columns"
                :rows="groupedHoroscopes[activeTab]" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'type'">
                        <div>
                            <div class="font-semibold uppercase">{{ props.row.type }}</div>
                            <div v-if="props.row.type === 'daily'" class="text-sm text-gray-500 lowercase">{{ props.row.date }}</div>
                            <div v-else-if="props.row.type === 'weekly'" class="text-sm text-gray-500 lowercase">{{ props.row.week_key }}</div>
                            <div v-else-if="props.row.type === 'monthly'" class="text-sm text-gray-500 lowercase">{{ props.row.month_key }}</div>
                            <div v-else-if="props.row.type === 'yearly'" class="text-sm text-gray-500 lowercase">{{ props.row.year_key }}</div>
                        </div>
                    </span>
                    <!-- <span v-else-if="props.column.field === 'actions'" class="flex">
                        <Link :href="route('blog-details', { slug: props.row.slug })"><button class="px-2 py-1 bg-orange-500 text-white rounded mr-2 mt-1">
                            View
                        </button></Link>
                        <Edit :categories="categories" :blog="props.row" />
                        <Delete :blog="props.row" />
                    </span> -->
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>

            <div v-else class="text-gray-500 italic">
                No horoscopes available for {{ activeTab }}.
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}
</style>
