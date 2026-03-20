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
    <div class="flex items-center space-x-2">
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" :checked="banner.active" @change="toggleActive(banner.id, $event.target.checked)"
                class="sr-only peer" />
            <div class="w-12 h-6 bg-gray-300 rounded-full transition-colors peer-checked:bg-green-500">
            </div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow
                        transition-all duration-200 ease-in-out peer-checked:left-7"></div>
        </label>
    </div>
</template>