<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Notification from '@/Components/Notification.vue';


const props = defineProps({
    user: Object,
    flash: Object,
})

const mobileMenuOpen = ref(false)


const showDropdown = ref(false)

function toggleDropdown() {
    showDropdown.value = !showDropdown.value
}

function closeDropdown() {
    showDropdown.value = false
}

// click‑outside handler
function handleClickOutside(event) {
    const dropdown = document.getElementById('horoscope-dropdown')
    if (dropdown && !dropdown.contains(event.target)) {
        closeDropdown()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.text-primary {
    color: #FF7010;
}
</style>

<template>
    <Notification :flash="props.flash" />
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
                        <Link :href="route('user.recharge')" class="flex mr-5">
                            <Icon icon="mdi:wallet" width="20" height="20" class="mr-1" /> Balance: ₹{{
                            user.wallet?.balance }}
                        </Link>
                        <div>
                            <Link href="/logout" method="post" as="button" class="flex text-red-400 hover:text-red-500">
                                <Icon icon="mdi:logout" width="20" height="20" class="mr-1" />Logout
                            </Link>
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
                <li id="horoscope-dropdown" class="relative">
                    <div class="flex items-center cursor-pointer" @click="toggleDropdown">
                        <span href="#" class="flex"
                            :class="route().current('horoscope') || route().current('horoscope-type') ? 'text-primary font-bold' : 'hover:text-orange-500'">
                            <template v-if="route().current('horoscope') || route().current('horoscope-type')">
                                <Icon icon="mdi:star-outline" width="20" height="20" class="mr-1" />
                            </template>
                            Horoscopes
                        </span>
                        <!-- Dropdown arrow -->
                        <svg class="w-4 h-4 ml-1 text-gray-500 hover:text-orange-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Dropdown menu -->
                    <ul v-if="showDropdown"
                        class="absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                        <li>
                            <Link :href="route('horoscope-type', { type: 'daily' })"
                                class="block px-4 py-2 hover:bg-orange-100">Today Horoscope</Link>
                        </li>
                        <li>
                            <Link :href="route('horoscope-type', { type: 'yesterday' })"
                                class="block px-4 py-2 hover:bg-orange-100">Yesterday Horoscope</Link>
                        </li>
                        <li>
                            <Link :href="route('horoscope-type', { type: 'tomorrow' })"
                                class="block px-4 py-2 hover:bg-orange-100">Tomorrow Horoscope</Link>
                        </li>
                        <li>
                            <Link :href="route('horoscope-type', { type: 'weekly' })"
                                class="block px-4 py-2 hover:bg-orange-100">Weekly Horoscope</Link>
                        </li>
                        <li>
                            <Link :href="route('horoscope-type', { type: 'monthly' })"
                                class="block px-4 py-2 hover:bg-orange-100">Monthly Horoscope</Link>
                        </li>
                        <li>
                            <Link :href="route('horoscope-type', { type: 'yearly' })"
                                class="block px-4 py-2 hover:bg-orange-100">Yearly Horoscope</Link>
                        </li>
                    </ul>
                </li>

                <li>
                    <Link href="/contact" class="hover:text-orange-500">Contact Us</Link>
                </li>
                <li v-if="user && user.roles[0].name === 'Astrologer'">
                    <Link href="/astrologer/dashboard">Dashboard</Link>
                </li>
                <li v-if="user && user.roles[0].name === 'Admin'">
                    <Link href="/dashboard">Dashboard</Link>
                </li>
                <li v-if="user && user.roles[0].name === 'User'">
                    <Link href="/profile">Profile</Link>
                </li>
                <li v-if="user && user.roles[0].name === 'User'">
                    <Link href="/user/chat-sessions">Chat Sessions</Link>
                </li>
                <li v-if="user && user.roles[0].name === 'User'">
                    <Link href="/user/call-history">Call History</Link>
                </li>
            </ul>

            <!-- Action Buttons -->
            <div v-if="!user || user.roles[0].name === 'User'">
                <div class="flex flex-col md:flex-row md:space-x-3 mt-4 md:mt-0">
                    <Link :href="route('user.chat-with-astrologers')"
                        class="flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 mb-2 md:mb-0">
                        <Icon icon="mdi:wechat" width="20" height="20" class="mr-1" /> Chat With Astrologer
                    </Link>
                    <Link :href="route('user.talk-to-astrologers')"
                        class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <Icon icon="mdi:phone-in-talk-outline" width="20" height="20" class="mr-1" /> Talk To
                        Astrologer
                    </Link>
                </div>
            </div>
            <!-- Auth / Balance -->
            <div class="md:hidden mt-2 md:mt-0">
                <template v-if="user && user.roles[0].name === 'User'">
                    <span class="flex items-center text-gray-700 font-semibold justify-between">
                        <Link :href="route('user.recharge')" class="flex mr-5">
                            <Icon icon="mdi:wallet" width="20" height="20" class="mr-1" /> Balance: ₹{{
                            user.wallet?.balance }}
                        </Link>
                        <div>
                            <Link href="/logout" method="post" as="button" class="flex text-red-400 hover:text-red-500">
                                <Icon icon="mdi:logout" width="20" height="20" class="mr-1" />Logout
                            </Link>
                        </div>
                    </span>
                </template>
                <template v-else>
                    <Link href="/login" class="text-gray-600 hover:text-orange-500 flex items-center">
                        <Icon icon="mdi:login" width="20" height="20" class="mr-1" /> Login / Register
                    </Link>
                </template>
            </div>
        </nav>
    </header>

    <main>
        <slot />
    </main>
</template>
