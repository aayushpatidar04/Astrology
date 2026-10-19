<script setup>
import { ref, computed, onMounted } from 'vue'

// items: [{ label, value, sub? }]
const props = defineProps({
    items: { type: Array, default: () => [] },
    color: { type: String, default: '#FF7010' },
    prefix: { type: String, default: '' },
    empty: { type: String, default: 'No data yet' },
})

const ready = ref(false)
onMounted(() => requestAnimationFrame(() => requestAnimationFrame(() => (ready.value = true))))

const max = computed(() => Math.max(1, ...props.items.map((i) => +i.value || 0)))
const fmt = (v) => props.prefix + Math.round(v).toLocaleString('en-IN')
</script>

<template>
    <div v-if="items.length" class="bl">
        <div v-for="(it, i) in items" :key="it.label" class="bl-row">
            <div class="bl-head">
                <span class="bl-rank">{{ i + 1 }}</span>
                <span class="bl-label">{{ it.label }}</span>
                <span v-if="it.sub" class="bl-sub">{{ it.sub }}</span>
                <b class="bl-value">{{ fmt(it.value) }}</b>
            </div>
            <div class="bl-track">
                <div class="bl-fill" :style="{
                    width: ready ? (it.value / max) * 100 + '%' : '0%',
                    background: `linear-gradient(90deg, ${color}, ${color}aa)`,
                    transitionDelay: i * 80 + 'ms',
                }"></div>
            </div>
        </div>
    </div>
    <div v-else class="bl-empty">{{ empty }}</div>
</template>

<style scoped>
.bl {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.bl-head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
}

.bl-rank {
    display: flex;
    width: 1.4rem;
    height: 1.4rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.45rem;
    background: #ffedd5;
    font-size: 0.7rem;
    font-weight: 700;
    color: #c2410c;
}

.bl-label {
    overflow: hidden;
    flex: 1;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    color: #374151;
}

.bl-sub {
    font-size: 0.75rem;
    color: #9ca3af;
}

.bl-value {
    color: #1f2937;
}

.bl-track {
    height: 0.5rem;
    overflow: hidden;
    border-radius: 9999px;
    background: #fff4ea;
}

.bl-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 1s cubic-bezier(0.22, 1, 0.36, 1);
}

.bl-empty {
    padding: 1.5rem 0;
    text-align: center;
    font-size: 0.85rem;
    color: #9ca3af;
}
</style>