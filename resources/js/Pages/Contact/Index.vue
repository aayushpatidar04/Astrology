<template>
    <Head title="Contact Us" />
    <Header :user="user" />
    <div class="max-w-3xl mx-auto px-6 py-12">
        <h1 class="text-3xl font-bold text-orange-600 mb-6">Contact Us</h1>

        <div class="space-y-4 text-gray-700">
            <p>
                <strong>Phone:</strong>
                <a href="tel:9691565883" class="text-orange-500 hover:underline">
                    9691565883
                </a>
            </p>

            <p>
                <strong>Email:</strong>
                <a href="mailto:myastrosathi15@gmail.com" class="text-orange-500 hover:underline">
                    myastrosathi15@gmail.com
                </a>
            </p>

            <p>
                <strong>Address:</strong>
                12 EXT Lokmanya Nagar, Indore
            </p>
        </div>
    </div>

    <Footer />
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
})
</script>

<style scoped>
/* Optional styling tweaks */
</style>
