<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Create from './Create.vue';
import { Icon } from '@iconify/vue';
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

</script>

<template>

    <Head title="Packages" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Packages
            </h2>
        </template>
        <div class="container p-4 mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold"></h2>
                <Create />
            </div>

            <!-- Table for active tab -->
            <VueGoodTable :columns="columns" :rows="packages" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'recommended'">
                        <span v-if="props.row.recommended"
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Yes
                        </span>
                        <span v-else
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            No
                        </span>
                    </span>

                    <span v-else-if="props.column.field === 'actions'" class="flex gap-2 items-center">
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

<style>
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}
</style>