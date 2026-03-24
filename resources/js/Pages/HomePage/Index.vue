<script setup>
import { Head, Link } from '@inertiajs/vue3';
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
import { onMounted } from 'vue';

const props = defineProps({
    user: Object,
    blogs: Object,
    astrologers: Object,
})

const beamsClient = new PusherPushNotifications.Client({
    instanceId: import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID,
});

// beamsClient.start()
//     .then(() => beamsClient.addDeviceInterest('NewsLetter'))
//     .then(() => console.log('Successfully registered and subscribed!'))
//     .catch(console.error);

const beamsTokenProvider = new PusherPushNotifications.TokenProvider({
    url: '/beams-auth', // your Laravel route
});

onMounted(async () => {
    if (props.user?.id) {
        try {
            await beamsClient.start();
            await beamsClient.setUserId(String(props.user.id), beamsTokenProvider);
        } catch (err) {
            console.error('Beams setup failed', err);
        }
    }
});
</script>

<template>

    <Head title="Home" />
    <Header :user="user" />

    <Banner />

    <!-- Astrologers Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6 p-5 md:p-16">
        <div v-for="astrologer in props.astrologers" :key="astrologer.id"
            class="bg-white rounded-lg shadow hover:shadow-lg transition flex items-center p-4">

            <!-- Astrologer image -->
            <div class="flex-shrink-0">
                <img :src="`/${astrologer.profile_image}`" alt="Astrologer image"
                    class="w-24 h-24 object-cover rounded-full border-2 border-orange-500 transform transition-transform duration-300 hover:scale-105" />
            </div>

            <!-- Astrologer content -->
            <div class="flex-1 ml-4 flex flex-col justify-between h-full">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ astrologer.user.name }}
                    </h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <span>Exp: {{ astrologer.experience_years }} years</span>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ Array.isArray(astrologer.expertise)
                            ? astrologer.expertise.join(', ')
                            : (astrologer.expertise ?? 'Astrologer') }}
                    </p>
                </div>

                <!-- Chat Now button -->
                <div class="mt-3 flex justify-between items-center">
                    <p class="font-bold">₹ {{ astrologer.charged_text_price }} / min</p>
                    <Link :href="route('user.chat.start', astrologer.id)"
                        class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                        Chat Now
                    </Link>
                </div>
            </div>
        </div>

    </div>

    <About />

    <Service />

    <ChooseZodiac />

    <KnowZodiac />

    <Testimonials />

    <WhyChooseUs />

    <Blogs :blogs="blogs" />

    <Footer />

</template>