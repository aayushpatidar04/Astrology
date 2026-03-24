<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import * as PusherPushNotifications from "@pusher/push-notifications-web";
import axios from 'axios';

const users = ref([])

const props = defineProps({
    user: Object,
});

const beamsClient = new PusherPushNotifications.Client({
    instanceId: import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID,
});

const beamsTokenProvider = new PusherPushNotifications.TokenProvider({
    url: '/beams-auth', // your Laravel route
});

onMounted(async () => {
    if (props.user?.id) {
        try {
            await beamsClient.start();
            await beamsClient.setUserId(String(props.user.id), beamsTokenProvider);
        } catch (err) {
            console.error('Beams setup failed', err);
        }
    }
});

</script>

<template>

    <Head title="Dashboard" />
    <AstrologerLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
            </div>

        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-5">
                    <div class="p-6 text-gray-900 text-xl font-bold">
                        Welcome, {{ $page.props.auth.user.name }}!
                    </div>
                </div>
            </div>
        </div>

    </AstrologerLayout>
</template>
