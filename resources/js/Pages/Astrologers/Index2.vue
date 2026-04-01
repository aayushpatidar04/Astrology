<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';
import echo from '@/echo'

const props = defineProps({
    user: Object,
    astrologers: Object,
})

const page = usePage()
const flash = page.props.flash

const show = ref(false)
const message = ref('')
const type = ref('success')

// Watch for changes in flash messages
watch(
    () => [flash.success, flash.error],
    ([success, error]) => {
        if (success) {
            message.value = success
            type.value = 'success'
            show.value = true
            setTimeout(() => (show.value = false), 5000)
        } else if (error) {
            message.value = error
            type.value = 'error'
            show.value = true
            setTimeout(() => (show.value = false), 5000)
        }
    },
    { immediate: true }
)

const astrologers = ref(props.astrologers.data)

// store wallet balance (fetched from backend)
const userWalletBalance = ref(props.user.wallet?.balance ?? 0);
const showRechargeModal = ref(false);
const selectedAstrologer = ref(null);

function checkWalletAndStartChat(astrologer) {
    const requiredBalance = astrologer.charged_call_price * 4;

    if (userWalletBalance.value >= requiredBalance) {
        // ✅ Enough balance → redirect to chat
        window.location.href = route('user.call.show', astrologer.id);
    } else {
        // ❌ Not enough balance → show modal
        selectedAstrologer.value = astrologer;
        showRechargeModal.value = true;
    }
}

onMounted(() => {
    echo.channel('astrologers')
        .listen('AstrologerStatusUpdated', (e) => {
            const idx = astrologers.value.findIndex(a => a.id === e.astrologerId)
            if (idx !== -1) {
                astrologers.value[idx].is_busy = e.isBusy
            } else {
                console.warn(`Astrologer ${e.astrologerId} not found in list`)
            }
        })

})
</script>
<template>

    <Head title="Talk Astrologers" />

    <Header :user="user" :flash="flash" />

    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Astrologers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
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
                            <p class="font-bold">₹ {{ astrologer.charged_call_price }} / min</p>
                            <button @click.prevent="checkWalletAndStartChat(astrologer)" :disabled="astrologer.is_busy"
                                class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed">
                                Call Now
                            </button>
                        </div>
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
                Minimum balance of 4 minutes ₹{{ selectedAstrologer.charged_call_price * 4 }}
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

    <Footer />
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>