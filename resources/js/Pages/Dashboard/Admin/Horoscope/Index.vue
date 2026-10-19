<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Create from './Create.vue';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import { ref, computed } from 'vue'

const props = defineProps({
    horoscopes: Array,
    user: Object
})

const columns = [
    { label: "Type", field: "type" },
    { label: "Sign", field: "sign" },
    { label: "Actions", field: "actions", sortable: false },
]

// active tab state
const activeTab = ref('daily')

// group horoscopes by type
const groupedHoroscopes = computed(() => {
    return {
        daily: props.horoscopes.filter(h => h.type === 'daily'),
        weekly: props.horoscopes.filter(h => h.type === 'weekly'),
        monthly: props.horoscopes.filter(h => h.type === 'monthly'),
        yearly: props.horoscopes.filter(h => h.type === 'yearly'),
    }
})
</script>

<template>

    <Head title="Horoscopes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="adm-title">Horoscopes</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Daily, weekly, monthly and yearly predictions for every zodiac sign.</p>
            <Create />
        </div>

        <!-- Tabs -->
        <div class="adm-tabs">
            <button v-for="tab in ['daily', 'weekly', 'monthly', 'yearly']" :key="tab" @click="activeTab = tab"
                class="adm-tab" :class="{ 'is-active': activeTab === tab }">
                {{ tab.toUpperCase() }}
                <span class="adm-tab-count">{{ groupedHoroscopes[tab].length }}</span>
            </button>
        </div>

        <!-- Table for active tab -->
        <div class="adm-card adm-table">
            <VueGoodTable v-if="groupedHoroscopes[activeTab].length" :columns="columns"
                :rows="groupedHoroscopes[activeTab]" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'type'">
                        <span class="adm-strong" style="text-transform: uppercase">{{ props.row.type }}</span>
                        <span v-if="props.row.type === 'daily'" class="adm-muted">{{ props.row.date }}</span>
                        <span v-else-if="props.row.type === 'weekly'" class="adm-muted">{{ props.row.week_key }}</span>
                        <span v-else-if="props.row.type === 'monthly'" class="adm-muted">{{ props.row.month_key }}</span>
                        <span v-else-if="props.row.type === 'yearly'" class="adm-muted">{{ props.row.year_key }}</span>
                    </span>
                    <span v-else-if="props.column.field === 'sign'">
                        <span class="adm-badge adm-badge-orange">{{ props.row.sign }}</span>
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="adm-row">
                        <Link
                            :href="route('horoscope', { type: props.row.type, sign: props.row.sign, key: (user.roles[0].name === 'Admin' ? props.row.date || props.row.week_key || props.row.month_key || props.row.year_key : null) })"
                            class="adm-btn adm-btn-sm">
                            <Icon icon="mdi:eye-outline" width="16" height="16" />
                            View
                        </Link>
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>

            <div v-else class="adm-empty">
                No horoscopes available for {{ activeTab }}.
            </div>
        </div>
    </AuthenticatedLayout>
</template>