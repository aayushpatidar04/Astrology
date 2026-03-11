<script setup>
import { inject, ref } from "vue";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { Icon } from '@iconify/vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import AddCategory from "./AddCategory.vue";
import { useForm } from "@inertiajs/vue3"

const alertRef = inject('alertRef')

const props = defineProps({
    blog: Object,
    categories: Object
})

// Modal state
const showEditBlog = ref(false)

const selectedBlog = useForm({
    blog_category_id: "",
    title: "",
    meta_description: "",
    short_description: "",
    description1: "",
    description2: "",
    images: [{ image: null, alt_text: "" }],
    old_images: [{ image: null, alt_text: "" }],
    removed_images: [],
    tags: []
})

function openEditBlog(blog) {
    selectedBlog.blog_category_id = blog.blog_category_id
    selectedBlog.title = blog.title
    selectedBlog.meta_description = blog.meta_description
    selectedBlog.short_description = blog.short_description
    selectedBlog.description1 = blog.description1
    selectedBlog.description2 = blog.description2
    selectedBlog.images = [{ image: null, alt_text: "" }]
    selectedBlog.old_images = blog.images
    selectedBlog.removed_images = []
    selectedBlog.tags = blog.tags || []

    tagsString.value = (blog.tags || []).join(", ")
    showEditBlog.value = true
}

// Handle file upload
function handleImageUpload(event, index) {
    const file = event.target.files[0]
    if (file) {
        selectedBlog.images[index].image = file
    }
}

// Dynamic image fields
function addImageField() {
    selectedBlog.images.push({ image: "", alt_text: "" })
}

function removeImageField(index) {
    if (index > 0) {
        selectedBlog.images.splice(index, 1)
    }
}

function removeOldImage(path) {
    selectedBlog.old_images = selectedBlog.old_images.filter(img => img.image !== path)
    selectedBlog.removed_images.push(path)
}


const tagsString = ref("")

function updateBlog() {
    // convert tags string to array
    selectedBlog.tags = tagsString.value
        .split(",")
        .map(t => t.trim())
        .filter(Boolean)

    selectedBlog.post(route("blogs.update", { id: props.blog.id }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEditBlog.value = false
            selectedBlog.clearErrors()
            selectedBlog.reset()
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
    <button @click="openEditBlog(blog)" class="px-2 py-1 bg-blue-500 text-white rounded mr-2 mt-1">
        Edit
    </button>

    <!-- Edit Blog Modal -->
    <Modal :show="showEditBlog" @close="showEditBlog = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Edit Blog</h3>

            <!-- Category -->
            <div class="mb-4">
                <InputLabel for="category" value="Category" />
                <div class="flex gap-2">
                    <select v-model="selectedBlog.blog_category_id" id="category"
                        class="w-full border-gray-300 rounded px-3 py-2">
                        <option disabled value="">Select Category</option>
                        <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <AddCategory />
                </div>
                <InputError :message="selectedBlog.errors.blog_category_id" />
            </div>

            <div class="mb-4">
                <InputLabel for="title" value="Title" />
                <TextInput id="title" v-model="selectedBlog.title" type="text" class="w-full" />
                <InputError :message="selectedBlog.errors.title" />
            </div>

            <!-- Meta Description -->
            <div class="mb-4">
                <InputLabel for="meta_description" value="Meta Description" />
                <textarea id="meta_description" v-model="selectedBlog.meta_description"
                    class="w-full border-gray-300 rounded px-3 py-2"></textarea>
                <InputError :message="selectedBlog.errors.meta_description" />
            </div>

            <!-- Short Description -->
            <div class="mb-4">
                <InputLabel for="short_description" value="Short Description" />
                <textarea id="short_description" v-model="selectedBlog.short_description"
                    class="w-full border-gray-300 rounded px-3 py-2"></textarea>
                <InputError :message="selectedBlog.errors.short_description" />
            </div>

            <!-- Description1 -->
            <div class="mb-4">
                <InputLabel for="description1" value="Description 1" />
                <QuillEditor v-model:content="selectedBlog.description1" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
                <InputError :message="selectedBlog.errors.description1" />
            </div>

            <!-- Description2 -->
            <div class="mb-4">
                <InputLabel for="description2" value="Description 2 (Optional)" />
                <QuillEditor v-model:content="selectedBlog.description2" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
                <InputError :message="selectedBlog.errors.description2" />
            </div>

            <div class="mb-4">
                <InputLabel value="Existing Images" />
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="(img, index) in selectedBlog.old_images" :key="index" class="relative">
                        <!-- Image preview -->
                        <img :src="`/${img.image}`" alt="Blog image" class="h-24 w-full object-cover rounded shadow" />

                        <!-- Small remove button -->
                        <button type="button" @click="removeOldImage(img.image)"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                            ×
                        </button>
                    </div>
                </div>
            </div>


            <!-- Images -->
            <div class="mb-4">
                <InputLabel value="Images" />
                <div v-for="(img, index) in selectedBlog.images" :key="index" class="flex gap-2 mb-2">
                    <input type="file" @change="handleImageUpload($event, index)" accept="image/*" class="block w-1/2 text-sm text-gray-500
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
                <InputError :message="selectedBlog.errors.images" />

            </div>

            <!-- Tags -->
            <div class="mb-4">
                <InputLabel for="tags" value="Tags" />
                <!-- Example using vue-tagify or similar -->
                <TextInput id="tags" v-model="tagsString" type="text" placeholder="Enter tags (comma separated)"
                    class="w-full" />
                <InputError :message="selectedBlog.errors.tags" />
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="showEditBlog = false"
                    class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" @click="updateBlog"
                    class="px-4 py-2 bg-blue-500 text-white rounded">Update</button>
            </div>
        </div>
    </Modal>
</template>