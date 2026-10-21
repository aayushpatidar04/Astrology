<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import CallWindow from './CallWindow.vue'

const props = defineProps({
    user: Object, // selected user (or null)
    chat: Object, // chat object between astrologer and user (or null)
    history: Array,
    astrologer: Object, // astrologer object
    calls: Array,
})

const active = () => !!(props.user && props.chat)
</script>

<template>
    <Head title="Calls" />
    <AstrologerLayout flush>
        <template #header>
            <h2 class="adm-title">Calls</h2>
        </template>

        <div class="ast-split">
            <!-- Call list -->
            <aside class="ast-list" :class="{ 'has-active': active() }">
                <div class="ast-list-head">
                    <h3>Recent calls</h3>
                    <span class="adm-chip">{{ calls.length }}</span>
                </div>

                <div v-if="!calls.length" class="adm-empty">No calls yet</div>

                <button v-for="call in calls" :key="call.user_id" type="button" class="ast-item"
                    :class="{ 'is-active': user?.id === call.user_id }"
                    @click="$inertia.visit(route('astrologer.calls', call.user_id))">
                    <span class="adm-avatar ast-av">{{ (call.user?.name ?? 'U').charAt(0).toUpperCase() }}</span>
                    <span class="ast-item-main">
                        <span class="adm-strong ast-trunc">{{ call.user?.name ?? 'Unknown' }}</span>
                        <span class="adm-muted ast-trunc">
                            {{ call.created_at ? new Date(call.created_at).toLocaleString() : 'No calls yet' }}
                        </span>
                    </span>
                </button>
            </aside>

            <!-- Call details -->
            <section class="ast-pane" :class="{ 'has-active': active() }">
                <Link v-if="active()" :href="route('astrologer.calls')" class="ast-back">
                    <Icon icon="mdi:arrow-left" width="18" height="18" /> All calls
                </Link>

                <div class="ast-pane-body">
                    <CallWindow v-if="active()" :user="user" :astrologer="astrologer" :chat="chat" :history="history" />
                    <div v-else class="ast-pane-empty">
                        <Icon icon="mdi:phone-outline" width="44" height="44" />
                        <span>Select a call to view details</span>
                    </div>
                </div>
            </section>
        </div>
    </AstrologerLayout>
</template>