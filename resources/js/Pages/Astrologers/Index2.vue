<script setup>
import { Link, Head, usePage, router } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed, watch } from 'vue';
import echo from '@/echo'

const props = defineProps({
    user: Object,
    astrologers: Object,
    filters: Object,
})

const page = usePage()
const flash = page.props.flash

const show = ref(false)
const message = ref('')
const type = ref('success')

// Filter state
const search = ref(props.filters?.search ?? '')
const specialization = ref(props.filters?.specialization ?? '')
const onlineFilter = ref(props.filters?.online ?? '')

const specializations = ['Vedic', 'Tarot', 'Vastu', 'Numerology', 'Astrology', 'Horoscope']

function applyFilters() {
    router.get(route('user.talk-to-astrologers'), {
        search: search.value,
        specialization: specialization.value,
        online: onlineFilter.value,
    }, {
        preserveState: true,
        replace: true,
    })
}

function selectSpecialization(spec) {
    specialization.value = specialization.value === spec ? '' : spec
    applyFilters()
}

function resetFilters() {
    search.value = ''
    specialization.value = ''
    onlineFilter.value = ''
    router.get(route('user.talk-to-astrologers'), {}, {
        preserveState: true,
        replace: true,
    })
}

// Review modal state
const showReviewModal = ref(false)
const reviewAstrologer = ref(null)
const rating = ref(5)
const reviewText = ref('')

function openReviewModal(astrologer) {
    reviewAstrologer.value = astrologer
    rating.value = 5
    reviewText.value = ''
    showReviewModal.value = true
}

function submitReview() {
    if (!reviewAstrologer.value) return
    router.post(`/user/astrologers/${reviewAstrologer.value.id}/reviews`, {
        rating: rating.value,
        review: reviewText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReviewModal.value = false
        }
    })
}

// Watch for flash messages
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

const astrologersList = computed(() => props.astrologers.data)
const userWalletBalance = ref(props.user.wallet?.balance ?? 0);
const showRechargeModal = ref(false);
const selectedAstrologer = ref(null);

function checkWalletAndStartCall(astrologer) {
    const requiredBalance = astrologer.charged_call_price * 4;
    if (userWalletBalance.value >= requiredBalance) {
        window.location.href = route('user.call.show', astrologer.id);
    } else {
        selectedAstrologer.value = astrologer;
        showRechargeModal.value = true;
    }
}
</script>

