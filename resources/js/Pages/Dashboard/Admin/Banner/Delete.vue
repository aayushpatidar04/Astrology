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
    router.delete(route("admin.banners.destroy", { id: props.banner.id }), {
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

    <button @click="openDeleteBanner(banner)" class="adm-icon-btn adm-icon-del" title="Delete">
        <Icon icon="mdi:trash" />
    </button>

    <!-- Delete Banner Modal -->
    <Modal :show="showDeleteBanner" @close="showDeleteBanner = false">
        <div class="adm-confirm">
            <div class="adm-confirm-icon">
                <Icon icon="mdi:trash-can-outline" />
            </div>
            <h3>Delete Banner</h3>
            <p>Are you sure you want to delete <b>{{ selectedBanner?.title }}</b>?</p>
            <div class="adm-confirm-actions">
                <button @click="showDeleteBanner = false" class="adm-btn adm-btn-ghost">Cancel</button>
                <button @click="deleteBanner" class="adm-btn adm-btn-danger">Delete</button>
            </div>
        </div>
    </Modal>
</template>