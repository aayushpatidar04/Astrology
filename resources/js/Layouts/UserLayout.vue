<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />

                <!-- User nav -->
                <nav class="space-x-4">
                    <Link href="/" class="text-gray-600 hover:text-gray-900">Home</Link>
                    <Link href="/user/chat-with-astrologers" class="text-gray-600 hover:text-gray-900">Chat</Link>
                    <Link href="/user/chat-sessions" class="text-gray-600 hover:text-gray-900">Chat Sessions</Link>
                    <Link href="/profile" class="text-gray-600 hover:text-gray-900">Profile</Link>
                    <Link href="/logout" method="post" as="button" class="text-red-500 hover:text-red-700">Logout</Link>
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


