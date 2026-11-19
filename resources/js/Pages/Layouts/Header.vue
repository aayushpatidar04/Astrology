<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import Notification from '@/Components/Notification.vue';

const props = defineProps({
    user: Object,
    flash: Object,
})

const role = computed(() => props.user?.roles?.[0]?.name)

const mobileMenuOpen = ref(false)
const scrolled = ref(false)
const showDropdown = ref(false)
const showHoroscopeDropdown = ref(false)
const showCalculatorsDropdown = ref(false)

function closeDropdown() {
    showDropdown.value = false
}

function closeHoroscopeDropdown() {
    showHoroscopeDropdown.value = false
}

function closeCalculatorsDropdown() {
    showCalculatorsDropdown.value = false
}

// click-outside handler
function handleClickOutside(event) {
    const freeDropdown = document.getElementById('free-services-dropdown')
    if (freeDropdown && !freeDropdown.contains(event.target)) {
        closeDropdown()
    }
    const horoDropdown = document.getElementById('horoscope-dropdown')
    if (horoDropdown && !horoDropdown.contains(event.target)) {
        closeHoroscopeDropdown()
    }
    const calcDropdown = document.getElementById('calculators-dropdown')
    if (calcDropdown && !calcDropdown.contains(event.target)) {
        closeCalculatorsDropdown()
    }
}

function onScroll() {
    scrolled.value = window.scrollY > 8
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    window.addEventListener('scroll', onScroll, { passive: true })
    onScroll()
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
    window.removeEventListener('scroll', onScroll)
})
</script>

<style scoped>
/* animated underline for nav links */
.nav-link {
    position: relative;
    padding: 0.25rem 0;
}

.nav-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 100%;
    height: 2px;
    border-radius: 9999px;
    background: #FF7010;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.nav-link:hover::after,
.nav-link.is-active::after {
    transform: scaleX(1);
}
</style>

