<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
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

const list = computed(() => Array.from(props.history || []))
const answered = computed(() => list.value.filter((c) => c.status === 'answered').length)
const totalSeconds = computed(() => list.value.reduce((a, c) => a + (+c.duration || 0), 0))
const totalCost = computed(() => list.value.reduce((a, c) => a + (+c.cost || 0), 0))

const fmtDuration = (sec) => {
    sec = Math.round(+sec || 0)
    const h = Math.floor(sec / 3600)
    const m = Math.floor((sec % 3600) / 60)
    const s = sec % 60
    return [h ? `${h}h` : '', m ? `${m}m` : '', !h ? `${s}s` : ''].filter(Boolean).join(' ')
}

const statusMeta = (s) => ({
    answered: { icon: 'mdi:phone-check', cls: 'usr-badge-green', label: 'Answered' },
    ended: { icon: 'mdi:phone-hangup', cls: 'usr-badge-gray', label: 'Ended' },
    missed: { icon: 'mdi:phone-missed', cls: 'usr-badge-red', label: 'Missed' },
    rejected: { icon: 'mdi:phone-cancel', cls: 'usr-badge-red', label: 'Rejected' },
    failed: { icon: 'mdi:phone-alert', cls: 'usr-badge-gray', label: 'Failed' },
    ringing: { icon: 'mdi:phone-ring', cls: 'usr-badge-yellow', label: 'Ringing' },
}[s] || { icon: 'mdi:phone', cls: 'usr-badge-gray', label: s || '—' })
</script>

<template>

    <Head title="Call History" />
    <UserLayout>
        <div class="usr-container">
            <div class="usr-head">
                <div>
                    <h1>Call History</h1>
                    <p>All your voice calls with astrologers.</p>
                </div>
            </div>

            <div class="usr-stats">
                <div class="usr-stat">
                    <span class="usr-stat-icon"><Icon icon="mdi:phone-log-outline" width="24" height="24" /></span>
                    <div><strong>{{ list.length }}</strong><span>Total calls</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-green"><Icon icon="mdi:phone-check-outline" width="24" height="24" /></span>
                    <div><strong>{{ answered }}</strong><span>Answered</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-blue"><Icon icon="mdi:timer-outline" width="24" height="24" /></span>
                    <div><strong>{{ fmtDuration(totalSeconds) || '0s' }}</strong><span>Talk time</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-purple"><Icon icon="mdi:wallet-outline" width="24" height="24" /></span>
                    <div><strong>₹{{ totalCost.toFixed(2) }}</strong><span>Total spent</span></div>
                </div>
            </div>

            <div class="usr-card usr-table">
                <VueGoodTable :columns="columns" :rows="history" :paginate="true" :search-options="{ enabled: true }"
                    :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                    <template #table-row="props">
                        <div v-if="props.column.field === 'astrologer'" class="usr-cell">
                            <span class="usr-avatar">
                                <img
                                    v-if="props.row.astrologer.astrologer?.profile_image"
                                    :src="props.row.astrologer.astrologer.profile_image"
                                    :alt="props.row.astrologer?.name"
                                    class="w-full h-full rounded-full object-cover"
                                />
                                <template v-else>
                                    {{ props.row.astrologer?.name?.charAt(0)?.toUpperCase() }}
                                </template>
                            </span>
                            <span class="usr-strong">{{ props.row.astrologer.name }}</span>
                        </div>
                        <span v-else-if="props.column.field === 'call_type'" style="text-transform: capitalize">
                            {{ props.row.call_type }}
                        </span>
                        <span v-else-if="props.column.field === 'duration'">
                            {{ fmtDuration(props.row.duration) }}
                        </span>
                        <span v-else-if="props.column.field === 'call_time'">
                            {{ dayjs(props.row.started_at).format("D MMM, YYYY HH:mm:ss") }} -
                            {{ dayjs(props.row.ended_at).format("D MMM, YYYY HH:mm:ss") }}
                        </span>
                        <span v-else-if="props.column.field === 'cost'" class="usr-strong">
                            ₹ {{ props.row.cost }}
                        </span>
                        <span v-else-if="props.column.field === 'status'">
                            <span class="usr-badge" :class="statusMeta(props.row.status).cls">
                                <Icon :icon="statusMeta(props.row.status).icon" width="14" height="14" />
                                {{ statusMeta(props.row.status).label }}
                            </span>
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