<script setup>
import AdminField from '@/Components/AdminField.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';


const showAddCategory = ref(false)
const alertRef = inject('alertRef')

const props = defineProps({
    categories: Object
})

const category_form = useForm({
    name: '',
    meta_title: '',
    meta_description: '',
})

function openAddCategory() {
    showAddCategory.value = true
}

const submitCategoryForm = () => {
    category_form.post(route('blog-category.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddCategory.value = false;
            category_form.clearErrors();
            category_form.reset();

            alertRef.value.showAlert('Category added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button type="button" @click="openAddCategory" class="adm-btn adm-btn-success adm-btn-square"
        title="Add category">
        <Icon icon="mdi:plus" width="20" height="20" />
    </button>

    <!-- Add Category Modal -->
    <Modal :show="showAddCategory" @close="showAddCategory = false">
        <div class="adm-modal-head">
            <h3>Add Category</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="showAddCategory = false">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <AdminField label="Name" for-id="name" :error="category_form.errors.name">
                <input id="name" v-model="category_form.name" type="text" class="adm-input"
                    placeholder="Enter category name" />
            </AdminField>

            <AdminField label="Meta Title" for-id="meta_title" :error="category_form.errors.meta_title">
                <input id="meta_title" v-model="category_form.meta_title" type="text" class="adm-input"
                    placeholder="Enter meta title of category" />
            </AdminField>

            <AdminField label="Meta Description" for-id="meta_description"
                :error="category_form.errors.meta_description">
                <textarea id="meta_description" class="adm-input" v-model="category_form.meta_description"
                    placeholder="Enter meta description of category"></textarea>
            </AdminField>
        </div>

        <div class="adm-modal-foot">
            <button type="button" @click="showAddCategory = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" class="adm-btn" :disabled="category_form.processing" @click="submitCategoryForm">
                <Icon v-if="category_form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Save
            </button>
        </div>
    </Modal>
</template>