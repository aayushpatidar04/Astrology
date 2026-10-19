<script setup>
import AdminField from '@/Components/AdminField.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';

const showEditBanner = ref(false)
const alertRef = inject('alertRef')

const props = defineProps({
    banner: Object
})

const form = useForm({
    title: "",
    image: "",
    link: "",
})

const openEditBanner = (banner) => {
    form.title = banner.title
    form.link = banner.link
    form.image = banner.image
    showEditBanner.value = true
}

function handleImageUpload(event) {
    const file = event.target.files[0]
    if (file) {
        form.image = file
    }
}

// Submit handler
function submitForm() {
    form.post(route("admin.banners.edit", { id: props.banner.id }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEditBanner.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Banner updated successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}
</script>

<template>
    <button @click="openEditBanner(props.banner)" class="adm-icon-btn adm-icon-edit" title="Edit">
        <Icon icon="mdi:pencil" />
    </button>

    <!-- Edit Banner Modal -->
    <Modal :show="showEditBanner" @close="showEditBanner = false">
        <div class="adm-modal-head">
            <h3>Edit Banner</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="showEditBanner = false">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <AdminField label="Title" for-id="title" :error="form.errors.title">
                <input id="title" v-model="form.title" type="text" class="adm-input" required />
            </AdminField>

            <AdminField label="Link" for-id="link" :error="form.errors.link">
                <input id="link" v-model="form.link" type="text" class="adm-input" />
            </AdminField>

            <AdminField label="Image" for-id="image" :error="form.errors.image">
                <input type="file" id="image" @change="handleImageUpload($event)" accept="image/*" class="adm-file"
                    required />
            </AdminField>
        </div>

        <div class="adm-modal-foot">
            <button type="button" @click="showEditBanner = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" @click="submitForm" class="adm-btn" :disabled="form.processing">
                <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Save
            </button>
        </div>
    </Modal>

</template>