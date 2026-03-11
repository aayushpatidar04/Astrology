<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '../Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';


const props = defineProps({
    blog: Object,
    user: Object,
    categories: Object
});

</script>
<template>

    <Head :title="props.blog.title" />

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


            <!-- Main Content: Blog -->
            <div class="md:col-span-3 space-y-8">

                <!-- First image -->
                <div v-if="blog.images && blog.images.length > 0" class="overflow-hidden rounded-lg">
                    <img :src="`/${blog.images[0].image}`" alt="Blog image"
                        class="w-2/3 h-auto mx-auto object-cover transform transition-transform duration-300 hover:scale-105" />
                </div>
                <br><br>
                <!-- Description 1 -->
                <div class="quill-content max-w-none" v-html="blog.description1"></div>
                <br>
                <!-- Remaining images -->
                <div v-if="blog.images && blog.images.length > 1" class="grid md:grid-cols-2 gap-6">
                    <div v-for="(img, index) in blog.images.slice(1)" :key="index" class="overflow-hidden rounded-lg">
                        <img :src="`/${img.image}`" alt="Blog image"
                            class="w-full h-64 object-cover transform transition-transform duration-300 hover:scale-105" />
                    </div>
                </div>
                <br>
                <!-- Description 2 -->
                <div v-if="blog.description2" class="quill-content max-w-none text-gray-700" v-html="blog.description2">
                </div>

                <!-- Tags -->
                <div v-if="blog.tags && blog.tags.length" class="flex flex-wrap gap-2 mt-6">
                    <span v-for="tag in blog.tags" :key="tag"
                        class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                        #{{ tag }}
                    </span>
                </div>
            </div>

        </div>
    </section>

    <Footer />
</template>

<style scoped>
/* Text blocks */
:deep(.quill-content p) {
    margin: 1em 0;
    line-height: 1.6;
}

:deep(.quill-content br) {
    display: block;
    margin-bottom: 0.5em;
}

/* Headings */
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

/* Lists */
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

/* Formatting */
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

/* Links */
:deep(.quill-content a) {
    color: #2563eb;
    /* Tailwind blue-600 */
    text-decoration: underline;
}

:deep(.quill-content a:hover) {
    color: #1e40af;
    /* Tailwind blue-800 */
}

/* Images */
:deep(.quill-content img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}

/* Blockquotes */
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
