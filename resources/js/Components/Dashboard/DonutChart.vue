<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

// segments: [{ label, value, color }]
const props = defineProps({
    segments: { type: Array, default: () => [] },
    centerValue: { type: [String, Number], default: '' },
    centerLabel: { type: String, default: '' },
    size: { type: Number, default: 176 },
    thickness: { type: Number, default: 22 },
})

const r = computed(() => (props.size - props.thickness) / 2)
const C = computed(() => 2 * Math.PI * r.value)
const total = computed(() => props.segments.reduce((a, s) => a + (+s.value || 0), 0))

const progress = ref(0)
let raf = null

onMounted(() => {
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        progress.value = 1
        return
    }
    const start = performance.now()
    const tick = (now) => {
        const p = Math.min((now - start) / 900, 1)
        progress.value = 1 - Math.pow(1 - p, 3)
        if (p < 1) raf = requestAnimationFrame(tick)
    }
    raf = requestAnimationFrame(tick)
})
onBeforeUnmount(() => cancelAnimationFrame(raf))

const active = ref(null)

const arcs = computed(() => {
    const gap = total.value > 0 && props.segments.filter((s) => s.value > 0).length > 1 ? 3 : 0
    let acc = 0
    return props.segments.map((s) => {
        const frac = total.value ? s.value / total.value : 0
        const full = frac * C.value
        const len = Math.max(0, full - gap)
        const out = { ...s, len, offset: -acc, pct: Math.round(frac * 100) }
        acc += full
        return out
    })
})
</script>

<template>
    <div class="dn">
        <div class="dn-chart" :style="{ width: size + 'px', height: size + 'px' }">
            <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`" style="transform: rotate(-90deg)">
                <circle :cx="size / 2" :cy="size / 2" :r="r" fill="none" stroke="#fff4ea" :stroke-width="thickness" />
                <circle v-for="a in arcs" :key="a.label" :cx="size / 2" :cy="size / 2" :r="r" fill="none"
                    :stroke="a.color" :stroke-width="active === a.label ? thickness + 4 : thickness"
                    stroke-linecap="butt" :stroke-dasharray="`${a.len * progress} ${C}`" :stroke-dashoffset="a.offset"
                    :style="{ opacity: active && active !== a.label ? 0.35 : 1 }" class="dn-arc"
                    @mouseenter="active = a.label" @mouseleave="active = null" />
            </svg>
            <div class="dn-center">
                <strong>{{ centerValue }}</strong>
                <span>{{ centerLabel }}</span>
            </div>
        </div>

        <ul class="dn-legend">
            <li v-for="a in arcs" :key="a.label" :class="{ 'is-dim': active && active !== a.label }"
                @mouseenter="active = a.label" @mouseleave="active = null">
                <i :style="{ background: a.color }"></i>
                <span>{{ a.label }}</span>
                <b>{{ a.value }}</b>
                <em>{{ a.pct }}%</em>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.dn {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1.25rem 1.75rem;
}

.dn-chart {
    position: relative;
    flex-shrink: 0;
}

.dn-arc {
    cursor: pointer;
    transition: opacity 0.25s ease, stroke-width 0.25s ease;
}

.dn-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.dn-center strong {
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    color: #1f2937;
}

.dn-center span {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #9ca3af;
}

.dn-legend {
    display: flex;
    min-width: 11rem;
    flex: 1;
    flex-direction: column;
    gap: 0.45rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.dn-legend li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #4b5563;
    cursor: default;
    transition: opacity 0.25s ease;
}

.dn-legend li.is-dim {
    opacity: 0.4;
}

.dn-legend i {
    width: 0.65rem;
    height: 0.65rem;
    flex-shrink: 0;
    border-radius: 9999px;
}

.dn-legend span {
    flex: 1;
}

.dn-legend b {
    color: #1f2937;
}

.dn-legend em {
    width: 2.6rem;
    font-style: normal;
    font-size: 0.75rem;
    text-align: right;
    color: #9ca3af;
}
</style>