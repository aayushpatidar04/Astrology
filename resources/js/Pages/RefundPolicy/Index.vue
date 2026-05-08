<template>

  <Head title="Policies" />
  <div class="policy-page">
    <div class="stars" aria-hidden="true">
      <span v-for="n in 60" :key="n" class="star" :style="starStyle(n)" />
    </div>

    <div class="container">
      <header class="page-header">
        <div class="emblem">✦</div>
        <h1 class="title">My Astro Sathi</h1>
        <p class="subtitle">Store Policies</p>
      </header>

      <div class="tab-bar" role="tablist">
        <button v-for="tab in tabs" :key="tab.key" class="tab-btn" :class="{ active: activeTab === tab.key }" role="tab"
          :aria-selected="activeTab === tab.key" @click="activeTab = tab.key">
          <span class="tab-icon">{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>

      <transition name="fade" mode="out-in">

        <div v-if="activeTab === 'refund'" key="refund" class="policy-content">
          <p class="lead-text" style="text-align: left;">
            This refund and cancellation policy outlines how you can cancel or seek a refund for a product / service
            that you have purchased through the Platform. Under this policy:
          </p>

          <div class="policy-card paragraph-card">
            <p class="card-text">Cancellations will only be considered if the request is made 5 days of placing the
              order. However, cancellation requests may not be entertained if the orders have been communicated to such
              sellers / merchant(s) listed on the Platform and they have initiated the process of shipping them, or the
              product is out for delivery. In such an event, you may choose to reject the product at the doorstep.</p>
          </div>

          <div class="policy-card paragraph-card">
            <p class="card-text">9752337971 does not accept cancellation requests for perishable items like flowers,
              eatables, etc. However, the refund can be made if the user establishes that the quality of
              the product delivered is not good.</p>
          </div>

          <div class="policy-card paragraph-card">
            <p class="card-text">In case of receipt of damaged or defective items, please report to our customer service
              team. The request would be entertained once the seller/ merchant listed on the Platform, has checked and
              determined the same at its own end. This should be reported within 5 days of receipt of products. In case
              you feel that the product received is not as shown on the site or as per your expectations, you must bring
              it to the notice of our customer service within 5 days of receiving the product. The customer service team
              after looking into your complaint will take an appropriate decision.</p>
          </div>

          <div class="policy-card paragraph-card">
            <p class="card-text">In case of complaints regarding the products that come with a warranty from the
              manufacturers, please refer the issue to them.</p>
          </div>

          <div class="policy-card paragraph-card">
            <p class="card-text">In case of any refunds approved by 9752337971, it will take 2 days for the refund to be
              processed to you.</p>
          </div>
        </div>

        <div v-else-if="activeTab === 'return'" key="return" class="policy-content">
          <div class="policy-card paragraph-card">
            <p class="card-text">We offer refund / exchange within first 2 days from the date of your purchase. If 2
              days have passed since your purchase, you will not be offered a return, exchange or refund of any kind. In
              order to become eligible for a return or an exchange, (i) the purchased item should be unused and in the
              same condition as you received it, (ii) the item must have original packaging, (iii) if the item that you
              purchased on a sale, then the item may not be eligible for a return / exchange. Further, only such items
              are replaced by us (based on an exchange request), if such items are found defective or damaged.</p>
          </div>

          <div class="policy-card paragraph-card">
            <p class="card-text">You agree that there may be a certain category of products / items that are exempted
              from returns or refunds. Such categories of the products would be identified to you at the item of
              purchase. For exchange / return accepted request(s) (as applicable), once your returned product / item is
              received and inspected by us, we will send you an email to notify you about receipt of the returned /
              exchanged product. Further. If the same has been approved after the quality check at our end, your request
              (i.e. return / exchange) will be processed in accordance with our policies.</p>
          </div>
        </div>

        <div v-else key="shipping" class="policy-content">
          <div class="policy-card paragraph-card">
            <p class="card-text">The orders for the user are shipped through registered domestic courier companies
              and/or speed post only. Orders are shipped within 2 days from the date of the order and/or payment or as
              per the delivery date agreed at the time of order confirmation and delivering of the shipment, subject to
              courier company / post office norms. Platform Owner shall not be liable for any delay in delivery by the
              courier company / postal authority. Delivery of all orders will be made to the address provided by the
              buyer at the time of purchase. Delivery of our services will be confirmed on your email ID as specified at
              the time of registration. If there are any shipping cost(s) levied by the seller or the Platform Owner (as
              the case be), the same is not refundable.</p>
          </div>
        </div>

      </transition>

      <footer class="page-footer">✦ My Astro Sathi — Written in the Stars ✦</footer>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

const activeTab = ref('refund')

const tabs = [
  { key: 'refund', label: 'Refund & Cancellation', icon: '💳' },
]
  // { key: 'return', label: 'Return Policy', icon: '↩️' },
  // { key: 'shipping', label: 'Shipping Policy', icon: '🚚' },

function starStyle(n) {
  const size = (n % 2) + 1
  return {
    width: size + 'px',
    height: size + 'px',
    top: ((n * 137.5) % 100) + '%',
    left: ((n * 97.3) % 100) + '%',
    animationDelay: ((n * 0.3) % 5) + 's',
    animationDuration: (3 + (n % 4)) + 's',
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Lato:wght@300;400&display=swap');

/* ─── Base ─────────────────────────────────── */
.policy-page {
  min-height: 100vh;
  background: #0a0614;
  color: #e8dff5;
  font-family: 'Lato', sans-serif;
  font-weight: 300;
  position: relative;
  overflow-x: hidden;
}

/* ─── Stars ─────────────────────────────────── */
.stars {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.star {
  position: absolute;
  border-radius: 50%;
  background: #fff;
  opacity: 0;
  animation: twinkle linear infinite;
}

@keyframes twinkle {

  0%,
  100% {
    opacity: 0;
  }

  50% {
    opacity: 0.65;
  }
}

/* ─── Layout ────────────────────────────────── */
.container {
  position: relative;
  z-index: 1;
  max-width: 860px;
  margin: 0 auto;
  padding: 60px 24px 80px;
}

/* ─── Header ────────────────────────────────── */
.page-header {
  text-align: center;
  margin-bottom: 40px;
}

.emblem {
  font-size: 2.2rem;
  color: #c9a84c;
  display: block;
  margin-bottom: 10px;
  animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }

  50% {
    opacity: 0.7;
    transform: scale(1.15);
  }
}

.title {
  font-family: 'Cinzel', serif;
  font-size: clamp(1.8rem, 5vw, 2.8rem);
  font-weight: 700;
  color: #f0e6c8;
  letter-spacing: 0.08em;
  margin: 0 0 6px;
}

.subtitle {
  font-family: 'Cinzel', serif;
  font-size: 0.95rem;
  color: #c9a84c;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin: 0;
}

/* ─── Tab Bar ───────────────────────────────── */
.tab-bar {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-bottom: 40px;
  flex-wrap: wrap;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 28px;
  border: 1px solid rgba(201, 168, 76, 0.3);
  border-radius: 50px;
  background: transparent;
  color: #9480b0;
  font-family: 'Cinzel', serif;
  font-size: 0.82rem;
  letter-spacing: 0.08em;
  cursor: pointer;
  transition: all 0.25s ease;
}

.tab-btn:hover {
  border-color: rgba(201, 168, 76, 0.6);
  color: #c9a84c;
}

.tab-btn.active {
  background: rgba(201, 168, 76, 0.12);
  border-color: #c9a84c;
  color: #f0d98a;
}

.tab-icon {
  font-size: 1rem;
}

/* ─── Fade Transition ───────────────────────── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* ─── Lead Text ─────────────────────────────── */
.lead-text {
  text-align: center;
  font-size: 0.95rem;
  line-height: 1.85;
  color: #c5b8d8;
  margin-bottom: 24px;
  padding: 20px 24px;
  border: 1px solid rgba(201, 168, 76, 0.15);
  border-radius: 12px;
  background: rgba(201, 168, 76, 0.03);
}

/* ─── Card Grid ─────────────────────────────── */
.paragraph-card {
  margin-bottom: 16px;
  display: block;
  width: 100%;
}

.policy-card {
  padding: 22px 24px;
  border: 1px solid rgba(148, 128, 176, 0.18);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.02);
  transition: border-color 0.2s, transform 0.2s;
}

.policy-card:hover {
  border-color: rgba(201, 168, 76, 0.35);
  transform: translateY(-2px);
}

.card-text {
  font-size: 0.92rem;
  line-height: 1.8;
  color: #c5b8d8;
  margin: 0;
  text-align: left;
}

/* ─── Footer ────────────────────────────────── */
.page-footer {
  text-align: center;
  margin-top: 56px;
  font-family: 'Cinzel', serif;
  font-size: 0.78rem;
  color: #6a5880;
  letter-spacing: 0.12em;
}

/* ─── Responsive ────────────────────────────── */
@media (max-width: 520px) {
  .tab-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .tab-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>