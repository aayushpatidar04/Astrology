<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Services from '../HomePage/Service.vue';
import Testimonials from '../HomePage/Testimonials.vue';
import WhyChooseUs from '../HomePage/WhyChooseUs.vue';
import Footer from '../Layouts/Footer.vue';

const props = defineProps({
    user: Object,
})
</script>

<template>
    <Head title="About Us" />
    <Header :user="user" />

    <Services />

    <WhyChooseUs />
    
    <Testimonials />

    <Footer />
    
</template>