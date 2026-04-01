<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from 'dayjs'

const props = defineProps({
    transactions: Object,
})

const columns = [
    { label: "Package Amount", field: "amount" },
    { label: "Bonus Amount", field: "bonus_amount" },
    { label: "Amount Paid", field: "payable_amount" },
    { label: "Date", field: "date" },
]


</script>

<template>

    <Head title="Transactions" />
    <UserLayout>
        <div class="container p-4 mx-auto">
            <VueGoodTable :columns="columns" :rows="transactions" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'amount'">
                        <div>₹ {{ props.row.amount }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'bonus_amount'">
                        <div>₹ {{ props.row.bonus_amount }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'payable_amount'">
                        <div class="font-semibold">₹ {{ props.row.payable_amount }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'date'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>
        </div>


    </UserLayout>
</template>

<style>
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}
</style>
