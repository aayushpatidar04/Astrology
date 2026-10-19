<template>
    <button @click="openForm(astrologer)" class="adm-pill adm-pill-purple">
        Add Transaction
    </button>

    <Modal :show="showForm" @close="closeModal">
        <form @submit.prevent="saveTransaction">
            <div class="adm-modal-head">
                <h3>Add Transaction for {{ selectedAstrologer?.user?.name }}</h3>
                <button type="button" class="adm-icon-btn" aria-label="Close" @click="closeModal">
                    <Icon icon="mdi:close" />
                </button>
            </div>

            <div class="adm-modal-body">
                <AdminField label="Amount" for-id="amount" :error="form.errors.amount">
                    <input id="amount" type="number" v-model="form.amount" class="adm-input" />
                </AdminField>

                <AdminField label="Mode (NEFT/Bank Transfer)" for-id="mode" :error="form.errors.mode">
                    <input id="mode" type="text" v-model="form.mode" class="adm-input" />
                </AdminField>

                <AdminField label="Reference No" for-id="reference_no">
                    <input id="reference_no" type="text" v-model="form.reference_no" class="adm-input" />
                </AdminField>

                <AdminField label="Remarks" for-id="remarks">
                    <textarea id="remarks" v-model="form.remarks" class="adm-input"></textarea>
                </AdminField>

                <AdminField label="Proof (Image/PDF)" for-id="proof">
                    <input id="proof" type="file" @change="handleFileUpload" class="adm-file" />
                </AdminField>
            </div>

            <div class="adm-modal-foot">
                <button type="button" @click="closeModal" class="adm-btn adm-btn-ghost">Cancel</button>
                <button type="submit" class="adm-btn" :disabled="form.processing">
                    <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                    Save
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Modal from '@/Components/Modal.vue';
import AdminField from '@/Components/AdminField.vue'

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
    // errors / processing are UI state, not request fields
    Object.keys(form.value).forEach(key => {
        if (key === 'errors' || key === 'processing') return
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