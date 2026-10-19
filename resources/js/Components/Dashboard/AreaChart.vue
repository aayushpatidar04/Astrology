<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

// labels: ['1 Oct', ...]   series: [{ name, color, values: [..] }]
const props = defineProps({
    labels: { type: Array, default: () => [] },
    series: { type: Array, default: () => [] },
    prefix: { type: String, default: '' },
    height: { type: Number, default: 280 },
})

const W = 800
const padL = 46
const padR = 14
const padT = 14
const padB = 30
const innerW = W - padL - padR
const innerH = computed(() => props.height - padT - padB)
const n = computed(() => props.labels.length)

function niceMax(m) {
    const pow = Math.pow(10, Math.floor(Math.log10(m)))
    const r = m / pow
    return (r <= 1 ? 1 : r <= 2 ? 2 : r <= 5 ? 5 : 10) * pow
}

const rawMax = computed(() => Math.max(0, ...props.series.flatMap((s) => s.values)))
const isEmpty = computed(() => rawMax.value === 0)
const max = computed(() => niceMax(Math.max(rawMax.value, 1)))

const xAt = (i) => padL + (n.value <= 1 ? 0 : (i / (n.value - 1)) * innerW)
const yAt = (v) => padT + innerH.value - (v / max.value) * innerH.value

function compact(v) {
    if (v >= 100000) return (v / 100000).toFixed(v % 100000 ? 1 : 0).replace(/\.0$/, '') + 'L'
    if (v >= 1000) return (v / 1000).toFixed(v % 1000 ? 1 : 0).replace(/\.0$/, '') + 'k'
    return String(Math.round(v))
}

const fmt = (v) => props.prefix + Math.round(v).toLocaleString('en-IN')

const ticks = computed(() => [0, 1, 2, 3, 4].map((k) => ({ y: yAt((max.value / 4) * k), label: compact((max.value / 4) * k) })))

const xLabels = computed(() => {
    const step = Math.max(1, Math.ceil(n.value / 6))
    return props.labels.map((l, i) => ({ l, i })).filter((o) => o.i % step === 0)
})

function smooth(points) {
    if (!points.length) return ''
    let d = `M${points[0][0].toFixed(1)},${points[0][1].toFixed(1)}`
    for (let i = 1; i < points.length; i++) {
        const [x0, y0] = points[i - 1]
        const [x1, y1] = points[i]
        const mx = ((x0 + x1) / 2).toFixed(1)
        d += ` C${mx},${y0.toFixed(1)} ${mx},${y1.toFixed(1)} ${x1.toFixed(1)},${y1.toFixed(1)}`
    }
    return d
}

const paths = computed(() =>
    props.series.map((s) => {
        const pts = s.values.map((v, i) => [xAt(i), yAt(v)])
        const line = smooth(pts)
        const base = padT + innerH.value
        const area = pts.length ? `${line} L${pts[pts.length - 1][0].toFixed(1)},${base} L${pts[0][0].toFixed(1)},${base} Z` : ''
        return { ...s, line, area }
    })
)

// ---- reveal animation ----
const reveal = ref(0)
let raf = null
const uid = Math.random().toString(36).slice(2, 8)

function play() {
    cancelAnimationFrame(raf)
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        reveal.value = 1
        return
    }
    reveal.value = 0
    const start = performance.now()
    const tick = (now) => {
        const p = Math.min((now - start) / 800, 1)
        reveal.value = 1 - Math.pow(1 - p, 3)
        if (p < 1) raf = requestAnimationFrame(tick)
    }
    raf = requestAnimationFrame(tick)
}

onMounted(play)
watch(() => [props.labels, props.series], play)
onBeforeUnmount(() => cancelAnimationFrame(raf))

// ---- hover tooltip ----
const wrap = ref(null)
const hover = ref(null)

function onMove(e) {
    if (!wrap.value || n.value < 1) return
    const rect = wrap.value.getBoundingClientRect()
    const clientX = e.touches ? e.touches[0].clientX : e.clientX
    const x = ((clientX - rect.left) / rect.width) * W
    const idx = Math.round(((x - padL) / innerW) * (n.value - 1))
    hover.value = Math.max(0, Math.min(n.value - 1, idx))
}

