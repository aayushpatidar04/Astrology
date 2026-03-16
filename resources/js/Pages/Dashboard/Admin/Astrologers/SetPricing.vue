<script setup>
import { ref, inject } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

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
    <button @click="openPricingForm(props.astrologer)"
        class="px-3 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800 hover:bg-purple-200 whitespace-nowrap">
        Set Pricing
    </button>

    <Modal :show="showPricingForm" @close="closeModal">
        <div class="bg-white p-6 rounded shadow-lg max-w-5xl">
            <h2 class="text-lg font-semibold mb-4">
                Set Pricing for {{ selectedAstrologer?.user?.name }}
            </h2>
    
            <div class="grid grid-cols-2 gap-5">
                <!-- Asked Call Price -->
                <div>
                    <InputLabel for="asked_call_price" value="Asked Call Price (per min)" />
                    <TextInput id="asked_call_price" type="number" v-model="form.asked_call_price"
                        class="mt-1 block w-full" />
                    <InputError :message="form.errors.asked_call_price" class="mt-1" />
                </div>
    
                <!-- Charged Call Price -->
                <div>
                    <InputLabel for="charged_call_price" value="Charged Call Price (per min)" />
                    <TextInput id="charged_call_price" type="number" v-model="form.charged_call_price"
                        class="mt-1 block w-full" />
                    <InputError :message="form.errors.charged_call_price" class="mt-1" />
                </div>
    
                <!-- Asked Text Price -->
                <div>
                    <InputLabel for="asked_text_price" value="Asked Text Price (per min)" />
                    <TextInput id="asked_text_price" type="number" v-model="form.asked_text_price"
                        class="mt-1 block w-full" />
                    <InputError :message="form.errors.asked_text_price" class="mt-1" />
                </div>
    
                <!-- Charged Text Price -->
                <div>
                    <InputLabel for="charged_text_price" value="Charged Text Price (per min)" />
                    <TextInput id="charged_text_price" type="number" v-model="form.charged_text_price"
                        class="mt-1 block w-full" />
                    <InputError :message="form.errors.charged_text_price" class="mt-1" />
                </div>
            </div>
    
            <div class="flex justify-end gap-2 mt-4">
                <button @click="showPricingForm = false" class="px-3 py-1 rounded bg-gray-200">Cancel</button>
                <button @click="savePricing" class="px-3 py-1 rounded bg-green-600 text-white"
                    :disabled="form.processing">
                    Save
                </button>
            </div>
        </div>
    </Modal>
</template>