<template>
    <Head title="Talk to Astrologers" />

    <Header :user="user" :flash="flash" />

    <section class="py-10 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Title Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Talk to Expert Astrologers</h1>
                <p class="mt-2 text-sm text-gray-600">Connect via voice call with certified Vedic, Tarot, and Vastu specialists.</p>
            </div>

            <!-- Flash Message -->
            <div v-if="show" class="mb-6 p-4 rounded-lg shadow text-white font-medium" :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'">
                {{ message }}
            </div>

            <!-- Modern Filter Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">

                    <!-- Search input -->
                    <div class="md:col-span-5 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            🔍
                        </span>
                        <input type="text" v-model="search" @keyup.enter="applyFilters"
                            placeholder="Search by name, expertise or bio..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm" />
                    </div>

                    <!-- Online status filter -->
                    <div class="md:col-span-3">
                        <select v-model="onlineFilter" @change="applyFilters"
                            class="w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm bg-white">
                            <option value="">All Statuses</option>
                            <option value="1">🟢 Online Only</option>
                        </select>
                    </div>

                    <!-- Action buttons -->
                    <div class="md:col-span-4 flex gap-2 justify-end">
                        <button @click="applyFilters"
                            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2.5 px-4 rounded-lg shadow transition text-sm">
                            Search & Filter
                        </button>
                        <button @click="resetFilters"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-lg transition text-sm">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Quick Specialization Chips -->
                <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 mr-2 uppercase tracking-wide">Specialities:</span>
                    <button v-for="spec in specializations" :key="spec"
                        @click="selectSpecialization(spec)"
                        class="px-3 py-1 rounded-full text-xs font-medium transition"
                        :class="specialization === spec ? 'bg-orange-600 text-white shadow' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        {{ spec }}
                    </button>
                </div>
            </div>

            <!-- Astrologers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="astrologer in astrologersList" :key="astrologer.id"
                    class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between p-5 border border-gray-100 relative overflow-hidden">

                    <div class="flex items-start gap-4">
                        <!-- Avatar & Status -->
                        <div class="relative flex-shrink-0">
                            <img :src="`/${astrologer.profile_image}`" alt="Astrologer"
                                class="w-20 h-20 object-cover rounded-full border-2 border-orange-500 shadow-sm" />
                            <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-white"
                                :class="!astrologer.online ? 'bg-gray-400' : astrologer.user?.is_busy ? 'bg-red-500' : 'bg-green-500'"
                                :title="!astrologer.online ? 'Offline' : astrologer.user?.is_busy ? 'Busy' : 'Online'">
                            </span>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-gray-900 truncate">
                                    {{ astrologer.user.name }}
                                </h3>
                            </div>

                            <!-- Rating & Reviews -->
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-500 my-1">
                                <span>⭐ {{ astrologer.average_rating ?? '0.0' }}</span>
                                <span class="text-gray-400 font-normal">({{ astrologer.reviews_count ?? 0 }} reviews)</span>
                            </div>

                            <p class="text-xs text-gray-500 font-medium">Exp: {{ astrologer.experience_years }} Years</p>

                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                {{ Array.isArray(astrologer.expertise) ? astrologer.expertise.join(', ') : (astrologer.expertise ?? 'Astrologer') }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer Pricing & CTA -->
                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-gray-400 uppercase tracking-wider block">Call Rate</span>
                            <span class="text-base font-extrabold text-gray-900">₹{{ astrologer.charged_call_price }} <span class="text-xs font-normal text-gray-500">/min</span></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="openReviewModal(astrologer)"
                                class="text-xs bg-amber-50 text-amber-700 font-medium px-3 py-2 rounded-lg hover:bg-amber-100 transition border border-amber-200">
                                Rate
                            </button>
                            <button @click.prevent="checkWalletAndStartCall(astrologer)"
                                :disabled="!astrologer.online || astrologer.user?.is_busy"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed">
                                {{ !astrologer.online ? 'Offline' : astrologer.user?.is_busy ? 'Busy' : 'Call Now' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="astrologersList.length === 0" class="bg-white rounded-xl shadow-md p-12 text-center mt-6">
                <p class="text-gray-500 text-lg">No astrologers found matching your filters.</p>
                <button @click="resetFilters" class="mt-4 bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Clear Filters
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="astrologersList.length > 0" class="mt-8 flex justify-center">
                <Pagination :links="props.astrologers.links" />
            </div>
        </div>
    </section>

    <!-- Recharge Modal -->
    <div v-if="showRechargeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Insufficient Balance</h3>
            <p class="text-sm text-gray-600 mb-4">
                Minimum balance of 4 minutes (₹{{ selectedAstrologer?.charged_call_price * 4 }}) is required to start a call with {{ selectedAstrologer?.user.name }}.
            </p>
            <div class="flex justify-end space-x-2">
                <button @click="showRechargeModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Cancel</button>
                <Link :href="route('user.recharge')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium shadow">
                    Recharge Now
                </Link>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Rate & Review {{ reviewAstrologer?.user.name }}</h3>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Rating</label>
                <select v-model="rating" class="border border-gray-300 rounded-lg w-full px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                    <option :value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                    <option :value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                    <option :value="3">⭐⭐⭐ (3 - Good)</option>
                    <option :value="2">⭐⭐ (2 - Fair)</option>
                    <option :value="1">⭐ (1 - Poor)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Your Review</label>
                <textarea v-model="reviewText" rows="3" placeholder="Share details of your consultation..."
                    class="border border-gray-300 rounded-lg w-full px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2">
                <button @click="showReviewModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Cancel</button>
                <button @click="submitReview" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium shadow">
                    Submit Review
                </button>
            </div>
        </div>
    </div>

    <Footer />
</template>
