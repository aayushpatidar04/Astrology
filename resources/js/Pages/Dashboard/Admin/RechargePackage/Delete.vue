<script setup>
import Modal from "@/Components/Modal.vue";
import { inject, ref } from "vue";
import { router } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue";

const props = defineProps({
    recharge: Object,
})

const selectedPackage = ref(null)
const showDeletePackage = ref(false)
const alertRef = inject('alertRef')

function openDeletePackage(recharge) {
    selectedPackage.value = recharge
    showDeletePackage.value = true
}

const deletePackage = () => {
    router.delete(route("admin.recharge-packages.destroy", { id: props.recharge.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showDeletePackage.value = false
            alertRef.value.showAlert("Package deleted successfully!", 'success')
        },
        onError: () => {
            alertRef.value.showAlert("Failed to delete recharge.", 'error')
        }
    })
}
</script>

<template>

    <button @click="openDeletePackage(recharge)" class="adm-icon-btn adm-icon-del" title="Delete">
        <Icon icon="mdi:trash" />
    </button>

    <!-- Delete Package Modal -->
    <Modal :show="showDeletePackage" @close="showDeletePackage = false">
        <div class="adm-confirm">
            <div class="adm-confirm-icon">
                <Icon icon="mdi:trash-can-outline" />
            </div>
            <h3>Delete Package</h3>
            <p>Are you sure you want to delete the <b>₹{{ selectedPackage?.amount }}</b> package?</p>
            <div class="adm-confirm-actions">
                <button @click="showDeletePackage = false" class="adm-btn adm-btn-ghost">Cancel</button>
                <button @click="deletePackage" class="adm-btn adm-btn-danger">Delete</button>
            </div>
        </div>
    </Modal>
</template>