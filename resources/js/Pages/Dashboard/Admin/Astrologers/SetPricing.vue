<script setup>
import { ref, inject } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Modal from '@/Components/Modal.vue';
import AdminField from '@/Components/AdminField.vue'

const props = defineProps({
    astrologer: Object
})

const alertRef = inject('alertRef')
const showPricingForm = ref(false)
const selectedAstrologer = ref(null)

// useForm handles state + errors
const form = useForm({
    asked_call_price: null,
    charged_call_price: null,
    asked_text_price: null,
    charged_text_price: null,
})

function openPricingForm(astrologer) {
    selectedAstrologer.value = astrologer
    form.asked_call_price = astrologer.asked_call_price
    form.charged_call_price = astrologer.charged_call_price
    form.asked_text_price = astrologer.asked_text_price
    form.charged_text_price = astrologer.charged_text_price
    showPricingForm.value = true
}

function savePricing() {
    form.post(route('admin.astrologers.updatePricing', selectedAstrologer.value.id), {
        onSuccess: () => {
            showPricingForm.value = false
            alertRef.value.showAlert('Pricing updated successfully!', 'success');
        }
    })
}

const closeModal = () => {
    showPricingForm.value = false;
};
</script>


<template>
    <button @click="openPricingForm(props.astrologer)" class="adm-pill adm-pill-purple">
        Set Pricing
    </button>

    <Modal :show="showPricingForm" @close="closeModal">
        <div class="adm-modal-head">
            <h3>Set Pricing for {{ selectedAstrologer?.user?.name }}</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="closeModal">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <div class="adm-grid-2">
                <!-- Asked Call Price -->
                <AdminField label="Asked Call Price (per min)" for-id="asked_call_price"
                    :error="form.errors.asked_call_price">
                    <input id="asked_call_price" type="number" v-model="form.asked_call_price" class="adm-input" />
                </AdminField>

                <!-- Charged Call Price -->
                <AdminField label="Charged Call Price (per min)" for-id="charged_call_price"
                    :error="form.errors.charged_call_price">
                    <input id="charged_call_price" type="number" v-model="form.charged_call_price" class="adm-input" />
                </AdminField>

                <!-- Asked Text Price -->
                <AdminField label="Asked Text Price (per min)" for-id="asked_text_price"
                    :error="form.errors.asked_text_price">
                    <input id="asked_text_price" type="number" v-model="form.asked_text_price" class="adm-input" />
                </AdminField>

                <!-- Charged Text Price -->
                <AdminField label="Charged Text Price (per min)" for-id="charged_text_price"
                    :error="form.errors.charged_text_price">
                    <input id="charged_text_price" type="number" v-model="form.charged_text_price" class="adm-input" />
                </AdminField>
            </div>
        </div>

        <div class="adm-modal-foot">
            <button type="button" @click="showPricingForm = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" @click="savePricing" class="adm-btn" :disabled="form.processing">
                <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Save
            </button>
        </div>
    </Modal>
</template>