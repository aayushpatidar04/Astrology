<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

const props = defineProps({
    user: Object
})

const mobileMenuOpen = ref(false)
</script>

<style scoped>
.text-primary {
    color: #FF7010;
}
</style>

<template>
    <header class="bg-white shadow">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center px-4 md:px-6 py-2 text-sm bg-gray-100">
            <!-- Logo -->
            <div class="flex justify-between items-center">
                <img src="/images/logo.png" alt="MyAstroSathi" class="h-12 w-auto" />
                <!-- Mobile menu toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 focus:outline-none">
                    <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" width="28" height="28" />
                </button>
            </div>

            <!-- Contact Info (hidden on small screens, shown on md+) -->
            <div class="hidden md:flex items-center gap-6 mt-2 md:mt-0">
                <span class="flex items-center text-primary">
                    <Icon icon="mdi:phone" width="20" height="20" class="mr-1" /> <b>+1800 326 3264</b>
                </span>
                <span class="flex items-center text-primary">
                    <Icon icon="mdi:email" width="20" height="20" class="mr-1" /> <b>myastrosathi15@gmail.com</b>
                </span>
            </div>

            <!-- Auth / Balance -->
            <div class="hidden md:block mt-2 md:mt-0">
                <template v-if="user">
                    <span class="flex items-center text-gray-700 font-semibold">
                        <div class="flex mr-5">
                            <Icon icon="mdi:wallet" width="20" height="20" class="mr-1" /> Balance: ₹ {{ user.balance }}
                        </div>
                        <div>
                            <Link href="/logout" method="post" as="button" class="flex text-red-400 hover:text-red-500"><Icon icon="mdi:logout" width="20" height="20" class="mr-1" />Logout</Link>
                        </div>
                    </span>
                </template>
                <template v-else>
                    <Link href="/login" class="text-gray-600 hover:text-orange-500 flex items-center">
                        <Icon icon="mdi:login" width="20" height="20" class="mr-1" /> Login / Register
                    </Link>
                </template>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="md:flex md:justify-between md:items-center px-4 md:px-6 py-3"
            :class="mobileMenuOpen ? 'block' : 'hidden md:flex'">
            <ul class="flex flex-col md:flex-row md:space-x-6 text-gray-700 font-medium">
                <li>
                    <Link :href="route('index')" class="flex"
                        :class="route().current('index') ? 'text-primary font-bold' : 'hover:text-orange-500'">
                        <template v-if="route().current('index')">
                            <Icon icon="mdi:home" width="20" height="20" class="mr-1" />
                        </template>
                        Home
                    </Link>
                </li>
                <li>
                    <Link :href="route('about-us')" class="flex"
                        :class="route().current('about-us') ? 'text-primary font-bold' : 'hover:text-orange-500'">
                        <template v-if="route().current('about-us')">
                            <Icon icon="mdi:about" width="20" height="20" class="mr-1" />
                        </template>
                        About
                    </Link>
                </li>
                <li>
                    <Link :href="route('services')" class="flex"
                        :class="route().current('services') ? 'text-primary font-bold' : 'hover:text-orange-500'">
                        <template v-if="route().current('services')">
                            <Icon icon="mdi:cards" width="20" height="20" class="mr-1" />
                        </template>
                        Services
                    </Link>
                </li>
                <li>
                    <Link :href="route('blog')" class="flex"
                        :class="route().current('blog') ? 'text-primary font-bold' : 'hover:text-orange-500'">
                        <template v-if="route().current('blog')">
                            <Icon icon="mdi:blog" width="20" height="20" class="mr-1" />
                        </template>
                        Blogs
                    </Link>
                </li>
                <li>
                    <Link href="/contact" class="hover:text-orange-500">Contact Us</Link>
                </li>
            </ul>

            <!-- Action Buttons -->
            <div v-if="!user || user.roles[0].name === 'User'">
                <div class="flex flex-col md:flex-row md:space-x-3 mt-4 md:mt-0">
                    <Link :href="route('user.chat-with-astrologers')"
                        class="flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 mb-2 md:mb-0">
                        <Icon icon="mdi:wechat" width="20" height="20" class="mr-1" /> Chat With Astrologer
                    </Link>
                    <button
                        class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <Icon icon="mdi:phone-in-talk-outline" width="20" height="20" class="mr-1" /> Talk With Astrologer
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <slot />
    </main>
</template>
