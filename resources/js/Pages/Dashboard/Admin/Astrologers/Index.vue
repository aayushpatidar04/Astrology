<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import dayjs from "dayjs";
import SetPricing from './SetPricing.vue';
import AddTransaction from './AddTransaction.vue';

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
            <h2 class="adm-title">Astrologers</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Verify astrologers, set their pricing and record payouts.</p>
        </div>

        <div class="adm-card adm-table">
            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="astrologers" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <div v-if="props.column.field === 'astrologer'" class="adm-cell-user">
                        <img v-if="props.row.astrologer?.profile_image" :src="props.row.astrologer?.profile_image"
                            :alt="props.row.name" class="adm-avatar">
                        <span v-else class="adm-avatar">{{ props.row.name?.charAt(0)?.toUpperCase() }}</span>
                        <div>
                            <span class="adm-strong">{{ props.row.name }}</span>
                            <span class="adm-muted adm-lc">{{ props.row.email }}</span>
                            <span class="adm-muted adm-lc">{{ props.row.phone }}</span>
                        </div>
                    </div>
                    <span v-else-if="props.column.field === 'experience'">
                        {{ props.row.astrologer?.experience_years }} Years
                    </span>
                    <span v-else-if="props.column.field === 'status'">
                        <span v-if="props.row.astrologer?.status === 'pending_verification'"
                            class="adm-badge adm-badge-yellow">
                            <span class="adm-dot"></span> Verification Pending
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'verified'" class="adm-badge adm-badge-green">
                            <span class="adm-dot"></span> Verified
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'active'" class="adm-badge adm-badge-blue">
                            <span class="adm-dot"></span> Active
                        </span>

                        <span v-else-if="props.row.astrologer?.status === 'rejected'" class="adm-badge adm-badge-red">
                            <span class="adm-dot"></span> Rejected
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'verified_by'">
                        <span class="adm-strong">{{ props.row.astrologer?.verifier?.name }}</span>
                        <span class="adm-muted adm-lc">{{ props.row.astrologer?.verifier?.email }}</span>
                    </span>
                    <span v-else-if="props.column.field === 'expertise'">
                        <span class="adm-row">
                            <span v-for="expertise in props.row.astrologer?.expertise" :key="expertise"
                                class="adm-chip">
                                {{ expertise }}
                            </span>
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'documents'">
                        <div v-for="doc in props.row.astrologer?.documents" :key="doc.document_path">
                            <a :href="doc.document_path" target="_blank" class="adm-link">
                                <Icon icon="mdi:file-document-outline" width="16" height="16" />
                                {{ doc.name }}
                            </a>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="adm-row adm-actions">
                        <SetPricing :astrologer="props.row.astrologer" />

                        <!-- Pending Verification: show Verify + Reject -->
                        <template v-if="props.row.astrologer?.status === 'pending_verification'">
                            <button @click="updateStatus(props.row.astrologer.id, 'verified')"
                                class="adm-pill adm-pill-green">
                                Verify
                            </button>
                        </template>

                        <!-- Verified: allow Activate or Reject -->
                        <template v-else-if="props.row.astrologer?.status === 'verified'">
                            <button @click="updateStatus(props.row.astrologer.id, 'active')"
                                class="adm-pill adm-pill-blue">
                                Activate
                            </button>
                        </template>


                        <!-- Rejected: optionally allow re‑verification -->
                        <template v-if="props.row.astrologer?.status === 'rejected'">
                            <button @click="updateStatus(props.row.astrologer.id, 'pending_verification')"
                                class="adm-pill adm-pill-yellow">
                                Re‑Verify
                            </button>
                        </template>
                        <!-- Everything else: allow Reject -->
                        <template v-else>
                            <button @click="updateStatus(props.row.astrologer.id, 'rejected')"
                                class="adm-pill adm-pill-red">
                                Reject
                            </button>
                        </template>

                        <AddTransaction :astrologer="props.row.astrologer" />
                    </span>

                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>
        </div>

    </AuthenticatedLayout>
</template>