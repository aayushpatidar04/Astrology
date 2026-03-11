<script setup>
import dayjs from 'dayjs';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    blogs: Object
})
</script>

<template>
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Section header -->
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800">Our Latest Blog</h2>
                <p class="mt-2 text-gray-600">
                    Stay updated with astrology insights, remedies, and guides.
                </p>
            </div>

            <!-- Blog grid (latest 3 only) -->
            <div class="grid md:grid-cols-3 gap-8">
                <div v-for="blog in props.blogs" :key="blog.id"
                    class="bg-white rounded-lg shadow hover:shadow-lg transition">
                    <!-- Blog image -->
                    <img :src="`/${blog.images[0]?.image}`" alt="Blog image"
                        class="h-48 w-full object-cover rounded-t-lg" />

                    <!-- Blog content -->
                    <div class="p-5">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span>{{ dayjs(blog.created_at).format("D MMM, YYYY") }}</span>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            {{ blog.title }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            {{ blog.short_description }}
                        </p>

                        <Link :href="route('blog-details', { slug: blog.slug })"
                            class="inline-block text-blue-600 font-medium hover:underline">
                        Read More →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Explore More button -->
            <div class="text-center mt-10">
                <a :href="route('blog')"
                    class="inline-block px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg shadow hover:bg-orange-600 transition">
                    Explore More Blogs
                </a>
            </div>
        </div>
    </section>

</template>