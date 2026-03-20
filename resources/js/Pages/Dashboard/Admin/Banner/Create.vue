<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';



const showAddBanner = ref(false)
const alertRef = inject('alertRef')

const form = useForm({
    title: "",
    image: "",
    link: "",
})

const openAddBanner = () => {
    showAddBanner.value = true
}

function handleImageUpload(event, index) {
  const file = event.target.files[0]
  if (file) {
    form.image = file
  }
}

// Submit handler
function submitForm() {
    form.post(route("admin.banners.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showAddBanner.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Banner added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button @click="openAddBanner"
        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 font-bold flex items-center">
        <Icon icon="mdi-plus" width="20" height="20" class="mr-1" /> Add Banner
    </button>

    <!-- Add Blog Modal -->
    <Modal :show="showAddBanner" @close="showAddBanner = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Add Banner</h3>

            <!-- Title -->
            <div class="mb-4">
                <InputLabel for="title" value="Title" />
                <TextInput id="title" v-model="form.title" type="text" class="w-full" required/>
                <InputError :message="form.errors.title" />
            </div>

            <!-- Link -->
            <div class="mb-4">
                <InputLabel for="link" value="Link" />
                <TextInput id="link" v-model="form.link" type="text" class="w-full"/>
                <InputError :message="form.errors.link" />
            </div>

            <!-- Image -->
            <div class="mb-4">
                <InputLabel for="image" value="Image" />
                <input type="file" id="image" @change="handleImageUpload($event, index)" accept="image/*"
                    class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100" required/>
                <InputError :message="form.errors.image" />

            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <button type="button" @click="showAddBanner = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" @click="submitForm"
                    class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
            </div>
        </div>
    </Modal>

</template>