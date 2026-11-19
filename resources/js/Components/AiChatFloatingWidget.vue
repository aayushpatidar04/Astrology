<script setup>
import { ref, nextTick, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
});

const isOpen = ref(false);
const chatId = ref(null);
const sessionReference = ref(null);
const messages = ref([]);
const inputMessage = ref('');
const loading = ref(false);
const walletBalance = ref(props.user?.wallet?.balance ?? 0.00);
const chatContainer = ref(null);
const errorMessage = ref('');

const chatRatePerMin = 5.00;
let timerInterval = null;
let elapsedSeconds = 0;

async function toggleChat() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await initChatSession();
        scrollToBottom();
        startTimer();
    } else {
        stopTimer();
    }
}

async function initChatSession() {
    try {
        const res = await axios.get(route('ai.astrologer.init'));
        chatId.value = res.data.chat_id;
        sessionReference.value = res.data.session_reference;
        messages.value = res.data.messages.map(m => ({
            sender: m.user_id === props.user.id ? 'user' : 'ai',
            text: m.message,
            time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        }));
        walletBalance.value = res.data.balance;
    } catch (e) {
        errorMessage.value = 'Failed to initialize chat session.';
    }
}

function startTimer() {
    stopTimer();
    elapsedSeconds = 0;
    timerInterval = setInterval(async () => {
        if (!isOpen.value || !chatId.value) return;
        elapsedSeconds += 15; // settle every 15 seconds for smooth billing

        try {
            const res = await axios.post(route('ai.astrologer.settle'), {
                chat_id: chatId.value,
                elapsed_seconds: 15
            });
            walletBalance.value = res.data.balance;
            if (walletBalance.value < (chatRatePerMin / 4)) {
                errorMessage.value = 'Low wallet balance. Please recharge!';
            }
        } catch (e) {
            console.error('Settlement ping failed', e);
        }
    }, 15000);
}

function stopTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
}

async function sendMessage() {
    if (!inputMessage.value.trim() || loading.value || !chatId.value) return;

    const userText = inputMessage.value.trim();
    inputMessage.value = '';
    errorMessage.value = '';

    messages.value.push({
        sender: 'user',
        text: userText,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    });

    scrollToBottom();
    loading.value = true;

    try {
        const res = await axios.post(route('ai.astrologer.send'), {
            chat_id: chatId.value,
            message: userText
        });

        walletBalance.value = res.data.balance;

        messages.value.push({
            sender: 'ai',
            text: res.data.message.message,
            time: new Date(res.data.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
    } catch (e) {
        if (e.response?.status === 402) {
            errorMessage.value = e.response.data.error;
            walletBalance.value = e.response.data.balance;
        } else {
            errorMessage.value = 'Failed to connect with Acharya AI.';
        }
    } finally {
        loading.value = false;
        scrollToBottom();
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

onUnmounted(() => {
    stopTimer();
});
</script>

<template>
    <div v-if="user" class="fixed bottom-6 right-6 z-50">
        <!-- Floating Trigger Button -->
        <button
            v-if="!isOpen"
            @click="toggleChat"
            class="relative group flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-tr from-orange-600 to-amber-500 text-white shadow-2xl hover:scale-110 transition duration-300 focus:outline-none"
            title="Chat with Acharya AI Astrologer"
        >
            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-2xl">🤖</span>
        </button>

        <!-- Floating Chat Modal Window -->
        <div
            v-if="isOpen"
            class="w-80 sm:w-96 h-[520px] bg-white rounded-2xl shadow-2xl border border-orange-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300"
        >
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-orange-600 to-amber-600 text-white p-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-base">
                        🤖
                    </div>
                    <div>
                        <div class="font-black text-sm">Acharya AI Astrologer</div>
                        <div class="text-[10px] text-orange-100">Wallet: ₹{{ Number(walletBalance).toFixed(2) }} (₹5/min)</div>
                    </div>
                </div>
                <button
                    @click="toggleChat"
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white font-bold transition"
                >
                    ✕
                </button>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="p-2.5 bg-red-50 border-b border-red-200 text-red-800 text-xs flex items-center justify-between">
                <span class="truncate">{{ errorMessage }}</span>
                <a href="/user/recharge" class="underline font-bold text-red-900 shrink-0 ml-2">Recharge</a>
            </div>

            <!-- Messages List -->
            <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50/60 text-xs">
                <div
                    v-for="(msg, index) in messages"
                    :key="index"
                    class="flex"
                    :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[85%] rounded-xl px-3.5 py-2.5 shadow-sm space-y-1"
                        :class="msg.sender === 'user'
                            ? 'bg-orange-600 text-white rounded-br-none'
                            : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none'"
                    >
                        <div class="leading-relaxed whitespace-pre-line">{{ msg.text }}</div>
                        <div
                            class="text-[9px] text-right"
                            :class="msg.sender === 'user' ? 'text-orange-200' : 'text-gray-400'"
                        >
                            {{ msg.time }}
                        </div>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div v-if="loading" class="flex justify-start">
                    <div class="bg-white text-gray-800 border border-gray-200 rounded-xl rounded-bl-none px-3.5 py-2.5 shadow-sm flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-bounce [animation-delay:0.4s]"></span>
                        <span class="text-[10px] text-gray-500 ml-1 font-semibold">Acharya AI is analyzing chart...</span>
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <div class="p-3 bg-white border-t border-gray-200">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input
                        type="text"
                        v-model="inputMessage"
                        placeholder="Ask astrological question..."
                        :disabled="loading"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-orange-500 bg-gray-50 focus:bg-white transition"
                    />
                    <button
                        type="submit"
                        :disabled="loading || !inputMessage.trim()"
                        class="bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold px-4 py-2 rounded-lg shadow transition text-xs"
                    >
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
