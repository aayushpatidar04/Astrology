<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import AreaChart from '@/Components/Dashboard/AreaChart.vue';
import DonutChart from '@/Components/Dashboard/DonutChart.vue';
import BarList from '@/Components/Dashboard/BarList.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';

const props = defineProps({
    totals: { type: Object, default: () => ({}) },
    daily: { type: Array, default: () => [] }, // last 180 days: { date, signups, revenue, sessions, payouts }
    astrologerStatus: { type: Object, default: () => ({}) },
    topAstrologers: { type: Array, default: () => [] },
    gender: { type: Object, default: () => ({}) },
    languages: { type: Array, default: () => [] },
    horoscopeTypes: { type: Object, default: () => ({}) },
    recentUsers: { type: Array, default: () => [] },
    recentPayouts: { type: Array, default: () => [] },
    pendingAstrologers: { type: Array, default: () => [] },
})

const page = usePage()
const firstName = computed(() => (page.props.auth?.user?.name || 'Admin').split(' ')[0])

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening'
const today = dayjs().format('dddd, D MMMM YYYY')

const inr = (n) => '₹' + Math.round(+n || 0).toLocaleString('en-IN')
const num = (n) => (+n || 0).toLocaleString('en-IN')

// ---------- period handling (all client side, from the 180-day series) ----------
const ranges = [7, 30, 90]
const range = ref(30)

const current = computed(() => props.daily.slice(-range.value))
const previous = computed(() => props.daily.slice(-range.value * 2, -range.value))

const sum = (arr, key) => arr.reduce((a, d) => a + (+d[key] || 0), 0)

const pct = (cur, prev) => (prev > 0 ? Math.round(((cur - prev) / prev) * 100) : null)

const period = computed(() => {
    const keys = ['signups', 'revenue', 'sessions', 'payouts']
    const out = {}
    keys.forEach((k) => {
        const cur = sum(current.value, k)
        const prev = sum(previous.value, k)
        out[k] = { value: cur, change: previous.value.length === range.value ? pct(cur, prev) : null, spark: current.value.map((d) => +d[k] || 0) }
    })
    return out
})

// ---------- main chart ----------
const metric = ref('revenue')
const metrics = [
    { key: 'revenue', label: 'Revenue' },
    { key: 'signups', label: 'New users' },
    { key: 'sessions', label: 'Sessions' },
]

const labels = computed(() => current.value.map((d) => dayjs(d.date).format('D MMM')))

const series = computed(() => {
    const vals = (k) => current.value.map((d) => +d[k] || 0)
    if (metric.value === 'revenue') {
        return [
            { name: 'Recharges', color: '#FF7010', values: vals('revenue') },
            { name: 'Astrologer payouts', color: '#2563eb', values: vals('payouts') },
        ]
    }
    if (metric.value === 'signups') return [{ name: 'New users', color: '#2563eb', values: vals('signups') }]
    return [{ name: 'Sessions', color: '#9333ea', values: vals('sessions') }]
})

const chartTitle = computed(() => ({ revenue: 'Revenue vs payouts', signups: 'User signups', sessions: 'Chat & call sessions' }[metric.value]))

// ---------- other widgets ----------
const statusMeta = [
    { key: 'active', label: 'Active', color: '#3b82f6' },
    { key: 'verified', label: 'Verified', color: '#22c55e' },
    { key: 'pending_verification', label: 'Pending', color: '#f59e0b' },
    { key: 'rejected', label: 'Rejected', color: '#ef4444' },
]
const statusSegments = computed(() => statusMeta.map((s) => ({ label: s.label, color: s.color, value: +props.astrologerStatus[s.key] || 0 })))
const astrologerTotal = computed(() => statusSegments.value.reduce((a, s) => a + s.value, 0))
const pending = computed(() => +props.astrologerStatus.pending_verification || 0)

const genderSegments = computed(() => {
    const g = props.gender || {}
    const map = { male: ['Male', '#3b82f6'], female: ['Female', '#ec4899'], other: ['Other', '#9333ea'] }
    return Object.entries(g)
        .filter(([, v]) => +v > 0)
        .map(([k, v]) => ({ label: (map[k]?.[0]) || k.charAt(0).toUpperCase() + k.slice(1), color: map[k]?.[1] || '#9ca3af', value: +v }))
})
const genderTotal = computed(() => genderSegments.value.reduce((a, s) => a + s.value, 0))

const topAstrologerItems = computed(() =>
    props.topAstrologers.map((a) => ({ label: a.name, value: a.sessions, sub: a.paid ? inr(a.paid) + ' paid' : '' }))
)

