<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import AddCategory from './AddCategory.vue';


const showAddBlog = ref(false)
const alertRef = inject('alertRef')

const props = defineProps({
    categories: Object
})

const blog_form = useForm({
    blog_category_id: "",
    title: "",
    meta_description: "",
    short_description: "",
    description1: "",
    description2: "",
    images: [{ image: "", alt_text: "" }], // array of objects
    tags: [],
})

const openAddBlog = () => {
    showAddBlog.value = true
}

// Handle file upload
function handleImageUpload(event, index) {
  const file = event.target.files[0]
  if (file) {
    blog_form.images[index].image = file
  }
}

// Dynamic image fields
function addImageField() {
    blog_form.images.push({ image: "", alt_text: "" })
}

function removeImageField(index) {
    if (index > 0) {
        blog_form.images.splice(index, 1)
    }
}

const tagsString = ref("")

// Submit handler
function submitForm() {
    blog_form.tags = tagsString.value.split(",").map(t => t.trim()).filter(Boolean)
    blog_form.post(route("blogs.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showAddBlog.value = false
            blog_form.clearErrors()
            blog_form.reset()
            tagsString.value = ""
            alertRef.value.showAlert('Blog added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button @click="openAddBlog"
        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 font-bold flex items-center">
        <Icon icon="mdi-plus" width="20" height="20" class="mr-1" /> Add Blog
    </button>

    <!-- Add Blog Modal -->
    <Modal :show="showAddBlog" @close="showAddBlog = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Add Blog</h3>
            <!-- Category -->
            <div class="mb-4">
                <InputLabel for="category" value="Category" />
                <div class="flex gap-2">
                    <select v-model="blog_form.blog_category_id" id="category"
                        class="w-full border-gray-300 rounded px-3 py-2">
                        <option disabled value="">Select Category</option>
                        <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <AddCategory />
                </div>
                <InputError :message="blog_form.errors.blog_category_id" />
            </div>

            <!-- Title -->
            <div class="mb-4">
                <InputLabel for="title" value="Title" />
                <TextInput id="title" v-model="blog_form.title" type="text" class="w-full" />
                <InputError :message="blog_form.errors.title" />
            </div>

            <!-- Meta Description -->
            <div class="mb-4">
                <InputLabel for="meta_description" value="Meta Description" />
                <textarea id="meta_description" v-model="blog_form.meta_description"
                    class="w-full border-gray-300 rounded px-3 py-2"></textarea>
                <InputError :message="blog_form.errors.meta_description" />
            </div>

            <!-- Short Description -->
            <div class="mb-4">
                <InputLabel for="short_description" value="Short Description" />
                <textarea id="short_description" v-model="blog_form.short_description"
                    class="w-full border-gray-300 rounded px-3 py-2"></textarea>
                <InputError :message="blog_form.errors.short_description" />
            </div>

            <!-- Description1 -->
            <div class="mb-4">
                <InputLabel for="description1" value="Description 1" />
                <QuillEditor v-model:content="blog_form.description1" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
                <InputError :message="blog_form.errors.description1" />
            </div>

            <!-- Description2 -->
            <div class="mb-4">
                <InputLabel for="description2" value="Description 2 (Optional)" />
                <QuillEditor v-model:content="blog_form.description2" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
                <InputError :message="blog_form.errors.description2" />
            </div>

            <!-- Images -->
            <div class="mb-4">
                <InputLabel value="Images" />
                <div v-for="(img, index) in blog_form.images" :key="index" class="flex gap-2 mb-2">
                    <!-- File input -->
                    <input type="file" @change="handleImageUpload($event, index)" accept="image/*"
                        class="block w-1/2 text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100" />
                    <!-- Alt text -->
                    <TextInput v-model="img.alt_text" type="text" placeholder="Alt Image text" class="w-1/2" />

                    <!-- Add/Remove buttons -->
                    <button v-if="index > 0" type="button" @click="removeImageField(index)"
                        class="px-3 py-1 bg-red-500 text-white rounded">
                        <Icon icon="mdi:minus" />
                    </button>
                    <button v-else type="button" @click="addImageField"
                        class="px-3 py-1 bg-green-500 text-white rounded">
                        <Icon icon="mdi:plus" />
                    </button>
                </div>
                <InputError :message="blog_form.errors.images" />

            </div>

            <!-- Tags -->
            <div class="mb-4">
                <InputLabel for="tags" value="Tags" />
                <!-- Example using vue-tagify or similar -->
                <TextInput id="tags" v-model="tagsString" type="text" placeholder="Enter tags (comma separated)"
                    class="w-full" />
                <InputError :message="blog_form.errors.tags" />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <button type="button" @click="showAddBlog = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" @click="submitForm"
                    class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
            </div>
        </div>
    </Modal>

</template>