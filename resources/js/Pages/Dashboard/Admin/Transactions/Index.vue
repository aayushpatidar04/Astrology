<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Transactions
            </h2>
        </template>

        <div class="container p-4 mx-auto max-w-7xl">
            <VueGoodTable :columns="columns" :rows="transactions.data" :paginate="true"
                :search-options="{ enabled: true }" :lineNumbers="true"
                :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'astrologer'">
                        {{ props.row.astrologer.user.name }}
                    </span>
                    <span v-else-if="props.column.field === 'amount'">
                        ₹{{ props.row.amount }}
                    </span>
                    <span v-else-if="props.column.field === 'proof'">
                        <a v-if="props.row.proof" :href="`/${props.row.proof}`" target="_blank"
                            class="text-blue-600 underline">
                            View Proof
                        </a>
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { VueGoodTable } from "vue-good-table-next"
import "vue-good-table-next/dist/vue-good-table-next.css"

const props = defineProps({
    transactions: Object,
})

const columns = [
    { label: "Astrologer", field: "astrologer" },
    { label: "Amount", field: "amount" },
    { label: "Mode", field: "mode" },
    { label: "Reference No", field: "reference_no" },
    { label: "Remarks", field: "remarks" },
    { label: "Proof", field: "proof" },
    { label: "Date", field: "transacted_at" },
]
</script>
