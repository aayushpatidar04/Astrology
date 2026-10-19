<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    tag: { type: String, default: 'div' },
    // up | down | left | right | scale | fade
    direction: { type: String, default: 'up' },
    delay: { type: Number, default: 0 }, // ms
})

const el = ref(null)
const visible = ref(false)
let observer = null

onMounted(() => {
    if (!('IntersectionObserver' in window)) {
        visible.value = true
        return
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                visible.value = true
                observer.disconnect()
            }
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    )
    observer.observe(el.value)
})

onBeforeUnmount(() => observer?.disconnect())
</script>

<template>
    <component :is="tag" ref="el" class="reveal" :class="[`reveal-${direction}`, { 'is-visible': visible }]"
        :style="{ transitionDelay: `${delay}ms` }">
        <slot />
    </component>
</template>