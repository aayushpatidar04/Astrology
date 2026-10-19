<script setup>
import { Link } from "@inertiajs/vue3"
import { Icon } from "@iconify/vue"
import Reveal from '@/Components/Reveal.vue'

const year = new Date().getFullYear()

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
    <footer class="relative bg-gradient-to-b from-[#F0D09A] to-[#E9C27F] pt-14 pb-6 px-6 md:px-12 overflow-hidden">
        <!-- soft decorative glows -->
        <div class="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-orange-300/30 blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Logo + Brand -->
            <Reveal>
                <img src="/images/logo.png" alt="MyAstroSathi" class="h-12 mb-4" />
                <p class="text-sm leading-relaxed text-black/90">
                    MyAstroSathi blends ancient wisdom with modern insight to guide your journey through love,
                    career, health, and destiny. Trusted by millions across India and beyond.
                </p>
            </Reveal>

            <!-- Quick Links -->
            <Reveal :delay="100">
                <h3 class="text-lg font-semibold text-primary mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-black">
                    <li>
                        <Link href="/" class="footer-link">Home</Link>
                    </li>
                    <li>
                        <Link href="/about-us" class="footer-link">About Us</Link>
                    </li>
                    <li>
                        <Link href="/services" class="footer-link">Services</Link>
                    </li>
                    <!-- <li>
                        <Link href="/blog" class="footer-link">Blog</Link>
                    </li> -->
                    <li>
                        <Link href="/contact" class="footer-link">Contact</Link>
                    </li>
                </ul>
            </Reveal>

            <!-- Legal -->
            <Reveal :delay="200">
                <h3 class="text-lg font-semibold text-primary mb-4">Legal</h3>
                <ul class="space-y-2 text-sm text-black">
                    <li>
                        <Link href="/terms-conditions" class="footer-link">Terms & Conditions</Link>
                    </li>
                    <li>
                        <Link href="/privacy-policy" class="footer-link">Privacy Policy</Link>
                    </li>
                    <li>
                        <Link href="/refund-policy" class="footer-link">Refund Policy</Link>
                    </li>
                </ul>
            </Reveal>

            <!-- Contact Info -->
            <Reveal :delay="300">
                <h3 class="text-lg font-semibold text-primary mb-4">Contact Us</h3>
                <p class="text-sm mb-2 flex items-center text-black">
                    <Icon icon="mdi:map-marker" class="text-primary mr-1 shrink-0" /> Indore - 452001, Madhya Pradesh,
                    India
                </p>
                <a href="tel:+919691565883" class="footer-link text-sm mb-2 flex items-center text-black">
                    <Icon icon="mdi:phone" class="text-primary mr-1 shrink-0" /> +91 96915 65883
                </a>
                <a href="mailto:myastrosathi15@gmail.com" class="footer-link text-sm mb-4 flex items-center text-black">
                    <Icon icon="mdi:email" class="text-primary mr-1 shrink-0" /> myastrosathi15@gmail.com
                </a>

                <!-- Social Icons -->
                <div class="flex space-x-3">
                    <a v-for="s in ['facebook', 'instagram', 'twitter', 'youtube']" :key="s" href="#" :aria-label="s"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/50 text-black transition duration-300 hover:-translate-y-1 hover:bg-primary hover:text-white hover:shadow-lg hover:shadow-orange-500/40">
                        <Icon :icon="`mdi:${s}`" width="20" />
                    </a>
                </div>
            </Reveal>
        </div>

        <!-- Bottom Bar -->
        <div
            class="relative max-w-7xl mx-auto border-t border-black/15 mt-10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-black">
            <span>&copy; {{ year }} MyAstroSathi. All rights reserved. Designed with cosmic care.</span>
            <button @click="scrollToTop" aria-label="Back to top"
                class="flex items-center gap-1 rounded-full bg-white/50 px-3 py-1 font-medium transition duration-300 hover:-translate-y-0.5 hover:bg-primary hover:text-white">
                <Icon icon="mdi:arrow-up" width="16" /> Back to top
            </button>
        </div>
    </footer>
</template>


<style scoped>
.footer-link {
    transition: color 0.2s ease, transform 0.2s ease;
}

.footer-link:hover {
    color: #FF7010;
    transform: translateX(4px);
}

a.footer-link {
    display: flex;
}
</style>