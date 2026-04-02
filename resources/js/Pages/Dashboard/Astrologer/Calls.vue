<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import { Head } from '@inertiajs/vue3';
import CallWindow from './CallWindow.vue';

const props = defineProps({
    user: Object, // selected user (or null)
    chat: Object, // chat object between astrologer and user (or null)
    history: Array,
    astrologer: Object, // astrologer object
    calls: Array,
})
</script>

<template>
    <Head title="Calls" />
    <AstrologerLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-white border rounded shadow">
            <!-- Sidebar -->
            <div class="w-1/4 border-r overflow-y-auto">
                <div v-for="(call, index) in calls" :key="call.user_id" @click="$inertia.visit(route('astrologer.calls', call.user_id))"
                    class="p-4 cursor-pointer hover:bg-gray-300 border-b last:border-b-0"
                    :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-200'">
                    <p class="font-semibold">
                        {{ call.user?.name ?? 'Unknown' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Last: {{ call.created_at ? new Date(call.created_at).toLocaleString() : 'No calls yet' }}
                    </p>
                </div>
            </div>

            <!-- Main area -->
            <div class="flex-1">
                <CallWindow v-if="user && chat" :user="user" :astrologer="astrologer" :chat="chat" :history="history" />
                <div v-else class="flex h-full items-center justify-center text-gray-500">
                    Select a call to view details
                </div>
            </div>
        </div>
    </AstrologerLayout>
</template>
