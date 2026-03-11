<script setup>
import { ref, provide } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import Alert from '@/Components/Alert.vue'

const alertRef = ref(null)
provide('alertRef', alertRef)

const showingNavigationDropdown = ref(false)

const page = usePage()
const online = ref(!!page.props.auth.user.astrologer.online)

const toggleStatus = async () => {
    online.value = !online.value
    try {
        await axios.post('/astrologer/status', { online: online.value ? 1 : 0 })
        if (online.value) {
            alertRef.value.showAlert('You are now online and available for chats!', 'success')
        } else {
            alertRef.value.showAlert('You are now offline. Users cannot reach you.', 'success')
        }
    } catch (error) {
        online.value = !online.value
        alertRef.value.showAlert('Something went wrong, please try after some time!', 'error')
    }
}
</script>

<template>
    <Alert ref="alertRef" />
    <div class="min-h-screen bg-gray-100">
        <nav class="border-b border-gray-100 bg-white">
            <!-- Primary Navigation Menu -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link href="/astrologer/dashboard">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-indigo-600" />
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink href="/astrologer/dashboard" :active="route().current('astrologer.dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink href="/astrologer/chats" :active="route().current('astrologer.chats')">
                                Chats
                            </NavLink>
                            <NavLink href="/astrologer/profile" :active="route().current('astrologer.profile')">
                                Profile
                            </NavLink>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <div>
                            <button @click="toggleStatus" :class="online ? 'bg-green-500' : 'bg-gray-400'"
                                class="relative inline-flex h-6 w-12 items-center rounded-full transition-colors">
                                <span :class="online ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" />
                            </button>
                        </div>
                        <div class="relative ms-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 hover:text-gray-700 focus:outline-none">
                                            {{ $page.props.auth.user.name }}
                                            <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink href="/astrologer/profile">Profile</DropdownLink>
                                    <DropdownLink href="/logout" method="post" as="button">Log Out</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink href="/astrologer/dashboard" :active="route().current('astrologer.dashboard')">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink href="/astrologer/chats" :active="route().current('astrologer.chats')">
                        Chats
                    </ResponsiveNavLink>
                    <ResponsiveNavLink href="/astrologer/profile" :active="route().current('astrologer.profile')">
                        Profile
                    </ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="border-t border-gray-200 pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink href="/astrologer/profile">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink href="/logout" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
