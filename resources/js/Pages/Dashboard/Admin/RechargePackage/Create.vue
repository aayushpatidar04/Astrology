<script setup>
import AdminField from '@/Components/AdminField.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';

const showAddPackage = ref(false)
const alertRef = inject('alertRef')

const form = useForm({
    amount: "",
    bonus_amount: "",
    label: "",
    recommended: "",
    type: "",
})

const openAddPackage = () => {
    showAddPackage.value = true
}

// Submit handler
function submitForm() {
    form.post(route("admin.recharge-packages.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showAddPackage.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Package added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}
</script>

<template>
    <button @click="openAddPackage" class="adm-btn">
        <Icon icon="mdi-plus" width="20" height="20" /> Add Package
    </button>

    <!-- Add Package Modal -->
    <Modal :show="showAddPackage" @close="showAddPackage = false">
        <div class="adm-modal-head">
            <h3>Add Package</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="showAddPackage = false">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <div class="adm-grid-2">
                <AdminField label="Amount" for-id="amount" :error="form.errors.amount">
                    <input id="amount" v-model="form.amount" type="number" class="adm-input" required />
                </AdminField>

                <AdminField label="Bonus Amount" for-id="bonus_amount" :error="form.errors.bonus_amount">
                    <input id="bonus_amount" v-model="form.bonus_amount" type="number" class="adm-input" />
                </AdminField>

                <AdminField label="Label" for-id="label" :error="form.errors.label">
                    <input id="label" v-model="form.label" type="text" class="adm-input" />
                </AdminField>

                <AdminField label="Type" for-id="type" :error="form.errors.type">
                    <select id="type" v-model="form.type" class="adm-input">
                        <option value="" selected disabled>Select Type</option>
                        <option value="first_time">First Time</option>
                        <option value="regular">Regular</option>
                        <option value="special">Special</option>
                    </select>
                </AdminField>
            </div>

            <AdminField :error="form.errors.recommended">
                <label class="adm-check">
                    <input v-model="form.recommended" type="checkbox" />
                    Mark as recommended
                </label>
            </AdminField>
        </div>

        <div class="adm-modal-foot">
            <button type="button" @click="showAddPackage = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" @click="submitForm" class="adm-btn" :disabled="form.processing">
                <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Save
            </button>
        </div>
    </Modal>

</template>