<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from 'dayjs'

const props = defineProps({
    sessions: Object,
})

const columns = [
    { label: "Astrologer", field: "astrologer" },
    { label: "Duration", field: "duration" },
    { label: "Deduction", field: "deduction" },
    { label: "Ended At", field: "ended_at" },
    { label: "Ended By", field: "ended_by" },
]


</script>

<template>

    <Head title="Chat Sessions" />
    <UserLayout>
        <div class="container p-4 mx-auto">
            <VueGoodTable :columns="columns" :rows="sessions" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'astrologer'">
                        <div class="font-semibold">{{ props.row.astrologer.name }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'duration'">
                        {{ props.row.duration_seconds }} seconds
                    </span>
                    <span v-else-if="props.column.field === 'ended_at'">
                        {{ dayjs(props.row.ended_at).format("D MMM, YYYY HH:mm:ss") }}
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
