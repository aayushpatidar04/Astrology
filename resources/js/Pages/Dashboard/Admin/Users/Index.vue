<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from "dayjs";

const props = defineProps({
    users: Object,
})

const columns = [
    { label: "User", field: "user" },
    { label: "Birth Date-Time", field: "dob-time" },
    { label: "Gender", field: "gender" },
    { label: "Location", field: "location" },
    { label: "Languages", field: "languages" },
    { label: "Joined On", field: "created_at" },
    { label: "Actions", field: "actions", sortable: false },
]



</script>

<template>

    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Users
            </h2>
        </template>
        <div class="container p-4 mx-auto max-w-7xl">

            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="users" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'user'" class="flex items-center gap-3">
                        <div>
                            <img :src="props.row.details?.profile_image" :alt="props.row.name"
                                class="w-32 h-auto rounded">
                        </div>
                        <div>
                            <div class="font-semibold">{{ props.row.name }}</div>
                            <div class="text-sm text-gray-500 lowercase">{{ props.row.email }}</div>
                            <div class="text-sm text-gray-500 lowercase">{{ props.row.phone }}</div>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'dob-time'">
                        {{ dayjs(props.row.details?.dob).format("D MMM, YYYY") }} {{ props.row.details?.birth_time }}
                    </span>
                    <span v-else-if="props.column.field === 'gender'">
                        <div class="uppercase">{{ props.row.details?.gender }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'location'">
                        <div>{{ props.row.details?.location }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'languages'">
                        <div>{{ props.row.details?.preferred_language }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="flex gap-2 items-center">
                        
                    </span>

                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>

        </div>

    </AuthenticatedLayout>
</template>


<style>
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}
</style>