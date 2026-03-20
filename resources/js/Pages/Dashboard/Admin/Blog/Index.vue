<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { VueGoodTable } from "vue-good-table-next";
import "vue-good-table-next/dist/vue-good-table-next.css";
import Create from './Create.vue';
import Edit from './Edit.vue';
import dayjs from "dayjs";
import Delete from './Delete.vue';

const props = defineProps({
    blogs: Object,
    categories: Object
})

const columns = [
    { label: "Title", field: "title" },
    { label: "Category", field: "category" },
    { label: "Tags", field: "tags" },
    { label: "Publish Date", field: "created_at" },
    { label: "Actions", field: "actions", sortable: false },
]

</script>

<template>

    <Head title="Blogs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Blogs
            </h2>
        </template>
        <div class="container p-4 mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold"></h2>
                <Create :categories="categories" />
            </div>

            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="blogs" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'title'">
                        <div>
                            <div class="font-semibold">{{ props.row.title }}</div>
                            <div class="text-sm text-gray-500 lowercase">{{ props.row.slug }}</div>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'category'">
                        {{ props.row.category?.name }}
                    </span>
                    <span v-else-if="props.column.field === 'tags'">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="tag in props.row.tags" :key="tag"
                                class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs">
                                {{ tag }}
                            </span>
                        </div>
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="flex">
                        <Link :href="route('blog-details', { slug: props.row.slug })"><button class="px-2 py-1 bg-orange-500 text-white rounded mr-2 mt-1">
                            View
                        </button></Link>
                        <Edit :categories="categories" :blog="props.row" />
                        <Delete :blog="props.row" />
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>

        </div>

    </AuthenticatedLayout>
</template>


<style>
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}
</style>