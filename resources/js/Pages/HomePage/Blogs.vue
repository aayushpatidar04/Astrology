<script setup>
import dayjs from 'dayjs';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Reveal from '@/Components/Reveal.vue'

const props = defineProps({
    blogs: Object
})
</script>

<template>
    <section class="py-20 bg-cream">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Section header -->
            <Reveal class="text-center mb-12">
                <h2 class="section-heading section-heading-accent">Our Latest Blog</h2>
                <p class="mt-6 text-gray-600">
                    Stay updated with astrology insights, remedies, and guides.
                </p>
            </Reveal>

            <!-- Blog grid (latest 3 only) -->
            <div class="grid md:grid-cols-3 gap-8">
                <Reveal v-for="(blog, i) in props.blogs" :key="blog.id" :delay="i * 120" class="h-full">
                    <article
                        class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-orange-900/10">
                        <!-- Blog image -->
                        <div class="relative h-48 overflow-hidden">
                            <img :src="`/${blog.images[0]?.image}`" alt="Blog image"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <span
                                class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-primary shadow">
                                {{ dayjs(blog.created_at).format("D MMM, YYYY") }}
                            </span>
                        </div>

                        <!-- Blog content -->
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2 transition-colors group-hover:text-primary">
                                {{ blog.title }}
                            </h3>
                            <p class="text-gray-600 text-sm mb-4">
                                {{ blog.short_description }}
                            </p>

                            <Link :href="route('blog-details', { slug: blog.slug })"
                                class="mt-auto inline-flex items-center gap-1 font-medium text-primary hover:text-primary-dark">
                                Read More
                                <Icon icon="mdi:arrow-right" width="18"
                                    class="transition-transform duration-300 group-hover:translate-x-1" />
                            </Link>
                        </div>
                    </article>
                </Reveal>
            </div>

            <!-- Explore More button -->
            <Reveal class="text-center mt-12">
                <a :href="route('blog')" class="btn-primary !px-8 !py-3">
                    Explore More Blogs
                    <Icon icon="mdi:arrow-right" width="18" />
                </a>
            </Reveal>
        </div>
    </section>

</template>