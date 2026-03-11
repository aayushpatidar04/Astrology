<script setup>
import { ref } from "vue"

const dob = ref("")
const tob = ref("")
const pob = ref("")

function findZodiac() {
  // For now, just log values — later you can add zodiac calculation logic
  console.log("DOB:", dob.value, "TOB:", tob.value, "POB:", pob.value)
}
</script>

<template>
  <section class="bg-[#fdf6f0] py-16 px-6 md:px-12 mt-12">
    <!-- Heading -->
    <div class="text-center max-w-2xl mx-auto mb-12">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Know Your Zodiac Sign</h2>
      <p class="text-gray-600 mt-4">
        Enter your birth details below to discover your zodiac sign and unlock personalized
        astrological insights.
      </p>
    </div>

    <!-- Form -->
    <div class="max-w-3xl mx-auto bg-orange-200 p-8 rounded-lg shadow-lg">
      <form @submit.prevent="findZodiac" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <!-- Date of Birth -->
        <div>
          <label class="block text-gray-700 font-medium mb-2">Date Of Birth</label>
          <input
            v-model="dob"
            type="date"
            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-orange-500"
          />
        </div>

        <!-- Time of Birth -->
        <div>
          <label class="block text-gray-700 font-medium mb-2">Time Of Birth</label>
          <input
            v-model="tob"
            type="time"
            placeholder="08:00"
            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-orange-500"
          />
        </div>

        <!-- Place of Birth -->
        <div>
          <label class="block text-gray-700 font-medium mb-2">Place Of Birth</label>
          <input
            v-model="pob"
            type="text"
            placeholder="Enter City Name..."
            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-orange-500"
          />
        </div>

        <!-- Button -->
        <div class="md:col-span-3 flex justify-center mt-6">
          <button
            type="submit"
            class="px-6 py-3 bg-orange-500 text-white font-semibold rounded hover:bg-orange-600 transition"
          >
            FIND ZODIAC
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