<template>
    <Notification :flash="props.flash" />
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md transition-shadow duration-300"
        :class="scrolled ? 'shadow-lg shadow-orange-900/5' : 'shadow-sm'">
        <!-- Top Bar -->
        <div
            class="flex flex-col md:flex-row md:justify-between md:items-center px-4 md:px-6 py-2 text-sm bg-gradient-to-r from-[#fff4ea] via-gray-100 to-[#fff4ea]">
            <!-- Logo -->
            <div class="flex justify-between items-center">
                <Link :href="route('index')" class="block">
                    <img src="/images/logo.png" alt="MyAstroSathi"
                        class="h-12 w-auto transition-transform duration-300 hover:scale-105" />
                </Link>
                <!-- Mobile menu toggle -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle menu"
                    class="md:hidden rounded-lg p-1 text-gray-700 transition hover:bg-orange-100 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" width="28" height="28" />
                </button>
            </div>

            <!-- Contact Info -->
            <div class="hidden md:flex items-center gap-6 mt-2 md:mt-0">
                <a href="tel:+919691565883"
                    class="flex items-center text-primary transition hover:text-primary-dark hover:-translate-y-0.5">
                    <Icon icon="mdi:phone" width="20" height="20" class="mr-1" /> <b>+91 96915 65883</b>
                </a>
                <a href="mailto:myastrosathi15@gmail.com"
                    class="flex items-center text-primary transition hover:text-primary-dark hover:-translate-y-0.5">
                    <Icon icon="mdi:email" width="20" height="20" class="mr-1" /> <b>myastrosathi15@gmail.com</b>
                </a>
            </div>

            <!-- Auth / Balance -->
            <div class="hidden md:block mt-2 md:mt-0">
                <template v-if="user">
                    <span class="flex items-center text-gray-700 font-semibold">
                        <Link :href="route('user.recharge')"
                            class="flex items-center mr-5 rounded-full bg-orange-100 px-3 py-1 text-primary transition hover:bg-orange-200">
                            <Icon icon="mdi:wallet" width="20" height="20" class="mr-1" /> Balance: ₹{{
                                user.wallet?.balance }}
                        </Link>
                        <div>
                            <Link href="/logout" method="post" as="button"
                                class="flex items-center text-red-400 transition hover:text-red-500">
                                <Icon icon="mdi:logout" width="20" height="20" class="mr-1" />Logout
                            </Link>
                        </div>
                    </span>
                </template>
                <template v-else>
                    <Link href="/login"
                        class="flex items-center text-gray-600 transition hover:text-primary">
                        <Icon icon="mdi:login" width="20" height="20" class="mr-1" /> Login / Register
                    </Link>
                </template>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="md:flex md:justify-between md:items-center px-4 md:px-6 py-3"
            :class="mobileMenuOpen ? 'block animate-fade-down' : 'hidden md:flex'">
            <ul class="flex flex-col gap-2 md:gap-0 md:flex-row md:space-x-6 text-gray-700 font-medium items-center">
                <li>
                    <Link :href="route('index')" class="nav-link inline-flex items-center"
                        :class="route().current('index') ? 'is-active text-primary font-bold' : 'hover:text-primary'">
                        <template v-if="route().current('index')">
                            <Icon icon="mdi:home" width="20" height="20" class="mr-1" />
                        </template>
                        Home
                    </Link>
                </li>
                <li>
                    <Link :href="route('about-us')" class="nav-link inline-flex items-center"
                        :class="route().current('about-us') ? 'is-active text-primary font-bold' : 'hover:text-primary'">
                        <template v-if="route().current('about-us')">
                            <Icon icon="mdi:information-outline" width="20" height="20" class="mr-1" />
                        </template>
                        About
                    </Link>
                </li>
                <li>
                    <Link :href="route('services')" class="nav-link inline-flex items-center"
                        :class="route().current('services') ? 'is-active text-primary font-bold' : 'hover:text-primary'">
                        <template v-if="route().current('services')">
                            <Icon icon="mdi:cards" width="20" height="20" class="mr-1" />
                        </template>
                        Services
                    </Link>
                </li>

                <!-- Horoscope Dropdown -->
                <li class="relative" id="horoscope-dropdown">
                    <button @click="showHoroscopeDropdown = !showHoroscopeDropdown" type="button"
                        class="nav-link inline-flex items-center hover:text-primary focus:outline-none"
                        :class="route().current('horoscope*') ? 'is-active text-primary font-bold' : ''">
                        Horoscope
                        <Icon icon="mdi:chevron-down" width="16" height="16" class="ml-1" />
                    </button>
                    <div v-if="showHoroscopeDropdown"
                        class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100">
                        <Link :href="route('horoscope-type', { type: 'daily' })" @click="closeHoroscopeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition">
                            Daily Horoscope
                        </Link>
                        <Link :href="route('horoscope-type', { type: 'weekly' })" @click="closeHoroscopeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition">
                            Weekly Horoscope
                        </Link>
                        <Link :href="route('horoscope-type', { type: 'monthly' })" @click="closeHoroscopeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition">
                            Monthly Horoscope
                        </Link>
                        <Link :href="route('horoscope-type', { type: 'yearly' })" @click="closeHoroscopeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition">
                            Yearly Horoscope
                        </Link>
                    </div>
                </li>

                <!-- Free Services Dropdown -->
                <li class="relative" id="free-services-dropdown">
                    <button @click="showDropdown = !showDropdown" type="button"
                        class="nav-link inline-flex items-center hover:text-primary focus:outline-none"
                        :class="route().current('tools.kundli') || route().current('tools.matchmaking') || route().current('tools.panchang') || route().current('tools.compatibility') || route().current('tools.tarot') || route().current('tools.baby-names') ? 'is-active text-primary font-bold' : ''">
                        Free Services
                        <Icon icon="mdi:chevron-down" width="16" height="16" class="ml-1" />
                    </button>
                    <div v-if="showDropdown"
                        class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100">
                        <Link :href="route('tools.kundli')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.kundli') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Free Kundli
                        </Link>
                        <Link :href="route('tools.matchmaking')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.matchmaking') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Kundli Matching
                        </Link>
                        <Link :href="route('tools.panchang')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.panchang') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Panchang
                        </Link>
                        <Link :href="route('tools.compatibility')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.compatibility') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Zodiac Compatibility
                        </Link>
                        <Link :href="route('tools.tarot')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.tarot') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Tarot Reading
                        </Link>
                        <Link :href="route('tools.baby-names')" @click="closeDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.baby-names') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Baby Name Generator
                        </Link>
                    </div>
                </li>

                <!-- Calculators Dropdown -->
                <li class="relative" id="calculators-dropdown">
                    <button @click="showCalculatorsDropdown = !showCalculatorsDropdown" type="button"
                        class="nav-link inline-flex items-center hover:text-primary focus:outline-none"
                        :class="route().current('tools.numerology') || route().current('tools.love-calculator') || route().current('tools.sun-sign') || route().current('tools.rising-sign') || route().current('tools.rashi') || route().current('tools.karakas') || route().current('tools.dasha') || route().current('tools.nakshatra') || route().current('tools.manglik') ? 'is-active text-primary font-bold' : ''">
                        Calculators
                        <Icon icon="mdi:chevron-down" width="16" height="16" class="ml-1" />
                    </button>
                    <div v-if="showCalculatorsDropdown"
                        class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100">
                        <Link :href="route('tools.numerology')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.numerology') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Numerology Calculator
                        </Link>
                        <Link :href="route('tools.love-calculator')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.love-calculator') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Love Calculator
                        </Link>
                        <Link :href="route('tools.sun-sign')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.sun-sign') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Sun Sign Calculator
                        </Link>
                        <Link :href="route('tools.rising-sign')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.rising-sign') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Rising Sign Calculator
                        </Link>
                        <Link :href="route('tools.rashi')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.rashi') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Rashi Calculator
                        </Link>
                        <Link :href="route('tools.karakas')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.karakas') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Atmakaraka &amp; Darakaraka
                        </Link>
                        <Link :href="route('tools.dasha')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.dasha') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Vimshottari Dasha
                        </Link>
                        <Link :href="route('tools.nakshatra')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.nakshatra') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Nakshatra Calculator
                        </Link>
                        <Link :href="route('tools.manglik')" @click="closeCalculatorsDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary transition"
                            :class="route().current('tools.manglik') ? 'font-bold text-orange-600 bg-orange-50' : ''">
                            Manglik Dosha Calculator
                        </Link>
                    </div>
                </li>

                <li>
                    <Link href="/contact" class="nav-link inline-flex items-center hover:text-primary">Contact Us</Link>
                </li>
                <li v-if="role === 'Astrologer'">
                    <Link href="/astrologer/dashboard" class="nav-link inline-flex items-center hover:text-primary">Dashboard</Link>
                </li>
                <li v-if="role === 'Admin'">
                    <Link href="/dashboard" class="nav-link inline-flex items-center hover:text-primary">Dashboard</Link>
                </li>
                <li v-if="role === 'User'">
                    <Link href="/user/profile" class="nav-link inline-flex items-center hover:text-primary">Profile</Link>
                </li>
                <li v-if="role === 'User'">
                    <Link href="/user/chat-sessions" class="nav-link inline-flex items-center hover:text-primary">Chat Sessions</Link>
                </li>
                <li v-if="role === 'User'">
                    <Link href="/user/call-history" class="nav-link inline-flex items-center hover:text-primary">Call History</Link>
                </li>
            </ul>

            <!-- Action Buttons -->
            <div v-if="!user || role === 'User'">
                <div class="flex flex-col md:flex-row md:space-x-3 mt-4 md:mt-0">
                    <Link :href="route('user.chat-with-astrologers')"
                        class="group flex items-center justify-center px-4 py-2 rounded-lg bg-red-600 text-white shadow-md shadow-red-600/20 transition duration-300 hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 mb-2 md:mb-0">
                        <Icon icon="mdi:wechat" width="20" height="20"
                            class="mr-1 transition-transform duration-300 group-hover:scale-110" /> Chat With Astrologer
                    </Link>
                    <Link :href="route('user.talk-to-astrologers')"
                        class="group flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 text-white shadow-md shadow-green-600/20 transition duration-300 hover:-translate-y-0.5 hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/30">
                        <Icon icon="mdi:phone-in-talk-outline" width="20" height="20"
                            class="mr-1 transition-transform duration-300 group-hover:scale-110" /> Talk To
                        Astrologer
                    </Link>
                </div>
            </div>

            <!-- Auth / Balance (mobile) -->
            <div class="md:hidden mt-3">
                <template v-if="role === 'User'">
                    <span class="flex items-center text-gray-700 font-semibold justify-between">
                        <Link :href="route('user.recharge')"
                            class="flex items-center mr-5 rounded-full bg-orange-100 px-3 py-1 text-primary">
                            <Icon icon="mdi:wallet" width="20" height="20" class="mr-1" /> Balance: ₹{{
                                user.wallet?.balance }}
                        </Link>
                        <div>
                            <Link href="/logout" method="post" as="button"
                                class="flex items-center text-red-400 hover:text-red-500">
                                <Icon icon="mdi:logout" width="20" height="20" class="mr-1" />Logout
                            </Link>
                        </div>
                    </span>
                </template>
                <template v-else>
                    <Link href="/login" class="text-gray-600 hover:text-primary flex items-center">
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
