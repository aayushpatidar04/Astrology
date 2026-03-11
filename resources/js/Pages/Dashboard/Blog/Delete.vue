<script setup>
import Modal from "@/Components/Modal.vue";
import { inject, ref } from "vue";
import { router } from '@inertiajs/vue3'

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

    <button @click="openDeleteBlog(blog)" class="px-2 py-1 bg-red-500 text-white rounded mt-1">
        Delete
    </button>

    <!-- Delete Blog Modal -->
    <Modal :show="showDeleteBlog" @close="showDeleteBlog = false">
        <div class="p-6 text-center">
            <h3 class="text-xl font-semibold mb-4">Delete Blog</h3>
            <p>Are you sure you want to delete <b>{{ selectedBlog?.title }}</b>?</p>
            <div class="flex justify-center gap-4 mt-6">
                <button @click="showDeleteBlog = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button @click="deleteBlog" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </div>
        </div>
    </Modal>
</template>
