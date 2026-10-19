<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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
            <h2 class="adm-title">Banners</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Hover a banner to preview it; use the switch to show or hide it.</p>
            <Create />
        </div>

        <!-- Banner Grid -->
        <div class="adm-banner-grid">
            <div v-for="banner in banners.data" :key="banner.id" class="adm-banner">
                <!-- Background Image -->
                <div class="adm-banner-img" :style="`background-image: url(${banner.image})`"></div>

                <!-- Eye icon overlay -->
                <div class="adm-banner-eye">
                    <button type="button" @click="previewImage(banner.image)" aria-label="Preview">
                        <Icon icon="mdi:eye"></Icon>
                    </button>
                </div>

                <!-- Details -->
                <div class="adm-banner-info">
                    <div class="adm-banner-top">
                        <h3>{{ banner.title }}</h3>
                        <!-- Toggle -->
                        <Toogle :banner="banner" />
                    </div>
                    <!-- Action Buttons -->
                    <div class="adm-row">
                        <Edit :banner="banner" />
                        <Delete :banner="banner" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="adm-pagination">
            <template v-for="link in banners.links" :key="link.label">
                <!-- Clickable link -->
                <Link v-if="link.url" :href="link.url" v-html="link.label" class="adm-pg"
                    :class="{ 'is-active': link.active }" />

                <!-- Disabled link (no URL) -->
                <span v-else v-html="link.label" class="adm-pg is-disabled" />
            </template>
        </div>

    </AuthenticatedLayout>
</template>