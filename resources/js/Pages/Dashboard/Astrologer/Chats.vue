<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import ChatWindow from './ChatWindow.vue'

const props = defineProps({
    user: Object,
    chats: Array,
    chat: Object,
    messages: Array,
    sessionReference: String,
})

const partnerName = (c) =>
    c.participants.find((p) => p.user_id !== props.user.id)?.user?.name ?? 'Unknown'
</script>

<template>

    <Head title="Chats" />
    <AstrologerLayout flush>
        <template #header>
            <h2 class="adm-title">Chats</h2>
        </template>

        <div class="ast-split">
            <!-- Chat list -->
            <aside class="ast-list" :class="{ 'has-active': chat }">
                <div class="ast-list-head">
                    <h3>Conversations</h3>
                    <span class="adm-chip">{{ chats.length }}</span>
                </div>

                <div v-if="!chats.length" class="adm-empty">No chats yet</div>

                <button v-for="c in chats" :key="c.id" type="button" class="ast-item"
                    :class="{ 'is-active': chat?.id === c.id }"
                    @click="$inertia.visit(route('astrologer.chats', c.id))">
                    <span class="adm-avatar ast-av">{{ partnerName(c).charAt(0).toUpperCase() }}</span>
                    <span class="ast-item-main">
                        <span class="adm-strong ast-trunc">{{ partnerName(c) }}</span>
                        <span class="adm-muted ast-trunc">{{ c.messages[0]?.message ?? 'No messages yet' }}</span>
                    </span>
                </button>
            </aside>

            <!-- Conversation -->
            <section class="ast-pane" :class="{ 'has-active': chat }">
                <Link v-if="chat" :href="route('astrologer.chats')" class="ast-back">
                    <Icon icon="mdi:arrow-left" width="18" height="18" /> All chats
                </Link>

                <div class="ast-pane-body">
                    <ChatWindow v-if="chat" :user="user" :chat="chat" :messages="messages"
                        :session-reference="sessionReference" />
                    <div v-else class="ast-pane-empty">
                        <Icon icon="mdi:chat-processing-outline" width="44" height="44" />
                        <span>Select a chat to start messaging</span>
                    </div>
                </div>
            </section>
        </div>
    </AstrologerLayout>
</template>