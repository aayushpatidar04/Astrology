<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Create from './Create.vue';
import { Icon } from '@iconify/vue';
import Toogle from './Toogle.vue';
import Edit from './Edit.vue';
import Delete from './Delete.vue';

const props = defineProps({
    banners: Object,
})

function previewImage(url) {
    window.open(url, '_blank');
}

</script>

<template>

    <Head title="Banners" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Banners
            </h2>
        </template>
        <div class="container p-4 mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold"></h2>
                <Create />
            </div>

            <!-- Banner Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="banner in banners.data" :key="banner.id"
                    class="relative rounded-lg overflow-hidden shadow-lg group">

                    <!-- Background Image -->
                    <div class="h-80 w-full bg-cover bg-center" :style="`background-image: url(${banner.image})`">
                    
                    <!-- Eye icon overlay (only on image area) -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100
                  transition-opacity duration-200 bg-black/40">
                            <button type="button" @click="previewImage(banner.image)"
                                class="text-white text-4xl focus:outline-none">
                                <Icon icon="mdi:eye"></Icon>
                            </button>
                        </div>
                    </div>

                    <!-- Overlay with details (bottom only, not blocked) -->
                    <div class="absolute bottom-0 w-full bg-black/30 backdrop-blur-sm p-4 flex flex-col space-y-2">
                        <div class="flex justify-between">
                            <h3 class="text-white font-semibold">{{ banner.title }}</h3>
                            <!-- Toggle -->
                            <Toogle :banner="banner" />
                        </div>
                        <!-- Action Buttons -->
                        <div class="flex gap-2 items-center mt-2">
                            <Edit :banner="banner" />
                            <Delete :banner="banner" />
                        </div>
                    </div>
                </div>
            </div>




            <!-- Pagination -->
            <div class="mt-6 flex justify-center space-x-2">
                <template v-for="link in banners.links" :key="link.label">
                    <!-- Clickable link -->
                    <Link v-if="link.url" :href="link.url" v-html="link.label" :class="[
                        'px-3 py-1 rounded border',
                        link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700'
                    ]" />

                    <!-- Disabled link (no URL) -->
                    <span v-else v-html="link.label"
                        class="px-3 py-1 rounded border bg-gray-200 text-gray-500 cursor-not-allowed" />
                </template>
            </div>


        </div>

    </AuthenticatedLayout>
</template>
