<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Pages/Layouts/Header.vue';
import Footer from '../Layouts/Footer.vue';
import { ref, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: Object,
    chatRatePerMin: Number,
});

const messages = ref([
    {
        sender: 'ai',
        text: 'Namaste! I am Acharya AI, your personal Vedic Astrologer. I am connected to your Janma Kundli and planetary transits. Ask me any question about your career, marriage, health, or life path. (Rate: ₹' + (props.chatRatePerMin ?? 15) + '/min)',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
]);

const inputMessage = ref('');
const loading = ref(false);
const walletBalance = ref(props.user?.wallet?.balance ?? 0.00);
const chatContainer = ref(null);
const errorMessage = ref('');

async function sendMessage() {
    if (!inputMessage.value.trim() || loading.value) return;

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
            message: userText
        });

        walletBalance.value = res.data.balance;

        messages.value.push({
            sender: 'ai',
            text: res.data.reply,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
    } catch (e) {
        if (e.response?.status === 402) {
            errorMessage.value = e.response.data.error;
            walletBalance.value = e.response.data.balance;
        } else {
            errorMessage.value = 'Failed to connect with Acharya AI. Please try again.';
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
</script>

<template>
    <Head title="Acharya AI - Paid Live Astrologer Chat" />
    <Header :user="user" />

    <section class="py-10 bg-gradient-to-b from-orange-50 to-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Card / Wallet Bar -->
            <div class="bg-white rounded-2xl shadow-md p-4 mb-6 border border-orange-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-black text-lg shadow">
                        🤖
                    </div>
                    <div>
                        <h1 class="text-lg font-black text-gray-900">Acharya AI (Paid Astrologer Chat)</h1>
                        <p class="text-xs text-gray-500">Instant Vedic Answers • Rate: ₹{{ chatRatePerMin ?? 15 }}/min</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 bg-orange-50 px-4 py-2 rounded-xl border border-orange-200">
                    <div class="text-xs">
                        <span class="text-gray-500 block">Wallet Balance</span>
                        <strong class="text-gray-900 text-base font-black">₹{{ Number(walletBalance).toFixed(2) }}</strong>
                    </div>
                    <a
                        href="/user/recharge"
                        class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow transition"
                    >
                        Recharge
                    </a>
                </div>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between">
                <span>{{ errorMessage }}</span>
                <a href="/user/recharge" class="underline font-bold text-red-900 shrink-0 ml-4">Add Balance</a>
            </div>

            <!-- Chat Box -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 flex flex-col h-[600px] overflow-hidden">
                <!-- Messages Area -->
                <div ref="chatContainer" class="flex-1 p-6 overflow-y-auto space-y-4 bg-gray-50/50">
                    <div
                        v-for="(msg, index) in messages"
                        :key="index"
                        class="flex"
                        :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[80%] rounded-2xl px-4 py-3 text-sm shadow-sm space-y-1"
                            :class="msg.sender === 'user'
                                ? 'bg-orange-600 text-white rounded-br-none'
                                : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none'"
                        >
                            <div class="leading-relaxed whitespace-pre-line">{{ msg.text }}</div>
                            <div
                                class="text-[10px] text-right"
                                :class="msg.sender === 'user' ? 'text-orange-200' : 'text-gray-400'"
                            >
                                {{ msg.time }}
                            </div>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div v-if="loading" class="flex justify-start">
                        <div class="bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3 shadow-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-600 animate-bounce"></span>
                            <span class="w-2 h-2 rounded-full bg-orange-600 animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-2 h-2 rounded-full bg-orange-600 animate-bounce [animation-delay:0.4s]"></span>
                            <span class="text-xs text-gray-500 ml-1 font-semibold">Acharya AI is consulting the planetary ephemeris...</span>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white border-t border-gray-200">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            type="text"
                            v-model="inputMessage"
                            placeholder="Ask your astrological question (e.g. When will I get married?)..."
                            :disabled="loading"
                            class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 bg-gray-50 focus:bg-white transition"
                        />
                        <button
                            type="submit"
                            :disabled="loading || !inputMessage.trim()"
                            class="bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold px-6 py-3 rounded-xl shadow transition text-sm flex items-center gap-2"
                        >
                            <span>Send</span>
                            <span>➤</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>
