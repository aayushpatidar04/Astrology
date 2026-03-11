<template>
    <nav class="flex flex-wrap justify-center gap-2">
        <template v-for="(link, index) in links" :key="index">
            <!-- Disabled / Ellipsis -->
            <span v-if="!link.url" class="px-3 py-1 text-gray-400 cursor-default" v-html="link.label">
            </span>

            <!-- Active page -->
            <span v-else-if="link.active" class="px-3 py-1 bg-orange-600 text-white rounded" v-html="link.label">
            </span>

            <!-- Normal link -->
            <Link v-else :href="link.url" class="px-3 py-1 rounded hover:bg-orange-100 text-gray-700" v-html="link.label">
            </Link>
        </template>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        required: true
    }
})
</script>
