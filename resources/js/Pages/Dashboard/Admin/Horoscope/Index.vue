<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Create from './Create.vue';

</script>

<template>

    <Head title="Horoscopes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Horoscopes
            </h2>
        </template>

        <div class="container p-4 mx-auto">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold"></h2>
                <Create />
            </div>
        </div>


    </AuthenticatedLayout>
</template>
