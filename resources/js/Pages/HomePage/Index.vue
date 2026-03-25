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
import { ref, onMounted } from 'vue';
import echo from '@/echo';

const props = defineProps({
    user: Object,
    blogs: Object,
    astrologers: Object,
})

const astrologers = ref(props.astrologers)

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
            await beamsClient.clearAllState();
            await beamsClient.start();
            await beamsClient.setUserId(String(props.user.id), beamsTokenProvider);
        } catch (err) {
            console.error('Beams setup failed', err);
        }
    }

    echo.channel('astrologers')
        .listen('AstrologerStatusUpdated', (e) => {
            const idx = astrologers.value.findIndex(a => a.id === e.astrologerId)
            if (idx !== -1) {
                astrologers.value[idx].is_busy = e.isBusy
            } else {
                console.warn(`Astrologer ${e.astrologerId} not found in list`)
            }
        })
});

const userWalletBalance = ref(props.user?.wallet?.balance ?? 0);
const showRechargeModal = ref(false);
const selectedAstrologer = ref(null);

function checkWalletAndStartChat(astrologer) {

    if (!props.user || !props.user.id) {
        window.location.href = route('login');
        return;
    }

    const requiredBalance = astrologer.charged_text_price * 4;

    if (userWalletBalance.value >= requiredBalance) {
        // ✅ Enough balance → redirect to chat
        window.location.href = route('user.chat.start', astrologer.id);
    } else {
        // ❌ Not enough balance → show modal
        selectedAstrologer.value = astrologer;
        showRechargeModal.value = true;
    }
}
</script>

<template>

    <Head title="Home" />
    <Header :user="user" />

    <Banner />

    <!-- Astrologers Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6 p-5 md:p-16">
        <div v-for="astrologer in astrologers" :key="astrologer.id"
            class="bg-white rounded-lg shadow hover:shadow-lg transition flex items-center p-4">

            <!-- Astrologer image -->
            <div class="flex-shrink-0 text-center">
                <img :src="`/${astrologer.profile_image}`" alt="Astrologer image"
                    class="w-24 h-24 object-cover rounded-full border-2 border-orange-500 transform transition-transform duration-300 hover:scale-105" />
                <span v-if="astrologer.is_busy"
                    class="inline-flex items-center px-5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 mt-1">
                    Busy
                </span>
                <span v-else
                    class="inline-flex items-center px-5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 mt-1">
                    Available
                </span>
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
                    <button @click.prevent="checkWalletAndStartChat(astrologer)" :disabled="astrologer.is_busy"
                        class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed">
                        Chat Now
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Recharge Modal -->
    <div v-if="showRechargeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full">
            <p class="text-gray-800">
                Minimum balance of 4 minutes ₹{{ selectedAstrologer.charged_text_price * 4 }}
                is required to start chat with {{ selectedAstrologer.user.name }}
            </p>
            <div class="mt-4 flex justify-between space-x-2">
                <button @click="showRechargeModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <Link :href="route('user.recharge')" class="px-4 py-2 bg-green-500 text-white rounded">
                    Recharge Now
                </Link>
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