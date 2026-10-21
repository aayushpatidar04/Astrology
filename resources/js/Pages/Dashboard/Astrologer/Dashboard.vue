<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import StatCard from '@/Components/Dashboard/StatCard.vue'
import { ref, computed, inject, onMounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import dayjs from 'dayjs'
import * as PusherPushNotifications from '@pusher/push-notifications-web'

const props = defineProps({
    user: Object,
    // Optional – pass these from the controller when you want the KPI row to appear:
    // { chats_total, calls_total, earnings_total }
    stats: { type: Object, default: () => ({}) },
})

const page = usePage()
const firstName = computed(() => (page.props.auth?.user?.name || 'Astrologer').split(' ')[0])

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening'
const today = dayjs().format('dddd, D MMMM YYYY')

// shared with the layout so the top-bar switch and this page never disagree
const status = inject('astroStatus', null)
const online = computed(() => !!status?.online.value)
const toggling = computed(() => !!status?.toggling.value)

const inr = (n) => '₹' + Math.round(+n || 0).toLocaleString('en-IN')
const hasStats = computed(() => Object.keys(props.stats || {}).length > 0)

const quick = [
    { label: 'Chats', sub: 'Reply to users', icon: 'mdi:chat-processing-outline', href: '/astrologer/chats', tone: '#2563eb', bg: '#dbeafe' },
    { label: 'Calls', sub: 'Call history', icon: 'mdi:phone-outline', href: '/astrologer/calls', tone: '#9333ea', bg: '#f3e8ff' },
    { label: 'Profile', sub: 'Update your details', icon: 'mdi:account-circle-outline', href: '/astrologer/profile', tone: '#FF7010', bg: '#ffedd5' },
]

// ---------- Push notifications (unchanged) ----------
const beamsClient = new PusherPushNotifications.Client({
    instanceId: import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID,
})

const beamsTokenProvider = new PusherPushNotifications.TokenProvider({
    url: '/beams-auth',
})

onMounted(async () => {
    if (props.user?.id) {
        try {
            await beamsClient.clearAllState()
            await beamsClient.start()
            await beamsClient.setUserId(String(props.user.id), beamsTokenProvider)
        } catch (err) {
            console.error('Beams setup failed', err)
        }
    }
})
</script>

<template>

    <Head title="Dashboard" />

    <AstrologerLayout>
        <template #header>
            <h2 class="adm-title">Dashboard</h2>
        </template>

        <!-- Hero -->
        <section class="dsh-hero dsh-rise">
            <span class="dsh-ring dsh-ring-a"></span>
            <span class="dsh-ring dsh-ring-b"></span>

            <div class="dsh-hero-text">
                <p class="dsh-hero-date">{{ today }}</p>
                <h1>{{ greeting }}, {{ firstName }} 👋</h1>
                <p class="dsh-hero-sub">
                    {{ online
                        ? 'You are online — users can start chats and calls with you.'
                        : 'You are offline. Go online to start receiving chats and calls.' }}
                </p>
            </div>

            <div class="dsh-hero-actions">
                <button type="button" class="dsh-hero-btn" :disabled="toggling" @click="status?.toggleStatus()">
                    <Icon :icon="online ? 'mdi:power' : 'mdi:flash-outline'" width="18" height="18" />
                    {{ online ? 'Go offline' : 'Go online' }}
                </button>
                <Link href="/astrologer/chats" class="dsh-hero-btn">
                    <Icon icon="mdi:chat-processing-outline" width="18" height="18" /> Open chats
                </Link>
                <Link href="/astrologer/profile" class="dsh-hero-btn">
                    <Icon icon="mdi:account-edit-outline" width="18" height="18" /> Edit profile
                </Link>
            </div>
        </section>

        <!-- KPI row (only when the controller sends stats) -->
        <div v-if="hasStats" class="dsh-grid-4 dsh-rise" style="--d: 0.1s">
            <StatCard title="Chats" :value="+stats.chats_total || 0" icon="mdi:chat-processing-outline" tone="blue"
                href="/astrologer/chats" />
            <StatCard title="Calls" :value="+stats.calls_total || 0" icon="mdi:phone-outline" tone="purple"
                href="/astrologer/calls" />
            <StatCard title="Total earnings" :value="+stats.earnings_total || 0" prefix="₹" icon="mdi:cash-multiple"
                tone="green" :hint="inr(stats.earnings_total) + ' all time'" />
        </div>

        <div class="dsh-grid-4 dsh-rise" style="--d: 0.12s">
            <StatCard title="Wallet balance" :value="+user.wallet?.balance || 0" prefix="₹"
                icon="mdi:wallet-outline" tone="green" />
        </div>

        <div class="dsh-grid-2">
            <!-- Availability -->
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.16s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Availability</h3>
                        <p>Control whether users can reach you</p>
                    </div>
                    <span class="adm-badge" :class="online ? 'adm-badge-green' : 'adm-badge-gray'">
                        <span class="adm-dot"></span>{{ online ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <div class="dsh-avail">
                    <label class="adm-switch">
                        <input type="checkbox" :checked="online" :disabled="toggling" @change="status?.toggleStatus()" />
                        <span class="adm-switch-track"></span>
                    </label>
                    <p>
                        {{ online
                            ? 'Your profile is visible and you can receive new chat and call requests.'
                            : 'Your profile is hidden from new requests until you switch back online.' }}
                    </p>
                </div>
            </section>

            <!-- Quick access -->
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.22s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Quick access</h3>
                        <p>Jump to what you use most</p>
                    </div>
                </div>

                <div class="dsh-tiles dsh-tiles-3">
                    <Link v-for="q in quick" :key="q.label" :href="q.href" class="dsh-tile">
                        <span class="dsh-ico" :style="{ background: q.bg, color: q.tone }">
                            <Icon :icon="q.icon" width="22" height="22" />
                        </span>
                        <strong>{{ q.label }}</strong>
                        <span>{{ q.sub }}</span>
                    </Link>
                </div>
            </section>
        </div>
    </AstrologerLayout>
</template>

<style>
.dsh-rise {
    animation: adm-rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: var(--d, 0s);
}

/* Hero */
.dsh-hero {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    overflow: hidden;
    margin-bottom: 1.75rem;
    padding: 1.75rem 2rem;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, #FF7010 0%, #FF8A3D 55%, #FFB066 100%);
    color: #fff;
    box-shadow: 0 24px 48px -20px rgba(255, 112, 16, 0.6);
}

.dsh-ring {
    position: absolute;
    border-radius: 9999px;
    pointer-events: none;
}

.dsh-ring-a {
    top: -6rem;
    right: -4rem;
    width: 18rem;
    height: 18rem;
    border: 2px dashed rgba(255, 255, 255, 0.35);
    animation: adm-spin 60s linear infinite;
}

.dsh-ring-b {
    bottom: -7rem;
    left: 28%;
    width: 15rem;
    height: 15rem;
    background: rgba(255, 255, 255, 0.12);
}

.dsh-hero-text {
    position: relative;
    max-width: 34rem;
}

.dsh-hero-date {
    margin: 0 0 0.35rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    opacity: 0.85;
}

.dsh-hero h1 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.dsh-hero-sub {
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
    opacity: 0.95;
}

.dsh-hero-actions {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.dsh-hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.18);
    font-family: inherit;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
}

.dsh-hero-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    background: #fff;
    color: #E85F00;
}

.dsh-hero-btn:disabled {
    opacity: 0.6;
    cursor: wait;
}

/* Grids */
.dsh-grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
    gap: 1.25rem;
}

