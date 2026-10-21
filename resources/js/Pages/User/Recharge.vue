<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue'
import { ref, computed, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    firstTimeOffers: Object,
    regularOffers: Object,
    specialOffers: Object,
    walletBalance: String
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
        shake.value = true
        setTimeout(() => (shake.value = false), 500)
    }
}

const paying = ref(false)

function proceedToPay() {
    paying.value = true
    axios.post(route('user.phonePe'), {
        package_id: selectedPack.value?.id ?? null,
        amount: Number(selectedPack.value?.amount ?? 0),
        bonus_amount: Number(selectedPack.value?.bonus_amount ?? 0),
        gst: Number(gst.value),
        total_payable: Number(totalPayable.value),
        coupon_code: couponCode.value || null,
    })
        .then(res => {
            window.location.href = res.data.redirect_url; // redirect to PhonePe
        })
        .catch(() => {
            paying.value = false
            alert('❌ Payment request failed.');
        });
}

const gst = computed(() => selectedPack.value ? (selectedPack.value.amount * 0.18).toFixed(2) : '0.00')
const totalPayable = computed(() => selectedPack.value ? (selectedPack.value.amount * 1.18).toFixed(2) : '0.00')

// ---------- presentation helpers (no behaviour change) ----------
const shake = ref(false)

const sections = computed(() => [
    { key: 'first', title: 'First Time Offers', icon: 'mdi:gift-outline', packs: props.firstTimeOffers },
    { key: 'regular', title: 'Recharge Options', icon: 'mdi:wallet-plus-outline', packs: props.regularOffers },
    { key: 'special', title: 'Special Offers', icon: 'mdi:star-four-points-outline', packs: props.specialOffers },
].filter((s) => s.packs && s.packs.length))

const youGet = computed(() =>
    selectedPack.value ? Number(selectedPack.value.amount || 0) + Number(selectedPack.value.bonus_amount || 0) : 0
)

// count-up for the payable total
const shownTotal = ref(0)
let raf = null
watch(totalPayable, (to) => {
    cancelAnimationFrame(raf)
    const target = Number(to)
    const begin = shownTotal.value
    const start = performance.now()
    const tick = (now) => {
        const p = Math.min((now - start) / 500, 1)
        shownTotal.value = begin + (target - begin) * (1 - Math.pow(1 - p, 3))
        if (p < 1) raf = requestAnimationFrame(tick)
    }
    raf = requestAnimationFrame(tick)
})
onBeforeUnmount(() => cancelAnimationFrame(raf))
</script>

