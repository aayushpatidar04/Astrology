<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Header from '@/Pages/Layouts/Header.vue'
import Footer from '@/Pages/Layouts/Footer.vue'
import { ref } from 'vue'

const props = defineProps({
  sign: String,
  type: String,
  date: String,
  horoscope: Object,
  user: Object,
})

function normalizeType(t) {
  if (t.toLowerCase() === 'today') return 'daily'
  return t.toLowerCase()
}

const activeAspect = ref('love')

const aspects = [
  { key: 'love', label: 'Love', icon: '❤️', color: 'text-red-600' },
  { key: 'health', label: 'Health', icon: '🩺', color: 'text-green-600' },
  { key: 'career', label: 'Career', icon: '💼', color: 'text-blue-600' },
  { key: 'emotions', label: 'Emotions', icon: '😊', color: 'text-yellow-600' },
  { key: 'travel', label: 'Travel', icon: '✈️', color: 'text-purple-600' },
]

const signs = [
  'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
  'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'
]

const timeframes = ['Yesterday', 'Today', 'Tomorrow', 'Weekly', 'Monthly', 'Yearly']
</script>

<template>

  <Head :title="`${props.sign} ${props.type} Horoscope`" />

  <Header :user="props.user" />

  <div class="max-w-7xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div
      class="bg-gradient-to-r from-orange-400 to-yellow-400 rounded-lg p-6 flex flex-col md:flex-row justify-between items-center">
      <div class="text-center md:text-left mb-4 md:mb-0">
        <h1 class="text-2xl font-bold">{{ props.sign }} {{ props.type }} Horoscope</h1>
        <p class="text-gray-700">{{ props.date }}</p>
      </div>
      <div>
        <img src="/images/numerology.svg" alt="Zodiac Wheel" class="w-24 md:w-36 mx-auto md:mx-0" />
      </div>
    </div>

    <!-- Zodiac icons row -->
    <div class="flex flex-wrap justify-center md:justify-between mt-4 text-sm gap-4">
      <span v-for="z in signs" :key="z" class="flex flex-col items-center cursor-pointer hover:text-orange-600">
        <Link :href="route('horoscope', { type: props.type.toLowerCase(), sign: z })" class="text-center">
          <div class="rounded-full border-2 border-orange-500 hover:bg-orange-300"
            :class="{ 'bg-orange-300': z === props.sign }">
            <img :src="`/images/${z}.png`" :alt="z" class="w-12 h-12 md:w-16 md:h-16" />
          </div>
          <span class="mt-1 text-xs md:text-sm" :class="{ 'text-orange-600 font-semibold': z === props.sign }">{{ z
            }}</span>
        </Link>
      </span>
    </div>

    <!-- Timeframe navigation -->
    <div class="flex flex-wrap justify-center md:justify-between gap-2 my-4">
      <Link v-for="t in timeframes" :key="t" :href="route('horoscope', { type: normalizeType(t), sign: props.sign })"
        class="px-3 py-1 rounded border hover:bg-orange-100 text-center flex-1 md:flex-none" :class="normalizeType(t) === props.type.toLowerCase()
          ? 'border-b-[3px] border-orange-500 text-orange-600 font-semibold'
          : 'text-gray-600'">
        {{ t }}
      </Link>
    </div>

    <hr class="border-t-2 border-orange-500 my-4">

    <!-- Lucky items -->
    <h2 class="mt-6 text-lg font-semibold mb-2">Lucky items for you</h2>
    <div class="rounded-lg p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
        <div class="bg-blue-50 py-3">
          <p class="font-bold">Colors</p>
          <div class="flex gap-2 justify-center mt-2 flex-wrap">
            <span v-for="color in props.horoscope.colors.split(',')" :key="color.trim()"
              class="w-6 h-6 rounded-full border" :style="{ backgroundColor: color.trim() }"></span>
          </div>
        </div>
        <div class="bg-green-50 py-3">
          <p class="font-bold">Numbers</p>
          <p class="text-2xl font-bold">{{ props.horoscope.numbers }}</p>
        </div>
        <div class="bg-red-50 py-3">
          <p class="font-bold">Alphabets</p>
          <p class="text-2xl font-bold">{{ props.horoscope.alphabets }}</p>
        </div>
      </div>
    </div>

    <!-- Life aspects tabs -->
    <div class="flex flex-wrap justify-center gap-2 md:gap-4 mt-6 border-b">
      <button v-for="aspect in aspects" :key="aspect.key" @click="activeAspect = aspect.key"
        class="px-3 py-2 font-semibold flex-1 md:flex-none" :class="activeAspect === aspect.key
          ? `${aspect.color} border-b-[3px] border-orange-500`
          : 'text-gray-600 hover:text-orange-600'">
        {{ aspect.icon }} {{ aspect.label }}
      </button>
    </div>

    <!-- Active aspect content -->
    <div class="mt-6 p-4 border rounded">
      <h3 class="font-semibold mb-2" :class="aspects.find(a => a.key === activeAspect).color">
        {{aspects.find(a => a.key === activeAspect).icon}}
        {{aspects.find(a => a.key === activeAspect).label}}
      </h3>
      <p>{{ props.horoscope[activeAspect] }}</p>
    </div>

    <!-- Tips -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-5">
      <div class="p-4 bg-indigo-50 rounded">
        <h3 class="font-semibold">Cosmic Tip</h3>
        <p>{{ props.horoscope.cosmic_tip }}</p>
      </div>
      <div class="p-4 bg-pink-50 rounded">
        <h3 class="font-semibold">Tip for Singles</h3>
        <p>{{ props.horoscope.tip_for_singles }}</p>
      </div>
      <div class="p-4 bg-green-50 rounded">
        <h3 class="font-semibold">Tip for Couples</h3>
        <p>{{ props.horoscope.tip_for_couples }}</p>
      </div>
    </div>

    <!-- General description -->
    <div class="mt-6 p-4 border rounded">
      <div class="quill-content max-w-none text-gray-700" v-html="props.horoscope.description"></div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
