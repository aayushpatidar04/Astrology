<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import { Head } from '@inertiajs/vue3';
import ChatWindow from './ChatWindow.vue';

defineProps({
    user: Object,
    chats: Array,
    chat: Object,
    messages: Array,
})
</script>

<template>

    <Head title="Chats" />
    <AstrologerLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-white border rounded shadow">
            <!-- Sidebar -->
            <div class="w-1/4 border-r overflow-y-auto">
                <div v-for="(chat, index) in chats" :key="chat.id" @click="$inertia.visit(route('astrologer.chats', chat.id))"
                    class="p-4 cursor-pointer hover:bg-gray-300 border-b last:border-b-0"
                    :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-200'">
                    <p class="font-semibold">
                        {{chat.participants.find(p => p.user_id !== user.id)?.user?.name ?? 'Unknown'}}
                    </p>
                    <p class="text-sm text-gray-500">
                        Last: {{ chat.messages[0]?.message ?? 'No messages yet' }}
                    </p>
                </div>

            </div>

            <!-- Placeholder -->
            <div class="flex-1">
                <ChatWindow v-if="chat" :user="user" :chat="chat" :messages="messages" />
                <div v-else class="flex h-full items-center justify-center text-gray-500">
                    Select a chat to start messaging
                </div>
            </div>
        </div>
    </AstrologerLayout>
</template>
