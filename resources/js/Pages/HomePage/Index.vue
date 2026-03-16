<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Banner from './Banner.vue';
import About from './About.vue';
import Service from './Service.vue';
import ChooseZodiac from './ChooseZodiac.vue';
import KnowZodiac from './KnowZodiac.vue';
import Testimonials from './Testimonials.vue';
import WhyChooseUs from './WhyChooseUs.vue';
import Footer from '../Layouts/Footer.vue';
import Blogs from './Blogs.vue';
import * as PusherPushNotifications from "@pusher/push-notifications-web";

const props = defineProps({
    user: Object,
    blogs: Object
})

const beamsClient = new PusherPushNotifications.Client({
    instanceId: import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID,
});

beamsClient.start()
    .then(() => beamsClient.addDeviceInterest('NewsLetter'))
    .then(() => console.log('Successfully registered and subscribed!'))
    .catch(console.error);
</script>

<template>

    <Head title="Home" />
    <Header :user="user" />

    <Banner />

    <About />

    <Service />

    <ChooseZodiac />

    <KnowZodiac />

    <Testimonials />

    <WhyChooseUs />

    <Blogs :blogs="blogs" />

    <Footer />

</template>