:deep(.quill-content p) {
  margin: 1em 0;
  line-height: 1.6;
}

:deep(.quill-content h1) {
  font-size: 2em;
  font-weight: bold;
  margin: 0.67em 0;
}

:deep(.quill-content h2) {
  font-size: 1.5em;
  font-weight: bold;
  margin: 0.75em 0;
}

:deep(.quill-content h3) {
  font-size: 1.17em;
  font-weight: bold;
  margin: 0.83em 0;
}

:deep(.quill-content ul) {
  list-style: disc;
  margin: 1em 0 1em 1.5em;
}

:deep(.quill-content ol) {
  list-style: decimal;
  margin: 1em 0 1em 1.5em;
}

:deep(.quill-content li) {
  margin: 0.5em 0;
}

:deep(.quill-content strong) {
  font-weight: bold;
}

:deep(.quill-content em) {
  font-style: italic;
}

:deep(.quill-content u) {
  text-decoration: underline;
}

:deep(.quill-content s) {
  text-decoration: line-through;
}

:deep(.quill-content a) {
  color: #2563eb;
  text-decoration: underline;
}

:deep(.quill-content a:hover) {
  color: #1e40af;
}

:deep(.quill-content img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1em 0;
}

:deep(.quill-content blockquote) {
  border-left: 4px solid #ccc;
  padding-left: 1em;
  color: #555;
  font-style: italic;
  margin: 1em 0;
}

/* Code blocks */
:deep(.quill-content pre) {
  background: #f3f4f6;
  /* Tailwind gray-100 */
  padding: 1em;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin: 1em 0;
}

:deep(.quill-content code) {
  font-family: monospace;
  background: #f9fafb;
  /* Tailwind gray-50 */
  padding: 0.2em 0.4em;
  border-radius: 0.25rem;
}

/* Quill custom classes */
:deep(.quill-content .ql-align-center) {
  text-align: center;
}

:deep(.quill-content .ql-align-right) {
  text-align: right;
}

:deep(.quill-content .ql-align-justify) {
  text-align: justify;
}

:deep(.quill-content .ql-size-small) {
  font-size: 0.875rem;
}

:deep(.quill-content .ql-size-large) {
  font-size: 1.5rem;
}

:deep(.quill-content .ql-size-huge) {
  font-size: 2.25rem;
}

:deep(.quill-content .ql-indent-1) {
  margin-left: 2em;
}

:deep(.quill-content .ql-indent-2) {
  margin-left: 4em;
}

:deep(.quill-content .ql-indent-3) {
  margin-left: 6em;
}
</style>
