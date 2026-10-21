<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
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

const list = computed(() => Array.from(props.transactions || []))
const sum = (key) => list.value.reduce((a, t) => a + (+t[key] || 0), 0)
const money = (n) => '₹' + (+n || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 })
</script>

<template>

    <Head title="Transactions" />
    <UserLayout>
        <div class="usr-container">
            <div class="usr-head">
                <div>
                    <h1>Transactions</h1>
                    <p>Your wallet recharge history.</p>
                </div>
                <Link :href="route('user.recharge')" class="usr-btn">
                    <Icon icon="mdi:plus" width="18" height="18" /> Add money
                </Link>
            </div>

            <div class="usr-stats">
                <div class="usr-stat">
                    <span class="usr-stat-icon"><Icon icon="mdi:cash-plus" width="24" height="24" /></span>
                    <div><strong>{{ money(sum('amount')) }}</strong><span>Total recharged</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-green"><Icon icon="mdi:gift-outline" width="24" height="24" /></span>
                    <div><strong>{{ money(sum('bonus_amount')) }}</strong><span>Bonus earned</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-blue"><Icon icon="mdi:receipt-text-outline" width="24" height="24" /></span>
                    <div><strong>{{ money(sum('payable_amount')) }}</strong><span>Amount paid (incl. GST)</span></div>
                </div>
            </div>

            <div class="usr-card usr-table">
                <VueGoodTable :columns="columns" :rows="transactions" :paginate="true"
                    :search-options="{ enabled: true }" :lineNumbers="true"
                    :pagination-options="{ enabled: true, perPage: 10 }">
                    <template #table-row="props">
                        <span v-if="props.column.field === 'amount'" class="usr-strong">
                            ₹ {{ props.row.amount }}
                        </span>
                        <span v-else-if="props.column.field === 'bonus_amount'">
                            <span v-if="+props.row.bonus_amount" class="usr-badge usr-badge-green">+ ₹ {{ props.row.bonus_amount }}</span>
                            <span v-else class="usr-muted">—</span>
                        </span>
                        <span v-else-if="props.column.field === 'payable_amount'" class="usr-strong">
                            ₹ {{ props.row.payable_amount }}
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
        </div>
    </UserLayout>
</template>