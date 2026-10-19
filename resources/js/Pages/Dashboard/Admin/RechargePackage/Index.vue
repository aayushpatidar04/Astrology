<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Create from './Create.vue';
import Edit from './Edit.vue';
import Delete from './Delete.vue';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";

const props = defineProps({
    packages: Object,
})

const columns = [
    { label: "Amount", field: "amount" },
    { label: "Bonus Amount", field: "bonus_amount" },
    { label: "Label", field: "label" },
    { label: "Recommended", field: "recommended" },
    { label: "Type", field: "type" },
    { label: "Actions", field: "actions", sortable: false },
]

// "first_time" -> "First Time"
const prettyType = (type) =>
    (type || '').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
</script>

<template>

    <Head title="Packages" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="adm-title">Packages</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Wallet recharge packages shown to users.</p>
            <Create />
        </div>

        <div class="adm-card adm-table">
            <VueGoodTable :columns="columns" :rows="packages" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'amount'" class="adm-strong">
                        ₹{{ props.row.amount }}
                    </span>
                    <span v-else-if="props.column.field === 'bonus_amount'">
                        <span v-if="props.row.bonus_amount" style="color: #15803d; font-weight: 600">+₹{{ props.row.bonus_amount }}</span>
                        <span v-else class="adm-muted">—</span>
                    </span>
                    <span v-else-if="props.column.field === 'recommended'">
                        <span v-if="props.row.recommended" class="adm-badge adm-badge-green">
                            <span class="adm-dot"></span> Yes
                        </span>
                        <span v-else class="adm-badge adm-badge-red">
                            <span class="adm-dot"></span> No
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'type'">
                        <span class="adm-badge adm-badge-blue">{{ prettyType(props.row.type) }}</span>
                    </span>

                    <span v-else-if="props.column.field === 'actions'" class="adm-row">
                        <Edit :recharge="props.row" />
                        <Delete :recharge="props.row" />
                    </span>

                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>
        </div>

    </AuthenticatedLayout>
</template>