const tip = computed(() => {
    if (hover.value === null) return null
    const i = hover.value
    const topY = Math.min(...props.series.map((s) => yAt(s.values[i] ?? 0)))
    return {
        left: Math.max(14, Math.min(86, (xAt(i) / W) * 100)),
        top: (topY / props.height) * 100,
        label: props.labels[i],
        rows: props.series.map((s) => ({ name: s.name, color: s.color, value: fmt(s.values[i] ?? 0) })),
    }
})
</script>

<template>
    <div>
        <div v-if="series.length > 1" class="ac-legend">
            <span v-for="s in series" :key="s.name"><i :style="{ background: s.color }"></i>{{ s.name }}</span>
        </div>

        <div ref="wrap" class="ac-wrap" @mousemove="onMove" @touchmove.passive="onMove" @mouseleave="hover = null"
            @touchend="hover = null">
            <svg :viewBox="`0 0 ${W} ${height}`" class="ac-svg" role="img">
                <defs>
                    <clipPath :id="`clip-${uid}`">
                        <rect x="0" y="0" :width="W * reveal" :height="height" />
                    </clipPath>
                    <linearGradient v-for="(s, i) in paths" :key="i" :id="`fill-${uid}-${i}`" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" :stop-color="s.color" stop-opacity="0.28" />
                        <stop offset="100%" :stop-color="s.color" stop-opacity="0" />
                    </linearGradient>
                </defs>

                <!-- grid -->
                <g>
                    <g v-for="t in ticks" :key="t.y">
                        <line :x1="padL" :x2="W - padR" :y1="t.y" :y2="t.y" stroke="#ffe8d4" stroke-dasharray="4 5" />
                        <text :x="padL - 8" :y="t.y + 4" text-anchor="end" class="ac-tick">{{ prefix }}{{ t.label }}</text>
                    </g>
                    <text v-for="o in xLabels" :key="o.i" :x="xAt(o.i)" :y="height - 8"
                        :text-anchor="o.i === 0 ? 'start' : 'middle'" class="ac-tick">{{ o.l }}</text>
                </g>

                <!-- series -->
                <g :clip-path="`url(#clip-${uid})`">
                    <template v-for="(s, i) in paths" :key="s.name">
                        <path :d="s.area" :fill="`url(#fill-${uid}-${i})`" />
                        <path :d="s.line" fill="none" :stroke="s.color" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </template>
                </g>

                <!-- hover marker -->
                <g v-if="hover !== null">
                    <line :x1="xAt(hover)" :x2="xAt(hover)" :y1="padT" :y2="padT + innerH" stroke="#fdba74"
                        stroke-width="1.5" />
                    <circle v-for="s in series" :key="s.name" :cx="xAt(hover)" :cy="yAt(s.values[hover] ?? 0)" r="5"
                        fill="#fff" :stroke="s.color" stroke-width="2.5" />
                </g>
            </svg>

            <div v-if="isEmpty" class="ac-empty">No activity in this period</div>

            <div v-if="tip" class="ac-tip" :style="{ left: tip.left + '%', top: tip.top + '%' }">
                <b>{{ tip.label }}</b>
                <span v-for="r in tip.rows" :key="r.name"><i :style="{ background: r.color }"></i>{{ r.name }}: <strong>{{ r.value }}</strong></span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ac-wrap {
    position: relative;
    width: 100%;
    cursor: crosshair;
}

.ac-svg {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
}

.ac-tick {
    font-size: 11px;
    fill: #9ca3af;
}

.ac-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4b5563;
}

.ac-legend i,
.ac-tip i {
    display: inline-block;
    width: 0.6rem;
    height: 0.6rem;
    margin-right: 0.4rem;
    border-radius: 9999px;
}

.ac-tip {
    position: absolute;
    z-index: 5;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 8rem;
    padding: 0.5rem 0.7rem;
    border-radius: 0.65rem;
    background: #1f2937;
    font-size: 0.78rem;
    color: #e5e7eb;
    pointer-events: none;
    transform: translate(-50%, -115%);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
}

.ac-tip b {
    margin-bottom: 0.15rem;
    color: #fff;
}

.ac-tip strong {
    color: #fff;
}

.ac-empty {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: #9ca3af;
    pointer-events: none;
}
</style>