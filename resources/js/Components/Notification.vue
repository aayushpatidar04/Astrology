<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    flash: {
        type: Object,
        default: () => ({})
    }
})

const show = ref(false)
const message = ref('')
const type = ref('success')

watch(
    () => props.flash,
    (newFlash) => {
        if (newFlash.success) {
            message.value = newFlash.success
            type.value = 'success'
            show.value = true
            setTimeout(() => (show.value = false), 5000)
        } else if (newFlash.error) {
            message.value = newFlash.error
            type.value = 'error'
            show.value = true
            setTimeout(() => (show.value = false), 5000)
        }
    },
    { immediate: true, deep: true }
)
</script>

<template>
    <transition name="fade">
        <div v-if="show" class="fixed bottom-4 right-4 w-80 p-4 rounded-lg shadow-lg"
            :class="type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
            {{ message }}
        </div>
    </transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
