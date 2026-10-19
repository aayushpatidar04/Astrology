<script setup>
// Label + control + animated error, used by every admin form.
// Usage: <AdminField label="Title" :error="form.errors.title"><input class="adm-input" v-model="form.title" /></AdminField>
defineProps({
    label: String,
    error: String,
    hint: String,
    forId: String,
});
</script>

<template>
    <div class="adm-field">
        <label v-if="label" :for="forId" class="adm-label">{{ label }}</label>
        <slot />
        <p v-if="hint && !error" class="adm-hint">{{ hint }}</p>
        <Transition name="adm-err">
            <p v-if="error" class="adm-error">{{ error }}</p>
        </Transition>
    </div>
</template>
