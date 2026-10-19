<script setup>
import { inject, ref } from "vue";
import AdminField from '@/Components/AdminField.vue';
import Modal from '@/Components/Modal.vue';
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
            alertRef.value.showAlert('Blog updated successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button @click="openEditBlog(blog)" class="adm-icon-btn adm-icon-edit" title="Edit">
        <Icon icon="mdi:pencil" />
    </button>

    <!-- Edit Blog Modal -->
    <Modal :show="showEditBlog" @close="showEditBlog = false">
        <div class="adm-modal-head">
            <h3>Edit Blog</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="showEditBlog = false">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <!-- Category -->
            <AdminField label="Category" for-id="category" :error="selectedBlog.errors.blog_category_id">
                <div class="adm-inline">
                    <select v-model="selectedBlog.blog_category_id" id="category" class="adm-input">
                        <option disabled value="">Select Category</option>
                        <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <AddCategory />
                </div>
            </AdminField>

            <AdminField label="Title" for-id="title" :error="selectedBlog.errors.title">
                <input id="title" v-model="selectedBlog.title" type="text" class="adm-input" />
            </AdminField>

            <!-- Meta Description -->
            <AdminField label="Meta Description" for-id="meta_description"
                :error="selectedBlog.errors.meta_description">
                <textarea id="meta_description" v-model="selectedBlog.meta_description"
                    class="adm-input"></textarea>
            </AdminField>

            <!-- Short Description -->
            <AdminField label="Short Description" for-id="short_description"
                :error="selectedBlog.errors.short_description">
                <textarea id="short_description" v-model="selectedBlog.short_description"
                    class="adm-input"></textarea>
            </AdminField>

            <!-- Description1 -->
            <AdminField label="Description 1" :error="selectedBlog.errors.description1">
                <QuillEditor v-model:content="selectedBlog.description1" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
            </AdminField>

            <!-- Description2 -->
            <AdminField label="Description 2 (Optional)" :error="selectedBlog.errors.description2">
                <QuillEditor v-model:content="selectedBlog.description2" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
            </AdminField>

            <!-- Existing images -->
            <div v-if="selectedBlog.old_images?.length" class="adm-field">
                <p class="adm-section-label">Existing Images</p>
                <div class="adm-thumbs">
                    <div v-for="(img, index) in selectedBlog.old_images" :key="index" class="adm-thumb">
                        <!-- Image preview -->
                        <img :src="`/${img.image}`" alt="Blog image" />

                        <!-- Small remove button -->
                        <button type="button" @click="removeOldImage(img.image)" title="Remove image">
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <AdminField label="Images" :error="selectedBlog.errors.images">
                <div v-for="(img, index) in selectedBlog.images" :key="index" class="adm-inline"
                    style="margin-bottom: 0.5rem">
                    <input type="file" @change="handleImageUpload($event, index)" accept="image/*" class="adm-file" />
                    <!-- Alt text -->
                    <input v-model="img.alt_text" type="text" placeholder="Alt Image text" class="adm-input" />

                    <!-- Add/Remove buttons -->
                    <button v-if="index > 0" type="button" @click="removeImageField(index)"
                        class="adm-btn adm-btn-danger adm-btn-square" title="Remove image">
                        <Icon icon="mdi:minus" />
                    </button>
                    <button v-else type="button" @click="addImageField" class="adm-btn adm-btn-success adm-btn-square"
                        title="Add another image">
                        <Icon icon="mdi:plus" />
                    </button>
                </div>
            </AdminField>

            <!-- Tags -->
            <AdminField label="Tags" for-id="tags" :error="selectedBlog.errors.tags">
                <input id="tags" v-model="tagsString" type="text" placeholder="Enter tags (comma separated)"
                    class="adm-input" />
            </AdminField>
        </div>

        <div class="adm-modal-foot">
            <button type="button" @click="showEditBlog = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" @click="updateBlog" class="adm-btn" :disabled="selectedBlog.processing">
                <Icon v-if="selectedBlog.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Update
            </button>
        </div>
    </Modal>
</template>