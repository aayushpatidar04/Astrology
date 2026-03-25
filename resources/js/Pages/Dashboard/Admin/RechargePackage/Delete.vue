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
    router.delete(route("admin.recharge-packages.destroy", {id: props.recharge.id}), {
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

    <button @click="openDeletePackage(recharge)" class="text-red-500 text-2xl">
        <Icon icon="mdi:trash"></Icon>
    </button>

    <!-- Delete Package Modal -->
    <Modal :show="showDeletePackage" @close="showDeletePackage = false">
        <div class="p-6 text-center">
            <h3 class="text-xl font-semibold mb-4">Delete Package</h3>
            <p>Are you sure you want to delete <b>₹{{ selectedPackage?.amount }}</b> package?</p>
            <div class="flex justify-center gap-4 mt-6">
                <button @click="showDeletePackage = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button @click="deletePackage" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </div>
        </div>
    </Modal>
</template>
