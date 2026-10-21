<template>
  <Head title="Policies" />

  <PolicyLayout subtitle="Store Policies">
    <!-- Tabs (only shown when there is more than one policy tab) -->
    <div v-if="tabs.length > 1" class="pl-tabs" role="tablist">
      <button v-for="tab in tabs" :key="tab.key" class="pl-tab" :class="{ 'is-active': activeTab === tab.key }"
        role="tab" :aria-selected="activeTab === tab.key" @click="activeTab = tab.key">
        <span>{{ tab.icon }}</span>
        {{ tab.label }}
      </button>
    </div>

    <transition name="pl-fade" mode="out-in">

      <div v-if="activeTab === 'refund'" key="refund">
        <h2 class="pl-doc-title">Cancellation and Refund Policy</h2>
        <p class="pl-meta">Last Updated: May 18, 2026</p>

        <p class="pl-intro">
          Thank you for choosing My Astro Sathi
          <a href="https://myastrosathi.com" class="pl-a">
            https://myastrosathi.com
          </a>. Please review our policy for service and consultation bookings below.
        </p>

        <div class="pl-card">
          <h3 class="pl-card-title">Booking & Cancellation Policy</h3>
          <p class="pl-card-text">
            <strong>Advance Notice (More than 24 hours):</strong> You can cancel or reschedule your consultation up to
            24 hours before your appointment for a full refund.
          </p>
          <p class="pl-card-text">
            <strong>Late Notice & No-Shows:</strong> Cancellations made less than 24 hours before the session, or
            missing your scheduled appointment entirely, are non-refundable.
          </p>
          <p class="pl-card-text">
            <strong>Cancellations by Us:</strong> If we must cancel your session due to an emergency, you will receive
            a 100% refund.
          </p>
        </div>

        <div class="pl-card">
          <h3 class="pl-card-title">Refund Policy</h3>
          <p class="pl-card-text">
            Approved refunds will be credited back to your original payment method within 5 to 12 working days.
          </p>
        </div>

        <div class="pl-card">
          <h3 class="pl-card-title">Contact Us</h3>
          <p class="pl-card-text">For cancellations or support, contact us at:</p>
          <p class="pl-card-text">
            <strong>Email:</strong>
            <a href="mailto:support@myastrosathi.com" class="pl-a">
              support@myastrosathi.com
            </a>
          </p>
          <p class="pl-card-text">
            <strong>Phone:</strong>
            <a href="tel:+919201191929" class="pl-a">
              +91 9201191929
            </a>
          </p>
        </div>
      </div>


      <div v-else-if="activeTab === 'return'" key="return">
        <div class="pl-card">
          <p class="pl-card-text">We offer refund / exchange within first 2 days from the date of your purchase. If 2
            days have passed since your purchase, you will not be offered a return, exchange or refund of any kind. In
            order to become eligible for a return or an exchange, (i) the purchased item should be unused and in the
            same condition as you received it, (ii) the item must have original packaging, (iii) if the item that you
            purchased on a sale, then the item may not be eligible for a return / exchange. Further, only such items
            are replaced by us (based on an exchange request), if such items are found defective or damaged.</p>
        </div>

        <div class="pl-card">
          <p class="pl-card-text">You agree that there may be a certain category of products / items that are exempted
            from returns or refunds. Such categories of the products would be identified to you at the item of
            purchase. For exchange / return accepted request(s) (as applicable), once your returned product / item is
            received and inspected by us, we will send you an email to notify you about receipt of the returned /
            exchanged product. Further. If the same has been approved after the quality check at our end, your request
            (i.e. return / exchange) will be processed in accordance with our policies.</p>
        </div>
      </div>

      <div v-else key="shipping">
        <div class="pl-card">
          <p class="pl-card-text">The orders for the user are shipped through registered domestic courier companies
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
  </PolicyLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import PolicyLayout from '@/Layouts/PolicyLayout.vue'

const activeTab = ref('refund')

const tabs = [
  { key: 'refund', label: 'Refund & Cancellation', icon: '💳' },
]
// { key: 'return', label: 'Return Policy', icon: '↩️' },
// { key: 'shipping', label: 'Shipping Policy', icon: '🚚' },
</script>