<template>

    <Head title="Recharge" />
    <UserLayout>
        <div class="usr-container">
            <div class="rc-grid">

                <!-- Left side: Offers -->
                <div class="rc-left">
                    <!-- Wallet hero -->
                    <section class="rc-hero">
                        <span class="rc-hero-ring"></span>
                        <div>
                            <h1>Add Money to Wallet</h1>
                            <p>Choose from the available recharge packs</p>
                        </div>
                        <div class="rc-balance">
                            <span><Icon icon="mdi:wallet-outline" width="16" height="16" /> Available Balance</span>
                            <b>₹{{ walletBalance }}</b>
                        </div>
                    </section>

                    <section v-for="(sec, si) in sections" :key="sec.key" class="rc-section"
                        :style="{ animationDelay: si * 0.08 + 's' }">
                        <h2><Icon :icon="sec.icon" width="20" height="20" /> {{ sec.title }}</h2>
                        <div class="rc-packs">
                            <button v-for="(pack, i) in sec.packs" :key="pack.id" type="button" @click="selectPack(pack)"
                                class="rc-pack" :class="{ 'is-selected': selectedPack?.id === pack.id }"
                                :style="{ animationDelay: si * 0.08 + i * 0.05 + 's' }">
                                <span v-if="pack.recommended" class="rc-ribbon">
                                    <Icon icon="mdi:star" width="12" height="12" /> Recommended
                                </span>
                                <span class="rc-check"><Icon icon="mdi:check" width="16" height="16" /></span>

                                <strong>₹{{ pack.amount }}</strong>
                                <span v-if="pack.label" class="rc-label">{{ pack.label }}</span>
                                <span v-if="+pack.bonus_amount" class="rc-bonus">+ ₹{{ pack.bonus_amount }} bonus</span>
                            </button>
                        </div>
                    </section>
                </div>

                <!-- Right side: Payment Summary -->
                <aside class="rc-summary">
                    <h3><Icon icon="mdi:receipt-text-outline" width="20" height="20" /> Payment Details</h3>

                    <Transition name="rc-swap" mode="out-in">
                        <div v-if="!selectedPack" key="empty" class="rc-empty">
                            <Icon icon="mdi:cursor-default-click-outline" width="34" height="34" />
                            <span>Select a pack to see your payment details</span>
                        </div>

                        <div v-else key="rows" class="rc-rows">
                            <div class="rc-row"><span>Total Amount</span><b>₹{{ selectedPack?.amount ?? 0 }}</b></div>
                            <div class="rc-row"><span>GST @ 18%</span><b>₹{{ gst }}</b></div>
                            <div class="rc-row is-total"><span>Total Payable</span><b>₹{{ shownTotal.toFixed(2) }}</b></div>

                            <p v-if="selectedPack?.bonus_amount" class="rc-gain">
                                <Icon icon="mdi:gift-open-outline" width="18" height="18" />
                                You will get ₹{{ youGet }} in your wallet
                            </p>
                        </div>
                    </Transition>

                    <!-- Coupon -->
                    <div class="rc-coupon" :class="{ 'is-shake': shake }">
                        <input v-model="couponCode" type="text" placeholder="Enter coupon code" class="usr-input" />
                        <button type="button" @click="applyCoupon" class="usr-btn usr-btn-ghost">Apply</button>
                    </div>
                    <Transition name="rc-swap">
                        <p v-if="couponError" class="usr-error">{{ couponError }}</p>
                    </Transition>

                    <!-- Proceed Button -->
                    <button class="usr-btn usr-btn-green usr-btn-lg rc-pay" :disabled="!selectedPack || paying"
                        @click="proceedToPay">
                        <Icon v-if="paying" icon="mdi:loading" width="20" height="20" class="usr-spin" />
                        <Icon v-else icon="mdi:lock-outline" width="20" height="20" />
                        PROCEED NOW
                    </button>

                    <!-- Trust Badges -->
                    <ul class="rc-trust">
                        <li><Icon icon="mdi:shield-check-outline" width="18" height="18" /> Money Back Guarantee</li>
                        <li><Icon icon="mdi:account-check-outline" width="18" height="18" /> Verified Expert Astrologers</li>
                        <li><Icon icon="mdi:lock-check-outline" width="18" height="18" /> 100% Secure Payment</li>
                    </ul>
                </aside>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.rc-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 960px) {
    .rc-grid {
        grid-template-columns: 1fr 22rem;
        align-items: start;
    }
}

