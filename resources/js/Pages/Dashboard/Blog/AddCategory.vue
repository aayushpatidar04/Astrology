<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';


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
            console.log('yesssss');
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
    <button type="button" @click="openAddCategory" class="px-3 py-2 bg-green-500 text-white rounded">
        <Icon icon="mdi:plus" width="20" height="20" />
    </button>

    <!-- Add Category Modal -->
    <Modal :show="showAddCategory" @close="showAddCategory = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Add Category</h3>
            <div class="mb-4">
                <InputLabel for="name" value="Name" />
                <TextInput id="name" v-model="category_form.name" type="text" class="mt-1 block w-full"
                    placeholder="Enter category name" />
                <InputError :message="category_form.errors.name" class="mt-2" />
            </div>
            <div class="mb-4">
                <InputLabel for="meta_title" value="Meta Title" />
                <TextInput id="meta_title" v-model="category_form.meta_title" type="text" class="mt-1 block w-full"
                    placeholder="Enter meta title of category" />
                <InputError :message="category_form.errors.meta_title" class="mt-2" />
            </div>
            <div class="mb-4">
                <InputLabel for="meta_description" value="Meta Description" />
                <textarea class="w-full border-gray-300 rounded px-3 py-2" v-model="category_form.meta_description"
                    placeholder="Enter meta description of category"></textarea>
                <InputError :message="category_form.errors.meta_description" class="mt-2" />
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showAddCategory = false"
                    class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" class="px-4 py-2 bg-orange-500 text-white rounded"
                    :class="{ 'opacity-25': category_form.processing }" :disabled="category_form.processing"
                    @click="submitCategoryForm">Save</button>
            </div>
        </div>
    </Modal>
</template>