.dsh-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin-top: 1.25rem;
}

@media (min-width: 1100px) {
    .dsh-grid-2 {
        grid-template-columns: 1fr 1fr;
    }
}

/* Cards */
.dsh-pad {
    padding: 1.25rem 1.4rem 1.4rem;
}

.dsh-card-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.1rem;
}

.dsh-card-head h3 {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 700;
    color: #1f2937;
}

.dsh-card-head p {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: #9ca3af;
}

.dsh-avail {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.dsh-avail p {
    margin: 0;
    font-size: 0.9rem;
    color: #4b5563;
}

.dsh-ico {
    display: flex;
    width: 2.6rem;
    height: 2.6rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.8rem;
}

/* Tiles */
.dsh-tiles {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.dsh-tiles-3 {
    grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr));
}

.dsh-tile {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 1rem;
    border: 1px solid #ffe8d4;
    border-radius: 0.9rem;
    background: #fffaf5;
    font-size: 0.8rem;
    color: #6b7280;
    text-decoration: none;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.dsh-tile:hover {
    transform: translateY(-3px);
    border-color: #fdba74;
    box-shadow: 0 14px 26px -14px rgba(124, 45, 18, 0.35);
}

.dsh-tile .dsh-ico {
    margin-bottom: 0.5rem;
}

.dsh-tile strong {
    font-size: 1.1rem;
    font-weight: 800;
    line-height: 1.1;
    color: #1f2937;
}

@media (prefers-reduced-motion: reduce) {
    .dsh-rise,
    .dsh-ring-a {
        animation: none !important;
    }
}
</style>