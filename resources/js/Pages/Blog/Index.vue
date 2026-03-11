<script setup>
import { Link, Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import dayjs from 'dayjs';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    user: Object,
    blogs: Object,
    categories: Object
})
</script>
<template>

    <Head title="Blogs" />

    <Header :user="user" />

    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-8">

            <aside class="md:col-span-1 bg-white rounded-lg shadow-lg p-5">
                <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2">Categories</h3>
                <ul class="space-y-2">
                    <li v-for="category in categories" :key="category.id">
                        <a :href="route('category-blog', category.slug)" class="flex items-center gap-2 px-3 py-2 rounded-lg transition
                text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                            <span class="font-medium">{{ category.name }}</span>
                        </a>
                    </li>
                </ul>
            </aside>


            <!-- Main Content: Blogs -->
            <div class="md:col-span-3">
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="blog in props.blogs.data" :key="blog.id"
                        class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <!-- Blog image -->
                        <img :src="`/${blog.images[0]?.image}`" alt="Blog image"
                            class="h-48 w-full object-cover rounded-t-lg transform transition-transform duration-300 hover:scale-105" />

                        <!-- Blog content -->
                        <div class="p-5">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <span>{{ dayjs(blog.created_at).format("D MMM, YYYY") }}</span>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                {{ blog.title }}
                            </h3>

                            <Link :href="route('blog-details', {slug:blog.slug})" class="inline-block text-blue-600 font-medium hover:underline">
                                Read More →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="blogs.data.length > 0" class="mt-8 flex justify-center">
                    <Pagination :links="blogs.links" />
                </div>
            </div>
        </div>
    </section>


    <Footer />
</template>