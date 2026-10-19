<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    title: String,
    value: { type: Number, default: 0 },
    prefix: { type: String, default: '' },
    suffix: { type: String, default: '' },
    change: { type: Number, default: null }, // % vs previous period (null = no comparison)
    invert: Boolean, // true when a decrease is the good direction
    icon: String,
    tone: { type: String, default: 'orange' }, // orange | blue | green | purple | amber
    hint: String,
    spark: { type: Array, default: () => [] },
    href: String,
})

// ---- count-up ----
const shown = ref(0)
let raf = null

function animateTo(to) {
    cancelAnimationFrame(raf)
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        shown.value = to
        return
    }
    const begin = shown.value
    const start = performance.now()
    const duration = 900
    const tick = (now) => {
        const p = Math.min((now - start) / duration, 1)
        const eased = 1 - Math.pow(1 - p, 3)
        shown.value = begin + (to - begin) * eased
        if (p < 1) raf = requestAnimationFrame(tick)
    }
    raf = requestAnimationFrame(tick)
}

onMounted(() => animateTo(props.value))
watch(() => props.value, (v) => animateTo(v))
onBeforeUnmount(() => cancelAnimationFrame(raf))

const display = computed(() => Math.round(shown.value).toLocaleString('en-IN'))

// ---- sparkline ----
const uid = Math.random().toString(36).slice(2, 8)
const sparkPath = computed(() => {
    const v = props.spark
    if (v.length < 2) return null
    const max = Math.max(...v)
    const min = Math.min(...v)
    const range = max - min || 1
    const pts = v.map((n, i) => [(i / (v.length - 1)) * 100, 26 - ((n - min) / range) * 22])
    const line = 'M' + pts.map((p) => `${p[0].toFixed(1)},${p[1].toFixed(1)}`).join(' L')
    return { line, area: `${line} L100,30 L0,30 Z` }
})

const good = computed(() => (props.change === null ? null : props.invert ? props.change <= 0 : props.change >= 0))
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href" class="stat" :class="[`tone-${tone}`, { 'is-link': href }]">
        <div class="stat-top">
            <span class="stat-icon">
                <Icon :icon="icon" width="22" height="22" />
            </span>
            <span v-if="change !== null" class="stat-trend" :class="good ? 'is-good' : 'is-bad'">
                <Icon :icon="change >= 0 ? 'mdi:arrow-top-right' : 'mdi:arrow-bottom-right'" width="14" height="14" />
                {{ Math.abs(change) }}%
            </span>
            <Icon v-else-if="href" icon="mdi:arrow-top-right" width="18" height="18" class="stat-go" />
        </div>

        <div class="stat-value">{{ prefix }}{{ display }}{{ suffix }}</div>
        <div class="stat-title">{{ title }}</div>
        <div v-if="hint" class="stat-hint">{{ hint }}</div>

        <svg v-if="sparkPath" class="stat-spark" viewBox="0 0 100 30" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient :id="`sg-${uid}`" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--c)" stop-opacity="0.25" />
                    <stop offset="100%" stop-color="var(--c)" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path :d="sparkPath.area" :fill="`url(#sg-${uid})`" />
            <path :d="sparkPath.line" fill="none" stroke="var(--c)" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round" vector-effect="non-scaling-stroke" />
        </svg>
    </component>
</template>

<style scoped>
.stat {
    position: relative;
    display: block;
    overflow: hidden;
    padding: 1.15rem 1.25rem 1rem;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    color: inherit;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 12px 32px -14px rgba(124, 45, 18, 0.14);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 22px 40px -14px rgba(124, 45, 18, 0.25);
}

.tone-orange { --c: #FF7010; --bg: #ffedd5; }
.tone-blue { --c: #2563eb; --bg: #dbeafe; }
.tone-green { --c: #16a34a; --bg: #dcfce7; }
.tone-purple { --c: #9333ea; --bg: #f3e8ff; }
.tone-amber { --c: #d97706; --bg: #fef3c7; }

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-icon {
    display: flex;
    width: 2.75rem;
    height: 2.75rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.85rem;
    background: var(--bg);
    color: var(--c);
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.stat:hover .stat-icon {
    transform: rotate(-8deg) scale(1.1);
}

.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.stat-trend.is-good { background: #dcfce7; color: #15803d; }
.stat-trend.is-bad { background: #fee2e2; color: #b91c1c; }

.stat-go {
    color: #9ca3af;
    transition: transform 0.25s ease, color 0.25s ease;
}

.stat:hover .stat-go {
    transform: translate(2px, -2px);
    color: var(--c);
}

.stat-value {
    margin-top: 0.9rem;
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.1;
    color: #1f2937;
    font-variant-numeric: tabular-nums;
}

.stat-title {
    margin-top: 0.2rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
}

.stat-hint {
    margin-top: 0.2rem;
    font-size: 0.75rem;
    color: #9ca3af;
}

.stat-spark {
    display: block;
    width: 100%;
    height: 2rem;
    margin-top: 0.85rem;
}
</style>