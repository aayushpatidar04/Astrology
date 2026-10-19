<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
            <h2 class="adm-title">Blogs</h2>
        </template>

        <div class="adm-toolbar">
            <p class="adm-sub">Write and manage the articles shown on the website.</p>
            <Create :categories="categories" />
        </div>

        <div class="adm-card adm-table">
            <!-- DataTable -->
            <VueGoodTable :columns="columns" :rows="blogs" :paginate="true" :search-options="{ enabled: true }"
                :lineNumbers="true" :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'title'">
                        <span class="adm-strong">{{ props.row.title }}</span>
                        <span class="adm-muted adm-lc">{{ props.row.slug }}</span>
                    </span>
                    <span v-else-if="props.column.field === 'category'">
                        <span v-if="props.row.category?.name" class="adm-badge adm-badge-blue">
                            {{ props.row.category?.name }}
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'tags'">
                        <span class="adm-row">
                            <span v-for="tag in props.row.tags" :key="tag" class="adm-chip">
                                {{ tag }}
                            </span>
                        </span>
                    </span>
                    <span v-else-if="props.column.field === 'created_at'">
                        {{ dayjs(props.row.created_at).format("D MMM, YYYY HH:mm:ss") }}
                    </span>
                    <span v-else-if="props.column.field === 'actions'" class="adm-row">
                        <Link :href="route('blog-details', { slug: props.row.slug })" class="adm-icon-btn adm-icon-add"
                            title="View">
                            <Icon icon="mdi:eye-outline" />
                        </Link>
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