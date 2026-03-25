<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const mobileMenuOpen = ref(false)
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />

                <!-- Desktop nav -->
                <nav class="hidden md:flex space-x-4">
                    <Link href="/" class="text-gray-600 hover:text-gray-900">Home</Link>
                    <Link href="/user/chat-with-astrologers" class="text-gray-600 hover:text-gray-900">Chat</Link>
                    <Link href="/user/chat-sessions" class="text-gray-600 hover:text-gray-900">Chat Sessions</Link>
                    <Link href="/profile" class="text-gray-600 hover:text-gray-900">Profile</Link>
                    <Link href="/logout" method="post" as="button" class="text-red-500 hover:text-red-700">Logout</Link>
                </nav>

                <!-- Mobile menu button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile nav -->
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
                <nav class="px-4 py-4 space-y-2">
                    <Link href="/" class="block text-gray-600 hover:text-gray-900">Home</Link>
                    <Link href="/user/chat-with-astrologers" class="block text-gray-600 hover:text-gray-900">Chat</Link>
                    <Link href="/user/chat-sessions" class="block text-gray-600 hover:text-gray-900">Chat Sessions
                    </Link>
                    <Link href="/profile" class="block text-gray-600 hover:text-gray-900">Profile</Link>
                    <Link href="/logout" method="post" as="button" class="block text-red-500 hover:text-red-700">Logout
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t">
            <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-500">
                © {{ new Date().getFullYear() }} My Astro Sathi. All rights reserved.
            </div>
        </footer>
    </div>
</template>
