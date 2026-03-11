<script setup>
import { Link } from "@inertiajs/vue3"
import { Icon } from "@iconify/vue"
</script>

<template>
    <footer class="bg-[#F0D09A] text-gray-300 pt-12 pb-6 px-6 md:px-12">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Logo + Brand -->
            <div>
                <img src="/images/logo.png" alt="MyAstroSathi" class="h-12 mb-4" />
                <p class="text-sm leading-relaxed text-black">
                    MyAstroSathi blends ancient wisdom with modern insight to guide your journey through love,
                    career, health, and destiny. Trusted by millions across India and beyond.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold text-primary mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-black">
                    <li>
                        <Link href="/" class="hover:text-orange-500">Home</Link>
                    </li>
                    <li>
                        <Link href="/about-us" class="hover:text-orange-500">About Us</Link>
                    </li>
                    <li>
                        <Link href="/services" class="hover:text-orange-500">Services</Link>
                    </li>
                    <li>
                        <Link href="/blog" class="hover:text-orange-500">Blog</Link>
                    </li>
                    <li>
                        <Link href="/contact" class="hover:text-orange-500">Contact</Link>
                    </li>
                </ul>
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-lg font-semibold text-primary mb-4">Our Services</h3>
                <ul class="space-y-2 text-sm text-black">
                    <li>
                        <Link href="/services/vastu" class="hover:text-orange-500">Vastu Shastra</Link>
                    </li>
                    <li>
                        <Link href="/services/kundli" class="hover:text-orange-500">Kundli Dosh</Link>
                    </li>
                    <li>
                        <Link href="/services/tarot" class="hover:text-orange-500">Tarot Reading</Link>
                    </li>
                    <li>
                        <Link href="/services/palm" class="hover:text-orange-500">Palm Reading</Link>
                    </li>
                    <li>
                        <Link href="/services/crystal" class="hover:text-orange-500">Crystal Ball</Link>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-lg font-semibold text-primary mb-4">Contact Us</h3>
                <p class="text-sm mb-2  flex items-center  text-black">
                    <Icon icon="mdi:map-marker" class="text-orange-500 mr-1" /> Indore, Madhya Pradesh, India
                </p>
                <p class="text-sm mb-2  flex items-center  text-black">
                    <Icon icon="mdi:phone" class="text-orange-500 mr-1" /> +91 1800 326 3264
                </p>
                <p class="text-sm mb-4 flex items-center  text-black">
                    <Icon icon="mdi:email" class="text-orange-500 mr-1" /> myastrosathi15@gmail.com
                </p>

                <!-- Social Icons -->
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-orange-500 text-black">
                        <Icon icon="mdi:facebook" width="20" />
                    </a>
                    <a href="#" class="hover:text-orange-500 text-black">
                        <Icon icon="mdi:instagram" width="20" />
                    </a>
                    <a href="#" class="hover:text-orange-500 text-black">
                        <Icon icon="mdi:twitter" width="20" />
                    </a>
                    <a href="#" class="hover:text-orange-500 text-black">
                        <Icon icon="mdi:youtube" width="20" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-700 mt-8 pt-4 text-center text-sm text-black">
            &copy; 2026 MyAstroSathi. All rights reserved. Designed with cosmic care.
        </div>
    </footer>
</template>


<style scoped>
.text-primary {
    color: #FF7010;
}
</style>