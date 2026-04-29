<template>

  <Head title="Refund Policy" />
  <div class="policy-page">
    <!-- Starfield -->
    <div class="stars" aria-hidden="true">
      <span v-for="n in 60" :key="n" class="star" :style="starStyle(n)" />
    </div>

    <div class="container">
      <!-- Header -->
      <header class="page-header">
        <div class="emblem">✦</div>
        <h1 class="title">My Astro Sathi</h1>
        <p class="subtitle">Shipping &amp; Return Policy</p>
      </header>

      <!-- Tab Toggle -->
      <div class="tab-bar" role="tablist">
        <button v-for="tab in tabs" :key="tab.key" class="tab-btn" :class="{ active: activeTab === tab.key }" role="tab"
          :aria-selected="activeTab === tab.key" @click="activeTab = tab.key">
          <span class="tab-icon">{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>

      <!-- Shipping Policy -->
      <transition name="fade" mode="out-in">
        <div v-if="activeTab === 'shipping'" key="shipping" class="policy-content">
          <p class="lead-text">
            Our standard order processing time is within 0–2 days of receiving the order (may vary by product).
            We partner with reputable logistics companies such as Blue Dart, Delhivery, Xpress Bees, and other
            couriers based on destination. We appreciate your understanding and encourage you to plan accordingly
            for a stress-free shopping experience.
          </p>

          <div class="section-grid">
            <div v-for="item in shippingItems" :key="item.title" class="policy-card">
              <div class="card-icon">{{ item.icon }}</div>
              <h3 class="card-title">{{ item.title }}</h3>
              <p class="card-text">{{ item.text }}</p>
            </div>
          </div>

          <div class="free-shipping-banner">
            <span class="banner-icon">🎁</span>
            <div>
              <strong>Free Shipping on All Products</strong>
              <p>We currently offer free shipping on every order — no minimum required.</p>
            </div>
          </div>

          <p class="policy-note">
            This Delivery Policy is subject to change. Any updates will be posted on our website.
            For questions, contact our
            <a href="mailto:support@myastrosathi.com" class="link">customer support team</a>.
          </p>
        </div>

        <!-- Return Policy -->
        <div v-else key="return" class="policy-content">
          <p class="lead-text">
            We offer a 24-hour return policy for our products. Please read the guidelines below carefully
            to ensure a smooth return or refund experience.
          </p>

          <div class="section-grid">
            <div v-for="item in returnItems" :key="item.title" class="policy-card">
              <div class="card-icon">{{ item.icon }}</div>
              <h3 class="card-title">{{ item.title }}</h3>
              <p class="card-text">{{ item.text }}</p>
            </div>
          </div>

          <div class="replacement-block">
            <h3 class="block-title">🔄 Replacement Process</h3>
            <div class="replacement-steps">
              <div v-for="(step, i) in replacementSteps" :key="i" class="step">
                <div class="step-number">{{ i + 1 }}</div>
                <div>
                  <strong>{{ step.title }}</strong>
                  <p>{{ step.text }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="alert-box">
            <span>⚠️</span>
            <p>
              Orders once placed <strong>cannot be cancelled or exchanged</strong> for another product.
              For questions, contact us at
              <a href="mailto:support@myastrosathi.com" class="link">support@myastrosathi.com</a>.
            </p>
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

const activeTab = ref('shipping')

const tabs = [
  { key: 'shipping', label: 'Shipping Policy', icon: '🚚' },
  { key: 'return', label: 'Return Policy', icon: '↩️' },
]

const shippingItems = [
  {
    icon: '📦',
    title: 'Shipping Methods & Timeframes',
    text: 'We offer various shipping methods to suit different needs. Standard shipping typically takes 0–4 business days. Delivery times may vary by destination or due to unforeseen circumstances such as holidays or weather delays.',
  },
  {
    icon: '⚙️',
    title: 'Order Processing',
    text: 'Orders are processed after receipt. During peak seasons or promotional events, processing may take slightly longer due to increased demand. We strive to ship all orders as quickly as possible.',
  },
  {
    icon: '🔎',
    title: 'Tracking Information',
    text: 'After your order is shipped, you will receive a tracking link via email or SMS to monitor your package\'s status until delivery.',
  },
  {
    icon: '🚫',
    title: 'Shipping Restrictions',
    text: 'We offer domestic shipping to various locations. Certain regions may not be serviceable due to legal or logistical reasons. Orders to restricted areas may be cancelled if necessary.',
  },
  {
    icon: '📍',
    title: 'Address Accuracy',
    text: 'Customers are responsible for providing accurate shipping information. Errors in the address may cause delivery delays. Please verify your address before finalizing your order.',
  },
  {
    icon: '🔔',
    title: 'Delivery Attempts',
    text: 'Our partners make multiple delivery attempts. If unsuccessful, a notification is sent via Email or SMS with instructions to reschedule or collect from a designated location. If an order is returned, a refund will be issued upon parcel receipt.',
  },
  {
    icon: '📫',
    title: 'Damaged or Lost Packages',
    text: 'In the rare event of a lost or damaged package, our support team is here to help. Please reach out as soon as possible to initiate an investigation and resolution.',
  },
  {
    icon: '⏳',
    title: 'Delayed Shipments',
    text: 'While we aim to deliver within estimated timeframes, delays may occur due to unforeseen circumstances. We will keep you informed and work to resolve any issues promptly.',
  },
]

const returnItems = [
  {
    icon: '🕐',
    title: '24-Hour Return Window',
    text: 'To be eligible for a return, please take a video of the package being opened. Returns must be initiated within 24 hours of receiving the order.',
  },
  {
    icon: '📹',
    title: 'Damaged Product Returns',
    text: 'We accept returns only if the product is damaged upon delivery. Send video proof to support@myastrosathi.com within 24 hours of receiving your order.',
  },
  {
    icon: '📣',
    title: 'Notification Requirement',
    text: 'Returns or exchanges cannot be processed if we are not notified within the 24-hour window. Valid proof is required for any refund or replacement to be approved.',
  },
]

const replacementSteps = [
  {
    title: 'Approval & Replacement',
    text: 'Once your return is approved, we will dispatch a replacement of the same product.',
  },
  {
    title: 'Refund Timing',
    text: 'Refunds are processed back to the original payment method within 5–12 working days.',
  },
  {
    title: 'Product Unavailability',
    text: 'A complete refund will be offered only if the same product is unavailable for replacement.',
  },
]

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
  margin-bottom: 36px;
  padding: 20px 24px;
  border: 1px solid rgba(201, 168, 76, 0.15);
  border-radius: 12px;
  background: rgba(201, 168, 76, 0.03);
}

/* ─── Card Grid ─────────────────────────────── */
.section-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.policy-card {
  padding: 22px 20px;
  border: 1px solid rgba(148, 128, 176, 0.18);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.02);
  transition: border-color 0.2s, transform 0.2s;
}

