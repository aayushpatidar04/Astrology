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
import Reveal from '@/Components/Reveal.vue';
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
    <section class="px-5 py-14 md:px-16">
        <Reveal class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="section-heading section-heading-accent">Our Astrologers</h2>
        </Reveal>

        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
            <Reveal v-for="(astrologer, i) in astrologers" :key="astrologer.id" :delay="(i % 3) * 100" class="h-full">
                <div
                    class="group flex h-full items-center rounded-2xl border border-orange-100 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-orange-900/10">

                    <!-- Astrologer image -->
                    <div class="flex-shrink-0 text-center">
                        <div class="rounded-full bg-gradient-to-br from-primary to-amber-300 p-[3px]">
                            <img :src="`/${astrologer.profile_image}`" alt="Astrologer image"
                                class="h-24 w-24 rounded-full border-2 border-white object-cover transition-transform duration-500 group-hover:scale-105" />
                        </div>
                        <span v-if="astrologer.is_busy"
                            class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-red-100 px-4 py-1 text-xs font-semibold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            Busy
                        </span>
                        <span v-else
                            class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-green-100 px-4 py-1 text-xs font-semibold text-green-700">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-green-500 animate-ping-soft"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-green-500"></span>
                            </span>
                            Available
                        </span>
                    </div>

                    <!-- Astrologer content -->
                    <div class="ml-4 flex h-full flex-1 flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 transition-colors group-hover:text-primary">
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
                        <div class="mt-3 flex items-center justify-between">
                            <p class="font-bold text-gray-800">₹ {{ astrologer.charged_text_price }} / min</p>
                            <button @click.prevent="checkWalletAndStartChat(astrologer)" :disabled="astrologer.is_busy"
                                class="inline-block rounded-lg bg-green-500 px-4 py-2 text-white shadow-md shadow-green-500/20 transition duration-300 hover:-translate-y-0.5 hover:bg-green-600 hover:shadow-lg hover:shadow-green-500/30 active:translate-y-0 disabled:translate-y-0 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none">
                                Chat Now
                            </button>
                        </div>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>

    <!-- Recharge Modal -->
    <Transition name="modal">
        <div v-if="showRechargeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="showRechargeModal = false">
            <div class="modal-card w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <p class="text-gray-800">
                    Minimum balance of 4 minutes ₹{{ selectedAstrologer.charged_text_price * 4 }}
                    is required to start chat with {{ selectedAstrologer.user.name }}
                </p>
                <div class="mt-5 flex justify-between space-x-2">
                    <button @click="showRechargeModal = false"
                        class="rounded-lg bg-gray-200 px-4 py-2 transition duration-300 hover:bg-gray-300">Cancel</button>
                    <Link :href="route('user.recharge')"
                        class="rounded-lg bg-green-500 px-4 py-2 text-white shadow-md shadow-green-500/20 transition duration-300 hover:-translate-y-0.5 hover:bg-green-600">
                        Recharge Now
                    </Link>
                </div>
            </div>
        </div>
    </Transition>

    <About />

    <Service />

    <ChooseZodiac />

    <KnowZodiac />

    <Testimonials />

    <WhyChooseUs />

    <!-- <Blogs :blogs="blogs" /> -->

    <Footer />

</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .modal-card,
.modal-leave-active .modal-card {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.modal-enter-from .modal-card,
.modal-leave-to .modal-card {
    transform: scale(0.92) translateY(12px);
}
</style>