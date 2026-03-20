<script setup>
import Modal from "@/Components/Modal.vue";
import { inject, ref } from "vue";
import { router } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue";

const props = defineProps({
    banner: Object,
})

const selectedBanner = ref(null)
const showDeleteBanner = ref(false)
const alertRef = inject('alertRef')

function openDeleteBanner(banner) {
    selectedBanner.value = banner
    showDeleteBanner.value = true
}



const deleteBanner = () => {
    router.delete(route("admin.banners.destroy", {id: props.banner.id}), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteBanner.value = false
            alertRef.value.showAlert("Banner deleted successfully!", 'success')
        },
        onError: () => {
            alertRef.value.showAlert("Failed to delete banner.", 'error')
        }
    })
}

</script>

<template>

    <button @click="openDeleteBanner(banner)" class="text-red-500 text-2xl">
        <Icon icon="mdi:trash"></Icon>
    </button>

    <!-- Delete Banner Modal -->
    <Modal :show="showDeleteBanner" @close="showDeleteBanner = false">
        <div class="p-6 text-center">
            <h3 class="text-xl font-semibold mb-4">Delete Banner</h3>
            <p>Are you sure you want to delete <b>{{ selectedBanner?.title }}</b>?</p>
            <div class="flex justify-center gap-4 mt-6">
                <button @click="showDeleteBanner = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button @click="deleteBanner" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </div>
        </div>
    </Modal>
</template>