const summary = computed(() => {
    const p = period.value
    return `${num(p.signups.value)} new users and ${inr(p.revenue.value)} in recharges over the last ${range.value} days.`
})

const content = computed(() => [
    { label: 'Blogs', value: props.totals.blogs ?? 0, icon: 'mdi:post-outline', route: 'blogs', tone: '#2563eb', bg: '#dbeafe' },
    { label: 'Active banners', value: `${props.totals.banners_active ?? 0}/${props.totals.banners ?? 0}`, icon: 'mdi:image-multiple-outline', route: 'admin.banners', tone: '#9333ea', bg: '#f3e8ff' },
    { label: 'Horoscopes', value: props.totals.horoscopes ?? 0, icon: 'mdi:star-four-points-outline', route: 'admin.horoscopes', tone: '#FF7010', bg: '#ffedd5' },
    { label: 'Packages', value: props.totals.packages ?? 0, icon: 'mdi:wallet-giftcard', route: 'admin.recharge-packages', tone: '#16a34a', bg: '#dcfce7' },
])
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
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
                <p class="dsh-hero-sub">{{ summary }}</p>
            </div>

            <div class="dsh-hero-actions">
                <Link :href="route('admin.astrologers')" class="dsh-hero-btn">
                    <Icon icon="mdi:account-check-outline" width="18" height="18" />
                    Verify astrologers
                    <b v-if="pending">{{ pending }}</b>
                </Link>
                <Link :href="route('blogs')" class="dsh-hero-btn">
                    <Icon icon="mdi:pencil-plus-outline" width="18" height="18" /> Write a blog
                </Link>
                <Link :href="route('admin.transactions.index')" class="dsh-hero-btn">
                    <Icon icon="mdi:swap-horizontal" width="18" height="18" /> Transactions
                </Link>
            </div>
        </section>

        <!-- Period selector -->
        <div class="adm-toolbar dsh-rise" style="--d: 0.08s">
            <div>
                <h3 class="dsh-section">Performance</h3>
                <p class="adm-sub">Compared with the previous {{ range }} days.</p>
            </div>
            <div class="adm-tabs" style="margin: 0">
                <button v-for="r in ranges" :key="r" class="adm-tab" :class="{ 'is-active': range === r }"
                    @click="range = r">
                    {{ r }} days
                </button>
            </div>
        </div>

        <!-- KPI row (period based) -->
        <div class="dsh-grid-4">
            <div class="dsh-rise" style="--d: 0.12s">
                <StatCard title="Recharge revenue" :value="period.revenue.value" prefix="₹" icon="mdi:cash-plus"
                    tone="orange" :change="period.revenue.change" :spark="period.revenue.spark"
                    :hint="`${inr(totals.recharge_total)} all time`" />
            </div>
            <div class="dsh-rise" style="--d: 0.17s">
                <StatCard title="New users" :value="period.signups.value" icon="mdi:account-plus-outline" tone="blue"
                    :change="period.signups.change" :spark="period.signups.spark" :hint="`${num(totals.users)} total users`" />
            </div>
            <div class="dsh-rise" style="--d: 0.22s">
                <StatCard title="Chat & call sessions" :value="period.sessions.value"
                    icon="mdi:chat-processing-outline" tone="purple" :change="period.sessions.change"
                    :spark="period.sessions.spark" :hint="`${num(totals.sessions)} all time`" />
            </div>
            <div class="dsh-rise" style="--d: 0.27s">
                <StatCard title="Astrologer payouts" :value="period.payouts.value" prefix="₹" icon="mdi:bank-transfer-out"
                    tone="green" :change="period.payouts.change" :spark="period.payouts.spark"
                    :hint="`${inr(totals.payouts_total)} all time`" :href="route('admin.transactions.index')" />
            </div>
        </div>

        <!-- Overview row (all time) -->
        <div class="dsh-grid-4" style="margin-top: 1.25rem">
            <div class="dsh-rise" style="--d: 0.3s">
                <StatCard title="Total users" :value="+totals.users || 0" icon="mdi:account-group-outline" tone="blue"
                    :href="route('admin.users')" />
            </div>
            <div class="dsh-rise" style="--d: 0.34s">
                <StatCard title="Active astrologers" :value="+astrologerStatus.active || 0"
                    icon="mdi:account-star-outline" tone="orange" :href="route('admin.astrologers')"
                    :hint="`${num(astrologerTotal)} registered · ${num(totals.busy_astrologers)} busy now`" />
            </div>
            <div class="dsh-rise" style="--d: 0.38s">
                <StatCard title="Awaiting verification" :value="pending" icon="mdi:clock-alert-outline" tone="amber"
                    :href="route('admin.astrologers')" hint="Astrologers needing review" />
            </div>
            <div class="dsh-rise" style="--d: 0.42s">
                <StatCard title="Wallet balance held" :value="+totals.wallet_balance || 0" prefix="₹"
                    icon="mdi:wallet-outline" tone="green" hint="Across customer and astrologer wallets" />
            </div>
        </div>

        <!-- Main chart + astrologer status -->
        <div class="dsh-grid-main">
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.46s">
                <div class="dsh-card-head">
                    <div>
                        <h3>{{ chartTitle }}</h3>
                        <p>Last {{ range }} days</p>
                    </div>
                    <div class="adm-tabs" style="margin: 0">
                        <button v-for="m in metrics" :key="m.key" class="adm-tab"
                            :class="{ 'is-active': metric === m.key }" @click="metric = m.key">
                            {{ m.label }}
                        </button>
                    </div>
                </div>
                <AreaChart :labels="labels" :series="series" :prefix="metric === 'revenue' ? '₹' : ''" />
            </section>

            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.5s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Astrologers by status</h3>
                        <p>Verification pipeline</p>
                    </div>
                </div>
                <DonutChart :segments="statusSegments" :center-value="astrologerTotal" center-label="astrologers" />
            </section>
        </div>

        <!-- Insights row -->
        <div class="dsh-grid-3">
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.54s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Top astrologers</h3>
                        <p>By chat &amp; call sessions</p>
                    </div>
                </div>
                <BarList :items="topAstrologerItems" empty="No sessions yet" />
            </section>

            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.58s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Users by gender</h3>
                        <p>From profile details</p>
                    </div>
                </div>
                <DonutChart v-if="genderSegments.length" :segments="genderSegments" :size="150" :thickness="18"
                    :center-value="genderTotal" center-label="profiles" />
                <div v-else class="adm-empty">No profile data yet</div>
            </section>

            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.62s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Preferred languages</h3>
                        <p>Most requested by users</p>
                    </div>
                </div>
                <BarList :items="languages" color="#2563eb" empty="No language data yet" />
            </section>
        </div>

        <!-- Needs attention + payouts -->
        <div class="dsh-grid-2">
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.66s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Needs your attention</h3>
                        <p>Astrologers waiting for verification</p>
                    </div>
                    <Link :href="route('admin.astrologers')" class="adm-link">View all</Link>
                </div>

                <ul v-if="pendingAstrologers.length" class="dsh-list">
                    <li v-for="a in pendingAstrologers" :key="a.id">
                        <span class="adm-avatar" style="width: 2.6rem; height: 2.6rem">{{ a.name?.charAt(0)?.toUpperCase() }}</span>
                        <div class="dsh-list-main">
                            <span class="adm-strong">{{ a.name }}</span>
                            <span class="adm-muted adm-lc">{{ a.email }}</span>
                        </div>
                        <span v-if="a.experience_years !== null && a.experience_years !== undefined" class="adm-chip">{{ a.experience_years }} yrs</span>
                        <Link :href="route('admin.astrologers')" class="adm-btn adm-btn-sm">Review</Link>
                    </li>
                </ul>
                <div v-else class="dsh-clear">
                    <Icon icon="mdi:check-decagram" width="40" height="40" />
                    <b>All caught up</b>
                    <span>No astrologers are waiting for verification.</span>
                </div>
            </section>

            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.7s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Recent payouts</h3>
                        <p>Latest astrologer transactions</p>
                    </div>
                    <Link :href="route('admin.transactions.index')" class="adm-link">View all</Link>
                </div>

                <ul v-if="recentPayouts.length" class="dsh-list">
                    <li v-for="(p, i) in recentPayouts" :key="i">
                        <span class="dsh-ico dsh-ico-green">
                            <Icon icon="mdi:bank-transfer-out" width="20" height="20" />
                        </span>
                        <div class="dsh-list-main">
                            <span class="adm-strong">{{ p.name }}</span>
                            <span class="adm-muted">{{ [p.mode, p.reference_no].filter(Boolean).join(' · ') }}</span>
                        </div>
                        <div class="dsh-list-end">
                            <b>{{ inr(p.amount) }}</b>
                            <span class="adm-muted">{{ dayjs(p.date).format('D MMM, YYYY') }}</span>
                        </div>
                    </li>
                </ul>
                <div v-else class="adm-empty">No payouts recorded yet</div>
            </section>
        </div>

        <!-- Recent signups + content -->
        <div class="dsh-grid-2">
            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.74s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Newest users</h3>
                        <p>Latest sign-ups</p>
                    </div>
                    <Link :href="route('admin.users')" class="adm-link">View all</Link>
                </div>

                <ul v-if="recentUsers.length" class="dsh-list">
                    <li v-for="u in recentUsers" :key="u.id">
                        <span class="adm-avatar" style="width: 2.6rem; height: 2.6rem">{{ u.name?.charAt(0)?.toUpperCase() }}</span>
                        <div class="dsh-list-main">
                            <span class="adm-strong">{{ u.name }}</span>
                            <span class="adm-muted adm-lc">{{ u.email || u.phone }}</span>
                        </div>
                        <span class="adm-muted" style="white-space: nowrap">{{ dayjs(u.created_at).format('D MMM') }}</span>
                    </li>
                </ul>
                <div v-else class="adm-empty">No users yet</div>
            </section>

            <section class="adm-card dsh-pad dsh-rise" style="--d: 0.78s">
                <div class="dsh-card-head">
                    <div>
                        <h3>Content &amp; catalogue</h3>
                        <p>Jump to what you manage</p>
                    </div>
                </div>

                <div class="dsh-tiles">
                    <Link v-for="c in content" :key="c.label" :href="route(c.route)" class="dsh-tile">
                        <span class="dsh-ico" :style="{ background: c.bg, color: c.tone }">
                            <Icon :icon="c.icon" width="22" height="22" />
                        </span>
                        <strong>{{ c.value }}</strong>
                        <span>{{ c.label }}</span>
                    </Link>
                </div>

                <div v-if="Object.keys(horoscopeTypes).length" class="dsh-chips">
                    <span class="adm-muted" style="display: inline">Horoscopes:</span>
                    <span v-for="(v, k) in horoscopeTypes" :key="k" class="adm-badge adm-badge-orange">
                        {{ k }} · {{ v }}
                    </span>
                </div>
            </section>
        </div>

    </AuthenticatedLayout>
