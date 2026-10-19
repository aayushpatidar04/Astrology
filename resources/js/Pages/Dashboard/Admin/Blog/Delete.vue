<script setup>
import Modal from "@/Components/Modal.vue";
import { inject, ref } from "vue";
import { router } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue";

const props = defineProps({
    blog: Object,
})

const selectedBlog = ref(null)
const showDeleteBlog = ref(false)
const alertRef = inject('alertRef')

function openDeleteBlog(blog) {
    selectedBlog.value = blog
    showDeleteBlog.value = true
}



const deleteBlog = () => {
    router.delete(route("blogs.delete", {id: props.blog.id}), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteBlog.value = false
            alertRef.value.showAlert("Blog deleted successfully!", 'success')
        },
        onError: () => {
            alertRef.value.showAlert("Failed to delete blog.", 'error')
        }
    })
}

</script>

<template>

    <button @click="openDeleteBlog(blog)" class="adm-icon-btn adm-icon-del" title="Delete">
        <Icon icon="mdi:trash" />
    </button>

    <!-- Delete Blog Modal -->
    <Modal :show="showDeleteBlog" @close="showDeleteBlog = false">
        <div class="adm-confirm">
            <div class="adm-confirm-icon">
                <Icon icon="mdi:trash-can-outline" />
            </div>
            <h3>Delete Blog</h3>
            <p>Are you sure you want to delete <b>{{ selectedBlog?.title }}</b>?</p>
            <div class="adm-confirm-actions">
                <button @click="showDeleteBlog = false" class="adm-btn adm-btn-ghost">Cancel</button>
                <button @click="deleteBlog" class="adm-btn adm-btn-danger">Delete</button>
            </div>
        </div>
    </Modal>
</template>