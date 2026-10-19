<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
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
            <h2 class="adm-title">Users</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Registered users with their birth details.</p>
        </div>

        <div class="adm-card adm-table">
            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="users" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <div v-if="props.column.field === 'user'" class="adm-cell-user">
                        <img v-if="props.row.details?.profile_image" :src="props.row.details?.profile_image"
                            :alt="props.row.name" class="adm-avatar">
                        <span v-else class="adm-avatar">{{ props.row.name?.charAt(0)?.toUpperCase() }}</span>
                        <div>
                            <span class="adm-strong">{{ props.row.name }}</span>
                            <span class="adm-muted adm-lc">{{ props.row.email }}</span>
                            <span class="adm-muted adm-lc">{{ props.row.phone }}</span>
                        </div>
                    </div>
                    <span v-else-if="props.column.field === 'dob-time'">
                        {{ dayjs(props.row.details?.dob).format("D MMM, YYYY") }} {{ props.row.details?.birth_time }}
                    </span>
                    <span v-else-if="props.column.field === 'gender'">
                        <span v-if="props.row.details?.gender" class="adm-badge adm-badge-gray"
                            style="text-transform: uppercase">{{ props.row.details?.gender }}</span>
                    </span>
                    <span v-else-if="props.column.field === 'location'">
                        {{ props.row.details?.location }}
                    </span>
                    <span v-else-if="props.column.field === 'languages'">
                        {{ props.row.details?.preferred_language }}
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="adm-row">

                    </span>

                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>
        </div>

    </AuthenticatedLayout>
</template>