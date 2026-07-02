<template>
    <button @click="openForm(astrologer)"
        class="px-3 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800 hover:bg-purple-200 whitespace-nowrap">
        Add Transaction
    </button>

    <Modal :show="showForm" @close="closeModal">
        <div class="bg-white p-6 rounded shadow-lg max-w-3xl">
            <h2 class="text-lg font-semibold mb-4">
                Add Transaction for {{ selectedAstrologer?.user?.name }}
            </h2>

            <form @submit.prevent="saveTransaction" class="space-y-4">
                <div>
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" type="number" v-model="form.amount" class="mt-1 block w-full" />
                    <InputError :message="form.errors.amount" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="mode" value="Mode (NEFT/Bank Transfer)" />
                    <TextInput id="mode" type="text" v-model="form.mode" class="mt-1 block w-full" />
                    <InputError :message="form.errors.mode" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="reference_no" value="Reference No" />
                    <TextInput id="reference_no" type="text" v-model="form.reference_no" class="mt-1 block w-full" />
                </div>

                <div>
                    <InputLabel for="remarks" value="Remarks" />
                    <textarea id="remarks" v-model="form.remarks" class="mt-1 block w-full border rounded"></textarea>
                </div>

                <div>
                    <InputLabel for="proof" value="Proof (Image/PDF)" />
                    <input id="proof" type="file" @change="handleFileUpload" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeModal" class="px-3 py-1 rounded bg-gray-200">Cancel</button>
                    <button type="submit" class="px-3 py-1 rounded bg-green-600 text-white" :disabled="form.processing">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    astrologer: Object
})

const showForm = ref(false)
const selectedAstrologer = ref(null)
const form = ref({
    astrologer_id: null,
    amount: '',
    mode: '',
    reference_no: '',
    remarks: '',
    proof: null,
    errors: {},
    processing: false,
})

function openForm(astrologer) {
    selectedAstrologer.value = astrologer
    form.value.astrologer_id = astrologer.id
    showForm.value = true
}

function closeModal() {
    showForm.value = false
}

function handleFileUpload(e) {
    form.value.proof = e.target.files[0]
}

function saveTransaction() {
    form.value.processing = true
    const data = new FormData()
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null) data.append(key, form.value[key])
    })

    router.post(route('admin.transactions.store'), data, {
        onFinish: () => {
            form.value.processing = false
            showForm.value = false
        }
    })
}
</script>
