<script setup>
import { inject } from 'vue';

const alertRef = inject('alertRef')

const props = defineProps({
    banner: Object,
})

function toggleActive(id, value) {
    axios.patch(route('admin.banners.toggle', id), { active: value })
        .then(response => {
            alertRef.value?.showAlert(response.data.message, 'success');
        })
        .catch(() => {
            alertRef.value?.showAlert('Failed to update status.', 'error');
        });
}
</script>

<template>
    <label class="adm-switch" title="Show / hide banner">
        <input type="checkbox" :checked="banner.active" @change="toggleActive(banner.id, $event.target.checked)" />
        <span class="adm-switch-track"></span>
    </label>
</template>