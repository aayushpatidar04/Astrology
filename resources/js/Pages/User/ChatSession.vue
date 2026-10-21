<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
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

const list = computed(() => Array.from(props.sessions || []))

const totalSeconds = computed(() => list.value.reduce((a, s) => a + (+s.duration_seconds || 0), 0))
const totalSpent = computed(() => list.value.reduce((a, s) => a + (+s.deduction || 0), 0))

const fmtDuration = (sec) => {
    sec = Math.round(+sec || 0)
    const h = Math.floor(sec / 3600)
    const m = Math.floor((sec % 3600) / 60)
    const s = sec % 60
    return [h ? `${h}h` : '', m ? `${m}m` : '', !h ? `${s}s` : ''].filter(Boolean).join(' ')
}

const endedMeta = (who) => ({
    user: { label: 'You', cls: 'usr-badge-blue' },
    astrologer: { label: 'Astrologer', cls: 'usr-badge-purple' },
    balance: { label: 'Low balance', cls: 'usr-badge-red' },
}[who] || { label: who || '—', cls: 'usr-badge-gray' })
</script>

<template>

    <Head title="Chat Sessions" />
    <UserLayout>
        <div class="usr-container">
            <div class="usr-head">
                <div>
                    <h1>Chat Sessions</h1>
                    <p>Every conversation you've had with our astrologers.</p>
                </div>
            </div>

            <div class="usr-stats">
                <div class="usr-stat">
                    <span class="usr-stat-icon"><Icon icon="mdi:message-text-outline" width="24" height="24" /></span>
                    <div><strong>{{ list.length }}</strong><span>Total sessions</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-blue"><Icon icon="mdi:timer-outline" width="24" height="24" /></span>
                    <div><strong>{{ fmtDuration(totalSeconds) || '0s' }}</strong><span>Time in chat</span></div>
                </div>
                <div class="usr-stat">
                    <span class="usr-stat-icon is-green"><Icon icon="mdi:wallet-outline" width="24" height="24" /></span>
                    <div><strong>₹{{ totalSpent.toFixed(2) }}</strong><span>Total spent</span></div>
                </div>
            </div>

            <div class="usr-card usr-table">
                <VueGoodTable :columns="columns" :rows="sessions" :paginate="true" :search-options="{ enabled: true }"
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
                        <span v-else-if="props.column.field === 'duration'">
                            {{ fmtDuration(props.row.duration_seconds) }}
                        </span>
                        <span v-else-if="props.column.field === 'deduction'" class="usr-strong">
                            ₹{{ (+props.row.deduction || 0).toFixed(2) }}
                        </span>
                        <span v-else-if="props.column.field === 'ended_at'">
                            {{ dayjs(props.row.ended_at).format("D MMM, YYYY HH:mm:ss") }}
                        </span>
                        <span v-else-if="props.column.field === 'ended_by'">
                            <span class="usr-badge" :class="endedMeta(props.row.ended_by).cls">
                                {{ endedMeta(props.row.ended_by).label }}
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