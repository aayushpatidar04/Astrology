<template>
    <div class="flex h-screen items-center justify-center bg-gray-100">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-orange-600">{{ props.title }}</h2>
            <p class="text-gray-600 mb-6">
                {{ props.message }}
            </p>
            <div class="flex gap-4 justify-center">
                <button @click="goBack" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Back
                </button>
                <button @click="logout" class="px-4 py-2 bg-red-500 rounded hover:bg-red-600 text-white">
                    Logout
                </button>

            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
function logout() {
    router.post('/logout')
}
function goBack() {
    window.history.back()
}

const props = defineProps({
    title: String,
    message: String
})
</script>
