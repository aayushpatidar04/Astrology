<script setup>
import { ref } from "vue"
import { Icon } from '@iconify/vue'
import Reveal from '@/Components/Reveal.vue'

const dob = ref("")
const tob = ref("")
const pob = ref("")

function findZodiac() {
  // For now, just log values — later you can add zodiac calculation logic
  console.log("DOB:", dob.value, "TOB:", tob.value, "POB:", pob.value)
}

const inputClass =
  "w-full rounded-lg border border-orange-200 bg-white/80 px-4 py-2.5 text-gray-800 shadow-sm transition duration-300 placeholder:text-gray-400 hover:border-orange-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/40"
</script>

<template>
  <section class="relative overflow-hidden bg-cream py-20 px-6 md:px-12 mt-12">
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-orange-300/30 blur-3xl animate-float-slow"></div>

    <!-- Heading -->
    <Reveal class="relative text-center max-w-2xl mx-auto mb-12">
      <h2 class="section-heading section-heading-accent">Know Your Zodiac Sign</h2>
      <p class="text-gray-600 mt-6">
        Enter your birth details below to discover your zodiac sign and unlock personalized
        astrological insights.
      </p>
    </Reveal>

    <!-- Form -->
    <Reveal direction="scale" :delay="100" class="relative max-w-3xl mx-auto">
      <div
        class="relative overflow-hidden rounded-2xl border border-white/60 bg-gradient-to-br from-orange-100 to-orange-200 p-8 shadow-xl shadow-orange-900/10">
        <div class="pointer-events-none absolute -top-16 -right-16 h-48 w-48 rounded-full bg-white/40 blur-2xl"></div>

        <form @submit.prevent="findZodiac" class="relative grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
          <!-- Date of Birth -->
          <div>
            <label class="mb-2 flex items-center gap-1 font-medium text-gray-700">
              <Icon icon="mdi:calendar-month-outline" width="18" class="text-primary" /> Date Of Birth
            </label>
            <input v-model="dob" type="date" :class="inputClass" />
          </div>

          <!-- Time of Birth -->
          <div>
            <label class="mb-2 flex items-center gap-1 font-medium text-gray-700">
              <Icon icon="mdi:clock-outline" width="18" class="text-primary" /> Time Of Birth
            </label>
            <input v-model="tob" type="time" placeholder="08:00" :class="inputClass" />
          </div>

          <!-- Place of Birth -->
          <div>
            <label class="mb-2 flex items-center gap-1 font-medium text-gray-700">
              <Icon icon="mdi:map-marker-outline" width="18" class="text-primary" /> Place Of Birth
            </label>
            <input v-model="pob" type="text" placeholder="Enter City Name..." :class="inputClass" />
          </div>

          <!-- Button -->
          <div class="md:col-span-3 flex justify-center mt-4">
            <a href="/login" class="btn-primary !px-8 !py-3">
              <Icon icon="mdi:star-four-points" width="18" />
              FIND ZODIAC
            </a>
          </div>
        </form>
      </div>
    </Reveal>
  </section>
</template>