<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from "dayjs";
import SetPricing from './SetPricing.vue';

const props = defineProps({
    astrologers: Object,
})

const columns = [
    { label: "Astrologer", field: "astrologer" },
    { label: "Expertise", field: "expertise" },
    { label: "Experience", field: "experience" },
    { label: "Status", field: "status" },
    { label: "Updated By", field: "verified_by" },
    { label: "Joined On", field: "created_at" },
    { label: "Documents", field: "documents" },
    { label: "Actions", field: "actions", sortable: false },
]

function updateStatus(astrologerId, status) {
    router.post(route('admin.astrologers.updateStatus', astrologerId), { status })
}

</script>

<template>

    <Head title="Astrologers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Astrologers
            </h2>
        </template>
        <div class="container p-4 mx-auto max-w-7xl">

            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="astrologers" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'astrologer'" class="flex items-center gap-3">
                        <div>
                            <img :src="props.row.astrologer?.profile_image" :alt="props.row.name"
                                class="w-32 h-auto rounded">
                        </div>
                        <div>
                            <div class="font-semibold">{{ props.row.name }}</div>
                            <div class="text-sm text-gray-500 lowercase">{{ props.row.email }}</div>
                            <div class="text-sm text-gray-500 lowercase">{{ props.row.phone }}</div>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'experience'">
                        {{ props.row.astrologer?.experience_years }} Years
                    </span>
                    <span v-else-if="props.column.field === 'status'">
                        <span v-if="props.row.astrologer?.status === 'pending_verification'"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">
                            Verification Pending
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'verified'"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Verified
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'active'"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            Active
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'rejected'"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            Rejected
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'verified_by'">
                        <div class="font-semibold">{{ props.row.astrologer?.verifier?.name }}</div>
                        <div class="text-sm text-gray-500 lowercase">{{ props.row.astrologer?.verifier?.email }}</div>
                    </span>
                    <span v-else-if="props.column.field === 'expertise'">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="expertise in props.row.astrologer?.expertise" :key="expertise"
                                class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">
                                {{ expertise }}
                            </span>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'documents'">
                        <div v-for="doc in props.row.astrologer.documents">
                            <a :href="doc.document_path" target="_blank">{{ doc.name }}</a>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="flex gap-2 items-center">
                        <SetPricing :astrologer="props.row.astrologer" />

                        <!-- Pending Verification: show Verify + Reject -->
                        <template v-if="props.row.astrologer?.status === 'pending_verification'">
                            <button @click="updateStatus(props.row.astrologer.id, 'verified')"
                                class="px-3 py-1 text-xs font-semibold rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Verify
                            </button>
                        </template>

                        <!-- Verified: allow Activate or Reject -->
                        <template v-else-if="props.row.astrologer?.status === 'verified'">
                            <button @click="updateStatus(props.row.astrologer.id, 'active')"
                                class="px-3 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 hover:bg-blue-200">
                                Activate
                            </button>
                        </template>


                        <!-- Rejected: optionally allow re‑verification -->
                        <template v-if="props.row.astrologer?.status === 'rejected'">
                            <button @click="updateStatus(props.row.astrologer.id, 'pending_verification')"
                                class="px-3 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                Re‑Verify
                            </button>
                        </template>
                        <!-- Active: allow Reject (or deactivate if you want) -->
                        <template v-else="props.row.astrologer?.status === 'active'">
                            <button @click="updateStatus(props.row.astrologer.id, 'rejected')"
                                class="px-3 py-1 text-xs font-semibold rounded bg-red-100 text-red-800 hover:bg-red-200">
                                Reject
                            </button>
                        </template>
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