.rc-left {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

/* hero */
.rc-hero {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    overflow: hidden;
    padding: 1.5rem 1.75rem;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, #FF7010 0%, #FF8A3D 55%, #FFB066 100%);
    color: #fff;
    box-shadow: 0 24px 48px -20px rgba(255, 112, 16, 0.6);
}

.rc-hero-ring {
    position: absolute;
    top: -5rem;
    right: -3rem;
    width: 15rem;
    height: 15rem;
    border: 2px dashed rgba(255, 255, 255, 0.35);
    border-radius: 9999px;
    animation: usr-spin 60s linear infinite;
    pointer-events: none;
}

.rc-hero h1 {
    position: relative;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.rc-hero p {
    position: relative;
    margin: 0.25rem 0 0;
    font-size: 0.9rem;
    opacity: 0.92;
}

.rc-balance {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding: 0.7rem 1.1rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(6px);
}

.rc-balance span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 600;
    opacity: 0.95;
}

.rc-balance b {
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1.15;
}

/* sections */
.rc-section {
    animation: usr-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.rc-section h2 {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.9rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.rc-section h2 svg {
    color: #FF7010;
}

.rc-packs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (min-width: 640px) {
    .rc-packs {
        grid-template-columns: repeat(3, 1fr);
    }
}

.rc-pack {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    padding: 1.5rem 1rem 1.1rem;
    border: 2px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    text-align: center;
    cursor: pointer;
    animation: usr-rise 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.rc-pack:hover {
    transform: translateY(-4px);
    border-color: #fdba74;
    box-shadow: 0 18px 30px -16px rgba(124, 45, 18, 0.4);
}

.rc-pack strong {
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1f2937;
}

.rc-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #c2410c;
}

.rc-bonus {
    margin-top: 0.3rem;
    padding: 0.15rem 0.65rem;
    border-radius: 9999px;
    background: #dcfce7;
    font-size: 0.75rem;
    font-weight: 700;
    color: #15803d;
}

.rc-ribbon {
    position: absolute;
    top: -0.65rem;
    left: 50%;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.15rem 0.7rem;
    border-radius: 9999px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    font-size: 0.68rem;
    font-weight: 700;
    white-space: nowrap;
    color: #fff;
    transform: translateX(-50%);
    box-shadow: 0 6px 12px -4px rgba(245, 158, 11, 0.6);
}

.rc-check {
    position: absolute;
    top: 0.6rem;
    right: 0.6rem;
    display: flex;
    width: 1.5rem;
    height: 1.5rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: #FF7010;
    color: #fff;
    transform: scale(0);
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.rc-pack.is-selected {
    border-color: #FF7010;
    background: #fff7ed;
    box-shadow: 0 0 0 4px rgba(255, 112, 16, 0.15), 0 18px 30px -16px rgba(255, 112, 16, 0.5);
}

.rc-pack.is-selected .rc-check {
    transform: scale(1);
}

/* summary */
.rc-summary {
    position: relative;
    padding: 1.4rem;
    border: 1px solid #ffe8d4;
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 16px 36px -16px rgba(124, 45, 18, 0.2);
    animation: usr-rise 0.6s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@media (min-width: 960px) {
    .rc-summary {
        position: sticky;
        top: 5.5rem;
    }
}

.rc-summary h3 {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 1rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.rc-summary h3 svg {
    color: #FF7010;
}

.rc-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem 0.5rem;
    border: 2px dashed #fed7aa;
    border-radius: 1rem;
    text-align: center;
    font-size: 0.85rem;
    color: #9ca3af;
}

.rc-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.55rem 0;
    font-size: 0.92rem;
    color: #4b5563;
    border-bottom: 1px dashed #ffe8d4;
}

.rc-row b {
    color: #1f2937;
    font-variant-numeric: tabular-nums;
}

.rc-row.is-total {
    border-bottom: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.rc-row.is-total b {
    font-size: 1.4rem;
    color: #FF7010;
}

.rc-gain {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0.8rem 0 0;
    padding: 0.6rem 0.8rem;
    border-radius: 0.8rem;
    background: #dcfce7;
    font-size: 0.85rem;
    font-weight: 600;
    color: #15803d;
}

.rc-coupon {
    display: flex;
    gap: 0.5rem;
    margin-top: 1.1rem;
}

.rc-coupon.is-shake {
    animation: rc-shake 0.45s ease;
}

.rc-pay {
    width: 100%;
    margin-top: 1.25rem;
}

.rc-trust {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 1.25rem 0 0;
    padding: 0;
    list-style: none;
    font-size: 0.82rem;
    color: #6b7280;
}

.rc-trust li {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.rc-trust svg {
    color: #16a34a;
}

.rc-swap-enter-active,
.rc-swap-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.rc-swap-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.rc-swap-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

@keyframes rc-shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
}
</style>