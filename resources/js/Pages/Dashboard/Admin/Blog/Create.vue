<script setup>
import AdminField from '@/Components/AdminField.vue';
import Modal from '@/Components/Modal.vue';
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
    <button @click="openAddBlog" class="adm-btn">
        <Icon icon="mdi-plus" width="20" height="20" /> Add Blog
    </button>

    <!-- Add Blog Modal -->
    <Modal :show="showAddBlog" @close="showAddBlog = false">
        <div class="adm-modal-head">
            <h3>Add Blog</h3>
            <button type="button" class="adm-icon-btn" aria-label="Close" @click="showAddBlog = false">
                <Icon icon="mdi:close" />
            </button>
        </div>

        <div class="adm-modal-body">
            <!-- Category -->
            <AdminField label="Category" for-id="category" :error="blog_form.errors.blog_category_id">
                <div class="adm-inline">
                    <select v-model="blog_form.blog_category_id" id="category" class="adm-input">
                        <option disabled value="">Select Category</option>
                        <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <AddCategory />
                </div>
            </AdminField>

            <!-- Title -->
            <AdminField label="Title" for-id="title" :error="blog_form.errors.title">
                <input id="title" v-model="blog_form.title" type="text" class="adm-input" />
            </AdminField>

            <!-- Meta Description -->
            <AdminField label="Meta Description" for-id="meta_description" :error="blog_form.errors.meta_description">
                <textarea id="meta_description" v-model="blog_form.meta_description" class="adm-input"></textarea>
            </AdminField>

            <!-- Short Description -->
            <AdminField label="Short Description" for-id="short_description"
                :error="blog_form.errors.short_description">
                <textarea id="short_description" v-model="blog_form.short_description" class="adm-input"></textarea>
            </AdminField>

            <!-- Description1 -->
            <AdminField label="Description 1" :error="blog_form.errors.description1">
                <QuillEditor v-model:content="blog_form.description1" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
            </AdminField>

            <!-- Description2 -->
            <AdminField label="Description 2 (Optional)" :error="blog_form.errors.description2">
                <QuillEditor v-model:content="blog_form.description2" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />
            </AdminField>

            <!-- Images -->
            <AdminField label="Images" :error="blog_form.errors.images">
                <div v-for="(img, index) in blog_form.images" :key="index" class="adm-inline"
                    style="margin-bottom: 0.5rem">
                    <!-- File input -->
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
            <AdminField label="Tags" for-id="tags" :error="blog_form.errors.tags">
                <input id="tags" v-model="tagsString" type="text" placeholder="Enter tags (comma separated)"
                    class="adm-input" />
            </AdminField>
        </div>

        <!-- Actions -->
        <div class="adm-modal-foot">
            <button type="button" @click="showAddBlog = false" class="adm-btn adm-btn-ghost">Cancel</button>
            <button type="button" @click="submitForm" class="adm-btn" :disabled="blog_form.processing">
                <Icon v-if="blog_form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                Save
            </button>
        </div>
    </Modal>

</template>