.policy-card:hover {
  border-color: rgba(201, 168, 76, 0.35);
  transform: translateY(-3px);
}

.card-icon {
  font-size: 1.5rem;
  margin-bottom: 10px;
}

.card-title {
  font-family: 'Cinzel', serif;
  font-size: 0.85rem;
  font-weight: 600;
  color: #f0e6c8;
  letter-spacing: 0.04em;
  margin: 0 0 8px;
}

.card-text {
  font-size: 0.88rem;
  line-height: 1.75;
  color: #a898c0;
  margin: 0;
}

/* ─── Free Shipping Banner ──────────────────── */
.free-shipping-banner {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 20px 24px;
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 12px;
  background: rgba(201, 168, 76, 0.07);
  margin-bottom: 24px;
}

.banner-icon {
  font-size: 2rem;
}

.free-shipping-banner strong {
  display: block;
  color: #f0d98a;
  font-size: 0.97rem;
  margin-bottom: 4px;
}

.free-shipping-banner p {
  margin: 0;
  font-size: 0.87rem;
  color: #a898c0;
}

/* ─── Policy Note ───────────────────────────── */
.policy-note {
  font-size: 0.85rem;
  color: #7a6a90;
  line-height: 1.75;
  text-align: center;
}

/* ─── Replacement Block ─────────────────────── */
.replacement-block {
  border: 1px solid rgba(148, 128, 176, 0.22);
  border-radius: 14px;
  padding: 28px 24px;
  margin-bottom: 24px;
  background: rgba(255, 255, 255, 0.02);
}

.block-title {
  font-family: 'Cinzel', serif;
  font-size: 1rem;
  color: #f0e6c8;
  margin: 0 0 22px;
}

.replacement-steps {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.step {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.step-number {
  min-width: 30px;
  height: 30px;
  border-radius: 50%;
  background: rgba(201, 168, 76, 0.15);
  border: 1px solid rgba(201, 168, 76, 0.4);
  color: #c9a84c;
  font-family: 'Cinzel', serif;
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step strong {
  display: block;
  color: #e8dff5;
  font-size: 0.9rem;
  margin-bottom: 4px;
}

.step p {
  margin: 0;
  font-size: 0.87rem;
  color: #a898c0;
  line-height: 1.7;
}

/* ─── Alert Box ─────────────────────────────── */
.alert-box {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 18px 22px;
  border: 1px solid rgba(220, 100, 100, 0.25);
  border-radius: 12px;
  background: rgba(220, 100, 100, 0.06);
  font-size: 0.88rem;
  line-height: 1.75;
  color: #c8a8b8;
}

.alert-box span {
  font-size: 1.3rem;
}

.alert-box p {
  margin: 0;
}

.alert-box strong {
  color: #f0c8c8;
}

/* ─── Links ─────────────────────────────────── */
.link {
  color: #c9a84c;
  text-decoration: none;
  border-bottom: 1px solid rgba(201, 168, 76, 0.35);
  transition: color 0.2s, border-color 0.2s;
}

.link:hover {
  color: #f0d98a;
  border-color: #f0d98a;
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
    align-items: center;
  }

  .tab-btn {
    width: 100%;
    justify-content: center;
  }

  .section-grid {
    grid-template-columns: 1fr;
  }

  .free-shipping-banner {
    flex-direction: column;
    text-align: center;
  }
}
</style>