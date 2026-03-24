<script setup>
import { Link, Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    astrologers: Object,
})

// store wallet balance (fetched from backend)
const userWalletBalance = ref(props.user.wallet?.balance ?? 0);
const showRechargeModal = ref(false);
const selectedAstrologer = ref(null);

function checkWalletAndStartChat(astrologer) {
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

    <Head title="Chat with Astrologers" />

    <Header :user="user" />

    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Astrologers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="astrologer in props.astrologers.data" :key="astrologer.id"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition">

                    <!-- Astrologer image -->
                    <img :src="`/${astrologer.profile_image}`" alt="Astrologer image"
                        class="h-48 w-full object-cover rounded-t-lg transform transition-transform duration-300 hover:scale-105" />

                    <!-- Astrologer content -->
                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ astrologer.user.name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ Array.isArray(astrologer.expertise)
                                ? astrologer.expertise.join(', ')
                                : (astrologer.expertise ?? 'Astrologer') }}
                        </p>
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <span>Years of Experience: {{ astrologer.experience_years }} years</span>
                        </div>
                        <!-- Chat Now button -->
                        <!-- <Link :href="route('user.chat.start', astrologer.id)"
                            class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                            Chat Now
                        </Link> -->
                        <button
                            @click.prevent="checkWalletAndStartChat(astrologer)"
                            class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                            Chat Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.astrologers.data.length > 0" class="mt-8 flex justify-center">
                <Pagination :links="props.astrologers.links" />
            </div>
        </div>
    </section>

    <!-- Recharge Modal -->
    <div v-if="showRechargeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full">
            <p class="text-gray-800">
                Minimum balance of 4 minutes ₹{{ selectedAstrologer.charged_text_price * 4 }}
                is required to start chat with {{ selectedAstrologer.user.name }}
            </p>
            <div class="mt-4 flex justify-between space-x-2">
                <button @click="showRechargeModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <Link href="#" class="px-4 py-2 bg-green-500 text-white rounded">
                    Recharge Now
                </Link>
            </div>
        </div>
    </div>

    <Footer />
</template>