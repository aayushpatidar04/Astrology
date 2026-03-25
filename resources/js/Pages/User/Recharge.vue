<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue'

const props = defineProps({
    firstTimeOffers: Object,
    regularOffers: Object,
    specialOffers: Object,
    walletBalance: Number
})

const selectedPack = ref(null)
const couponCode = ref('')
const couponError = ref(null)

function selectPack(pack) {
    selectedPack.value = pack
    couponError.value = null
}

function applyCoupon() {
    if (couponCode.value === 'FIRST50') {
        couponError.value = null
        // Example: apply discount logic here
    } else {
        couponError.value = `Invalid Coupon code ${couponCode.value}`
    }
}

const gst = computed(() => selectedPack.value ? (selectedPack.value.amount * 0.18).toFixed(2) : '0.00')
const totalPayable = computed(() => selectedPack.value ? (selectedPack.value.amount * 1.18).toFixed(2) : '0.00')
</script>

<template>

    <Head title="Recharge" />
    <UserLayout>
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Left side: Offers -->
            <div class="md:col-span-2 space-y-8">

                <div class="flex justify-between">
                    <!-- Wallet Balance -->
                    <div class="mb-4">
                        <p class="text-2xl font-bold">Add Money to Wallet</p>
                        <p class="text-gray-600">Choose from the available recharge packs</p>
                    </div>
                    <div class="mb-4 text-end">
                        <p class="text-gray-600">Available Balance</p>
                        <p class="text-2xl font-bold">₹{{ walletBalance }}</p>
                    </div>
                </div>

                <!-- First Time Offers -->
                <section v-if="firstTimeOffers && firstTimeOffers.length">
                    <h2 class="text-lg font-semibold mb-4">First Time Offers</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="pack in firstTimeOffers" :key="pack.id" @click="selectPack(pack)"
                            class="cursor-pointer border rounded-lg p-4 text-center hover:shadow-lg transition"
                            :class="selectedPack?.id === pack.id ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                            <p class="text-xl font-bold">₹{{ pack.amount }}</p>
                            <p v-if="pack.label" class="text-sm text-orange-600 mt-1">{{ pack.label }}</p>
                            <span v-if="pack.recommended"
                                class="inline-block mt-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">
                                Recommended
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Regular Offers -->
                <section v-if="regularOffers && regularOffers.length">
                    <h2 class="text-lg font-semibold mb-4">Recharge Options</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="pack in regularOffers" :key="pack.id" @click="selectPack(pack)"
                            class="cursor-pointer border rounded-lg p-4 text-center hover:shadow-lg transition"
                            :class="selectedPack?.id === pack.id ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                            <p class="text-xl font-bold">₹{{ pack.amount }}</p>
                            <p v-if="pack.label" class="text-sm text-orange-600 mt-1">{{ pack.label }}</p>
                            <span v-if="pack.recommended"
                                class="inline-block mt-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">
                                Recommended
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Special Offers -->
                <section v-if="specialOffers && specialOffers.length">
                    <h2 class="text-lg font-semibold mb-4">Special Offers</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="pack in specialOffers" :key="pack.id" @click="selectPack(pack)"
                            class="cursor-pointer border rounded-lg p-4 text-center hover:shadow-lg transition"
                            :class="selectedPack?.id === pack.id ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                            <p class="text-xl font-bold">₹{{ pack.amount }}</p>
                            <p v-if="pack.label" class="text-sm text-orange-600 mt-1">{{ pack.label }}</p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Right side: Payment Summary -->
            <div class="border rounded-lg p-6 bg-white shadow">
                <h3 class="text-lg font-semibold mb-4">Payment Details</h3>
                <p>Total Amount: ₹{{ selectedPack?.amount ?? 0 }}</p>
                <p>GST @ 18%: ₹{{ gst }}</p>
                <p class="font-bold mt-2">Total Payable: ₹{{ totalPayable }}</p>

                <p v-if="selectedPack?.bonus_amount" class="mt-4 text-green-600 font-medium">
                    You will get ₹{{ selectedPack.bonus_amount + selectedPack.amount }} in your wallet
                </p>

                <!-- Coupon -->
                <div class="mt-4 flex gap-2 items-center">
                    <input v-model="couponCode" type="text" placeholder="Enter coupon code"
                        class="w-full border rounded" />
                    <button @click="applyCoupon"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                        Apply
                    </button>
                </div>
                <p v-if="couponError" class="text-red-600 text-sm mt-1">{{ couponError }}</p>

                <!-- Proceed Button -->
                <button
                    class="w-full mt-6 bg-green-500 text-white py-2 rounded hover:bg-green-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
                    :disabled="!selectedPack">
                    PROCEED NOW
                </button>

                <!-- Trust Badges -->
                <div class="mt-6 text-sm text-gray-500 space-y-1">
                    <p>✔ Money Back Guarantee</p>
                    <p>✔ Verified Expert Astrologers</p>
                    <p>✔ 100% Secure Payment</p>
                </div>
            </div>
        </div>

    </UserLayout>
</template>
