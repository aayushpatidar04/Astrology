<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from 'dayjs'

const props = defineProps({
    history: Object,
})

const columns = [
    { label: "Astrologer", field: "astrologer" },
    { label: "Type", field: "call_type" },
    { label: "Status", field: "status" },
    { label: "Call Time", field: "call_time" },
    { label: "Duration", field: "duration" },
    { label: "Cost", field: "cost" },
]


</script>

<template>

    <Head title="Chat Sessions" />
    <UserLayout>
        <div class="container p-4 mx-auto">
            <VueGoodTable :columns="columns" :rows="history" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'astrologer'">
                        <div class="font-semibold">{{ props.row.astrologer.name }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'duration'">
                        {{ props.row.duration }} seconds
                    </span>
                    <span v-else-if="props.column.field === 'call_time'">
                        {{ dayjs(props.row.started_at).format("D MMM, YYYY HH:mm:ss") }} - {{
                            dayjs(props.row.ended_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'cost'">
                        ₹ {{ props.row.cost }}
                    </span>
                    <span v-else-if="props.column.field === 'status'">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="{
                            'bg-red-100 text-red-600': props.row.status === 'missed',
                            'bg-yellow-100 text-yellow-600': props.row.status === 'answered',
                            'bg-green-100 text-green-600': props.row.status === 'ended',
                            'bg-gray-200 text-gray-600': props.row.status === 'failed'
                        }">
                            {{ props.row.status }}
                        </span>
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
