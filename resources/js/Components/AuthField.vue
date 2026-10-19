<script>
// kept as a normal block so it works on every Vue 3 version
export default { inheritAttrs: false }
</script>

<script setup>
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    id: String,
    label: String,
    icon: String,
    prefix: String, // purely visual, e.g. "+91"
    error: String,
    code: Boolean, // spaced-out centered style for OTP codes
    type: { type: String, default: 'text' },
})

defineEmits(['update:modelValue'])

const show = ref(false)
const isPassword = computed(() => props.type === 'password')
const inputType = computed(() => (isPassword.value && show.value ? 'text' : props.type))
</script>

<template>
    <div>
        <label :for="id" class="af-label">{{ label }}</label>

        <div class="af-wrap">
            <Icon v-if="icon" :icon="icon" width="20" height="20" class="af-icon" />

            <span v-if="prefix" class="af-prefix">{{ prefix }}</span>

            <input :id="id" v-bind="$attrs" :type="inputType" :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)" class="af-input" :class="{
                    'has-icon': icon && !prefix && !code,
                    'has-prefix': prefix,
                    'has-toggle': isPassword,
                    'is-code': code,
                    'is-error': error,
                }" />

            <button v-if="isPassword" type="button" class="af-toggle" @click="show = !show"
                :aria-label="show ? 'Hide password' : 'Show password'">
                <Icon :icon="show ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" height="20" />
            </button>
        </div>

        <Transition name="err">
            <p v-if="error" class="af-error">
                <Icon icon="mdi:alert-circle-outline" width="16" height="16" />
                {{ error }}
            </p>
        </Transition>
    </div>
</template>

<style scoped>
.af-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.af-wrap {
    position: relative;
}

.af-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
    transition: color 0.2s ease;
}

.af-wrap:focus-within .af-icon {
    color: #FF7010;
}

.af-prefix {
    position: absolute;
    left: 2.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    pointer-events: none;
}

.af-input {
    display: block;
    box-sizing: border-box;
    width: 100%;
    padding: 0.625rem 1rem;
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.95);
    font-size: 1rem;
    line-height: 1.5rem;
    color: #1f2937;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
}

.af-input::placeholder {
    color: #9ca3af;
    letter-spacing: normal;
    font-weight: 400;
}

.af-input.has-icon {
    padding-left: 2.5rem;
}

.af-input.has-prefix {
    padding-left: 4.5rem;
}

.af-input.has-toggle {
    padding-right: 2.75rem;
}

.af-input.is-code {
    padding-left: 2.5rem;
    padding-right: 2.5rem;
    text-align: center;
    font-weight: 600;
    letter-spacing: 0.35em;
}

.af-input:hover {
    border-color: #fb923c;
}

.af-input:focus {
    outline: none;
    border-color: #FF7010;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.25);
}

.af-input.is-error {
    border-color: #f87171;
}

.af-input.is-error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}

.af-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    padding: 0;
    border: 0;
    background: none;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.2s ease;
}

.af-toggle:hover {
    color: #FF7010;
}

.af-error {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin: 0.375rem 0 0;
    font-size: 0.875rem;
    color: #dc2626;
}

.err-enter-active,
.err-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.err-enter-from,
.err-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>