</template>

<!-- Plain "dsh-" prefixed CSS, shared tokens come from the admin layout (adm-*) -->
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
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    backdrop-filter: blur(6px);
    transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
}

.dsh-hero-btn:hover {
    transform: translateY(-2px);
    background: #fff;
    color: #E85F00;
}

.dsh-hero-btn b {
    min-width: 1.3rem;
    padding: 0.05rem 0.4rem;
    border-radius: 9999px;
    background: #fff;
    font-size: 0.75rem;
    text-align: center;
    color: #E85F00;
}

.dsh-section {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
}

/* Grids */
.dsh-grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
    gap: 1.25rem;
}

.dsh-grid-main,
.dsh-grid-3,
.dsh-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin-top: 1.25rem;
}

@media (min-width: 768px) {
    .dsh-grid-3 {
        grid-template-columns: 1fr 1fr;
    }
}

@media (min-width: 1100px) {
    .dsh-grid-main {
        grid-template-columns: 1.9fr 1fr;
    }

    .dsh-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

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

/* Lists */
.dsh-list {
    display: flex;
    flex-direction: column;
    margin: 0;
    padding: 0;
    list-style: none;
}

.dsh-list li {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.7rem 0.5rem;
    margin: 0 -0.5rem;
    border-radius: 0.75rem;
    transition: background 0.2s ease;
}

.dsh-list li:hover {
    background: #fffaf5;
}

.dsh-list-main {
    min-width: 0;
    flex: 1;
}

.dsh-list-end {
    text-align: right;
}

.dsh-list-end b {
    display: block;
    color: #15803d;
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

.dsh-ico-green {
    background: #dcfce7;
    color: #16a34a;
}

.dsh-clear {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    padding: 1.75rem 1rem;
    text-align: center;
    color: #16a34a;
}

.dsh-clear b {
    color: #1f2937;
}

.dsh-clear span {
    font-size: 0.85rem;
    color: #9ca3af;
}

/* Content tiles */
.dsh-tiles {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
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
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
    color: #1f2937;
}

.dsh-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1rem;
}

.dsh-chips .adm-badge {
    text-transform: capitalize;
}

@media (prefers-reduced-motion: reduce) {
    .dsh-rise,
    .dsh-ring-a {
        animation: none !important;
    }
}
</style>