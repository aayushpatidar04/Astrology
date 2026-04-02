<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'

const props = defineProps({
    auth: Object,
    chat: Object,
    messages: Array,
})

const newMessage = ref('')
const liveMessages = ref([...props.messages])
const messagesContainer = ref(null)
const typingUser = ref(null)
let typingTimeout = null

const astrologerOnline = ref(
    props.chat.participants.find(p => p.user_id !== props.auth.user.id)?.user?.astrologer?.online
)

const joined = ref(false)

// --- Countdown state ---
const userWalletBalance = ref(props.auth.user.wallet.balance ?? 0)
const astrologerRate = props.chat.participants.find(p => p.user_id !== props.auth.user.id)?.user?.astrologer?.charged_text_price ?? 0
const totalSeconds = Math.floor((userWalletBalance.value / astrologerRate) * 60)
const remainingSeconds = ref(totalSeconds)
const countdown = ref(null)

// Format H:m:s
function formatTime(seconds) {
    const h = Math.floor(seconds / 3600)
    const m = Math.floor((seconds % 3600) / 60)
    const s = seconds % 60
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

const chatEndReason = ref(null) // 'user', 'astrologer', 'balance'
const showEndModal = ref(false)

// Start countdown only when astrologer joins
function startCountdown() {
    if (countdown.value) clearInterval(countdown.value)
    countdown.value = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--
        } else {
            chatEndReason.value = 'balance'
            clearInterval(countdown.value)
            endChat()
        }
    }, 1000)
}

// End chat when time runs out or either leaves
async function endChat() {
    try {
        const usedSeconds = totalSeconds - remainingSeconds.value
        const usedMinutes = usedSeconds / 60
        const deduction = usedMinutes * astrologerRate

        await axios.post(route('user.chat.end', props.chat.id), {
            reason: chatEndReason.value,   // 'user', 'astrologer', 'balance'
            deduction: deduction           // exact fractional deduction
        })
    } catch (e) {
        console.error('Error ending chat:', e)
    }
    showEndModal.value = true
}


const sendMessage = async () => {
    if (!newMessage.value) return

    let oldMessage = newMessage.value
    newMessage.value = ''
    try {
        const response = await axios.post(route('user.chat.storeMessage', props.chat.id), {
            message: oldMessage
        })
        if (response.data.success) {
            liveMessages.value.push(response.data.message)
            scrollToBottom()
        }
    } catch (error) {
        console.error('Failed to send message:', error)
        newMessage.value = oldMessage
    }
}

const showWaitingModal = ref(false)

onMounted(async () => {
    await nextTick()
    scrollToBottom()

    // listen for astrologer status changes
    const astrologerId = props.chat.participants.find(p => p.user_id !== props.auth.user.id)?.user.id

    let currentMembers = []

    echo.join(`chat.${props.chat.id}`)
        .here((users) => {
            currentMembers = users
            if (users.length === 2) {
                startCountdown();
                axios.post(`/astrologers/${astrologerId}/busy`, { busy: true })
                joined.value = true;
                showWaitingModal.value = false
            }
        })
        .joining((user) => {
            currentMembers.push(user);
            if (currentMembers.length === 2) {
                startCountdown();
                axios.post(`/astrologers/${astrologerId}/busy`, { busy: true })
                joined.value = true;
                showWaitingModal.value = false
            }
        })
        .leaving((user) => {
            currentMembers = currentMembers.filter(u => u.id !== user.id)
            if (user.id === props.auth.user.id) {
                chatEndReason.value = 'user'
            } else {
                chatEndReason.value = 'astrologer'
                axios.post(`/astrologers/${astrologerId}/busy`, { busy: false })
            }
            clearInterval(countdown.value);
            endChat();
        })
        .listen('MessageSent', (e) => {
            if (props.auth.user.id != e.user_id) {
                liveMessages.value.push(e.message);
            }
            scrollToBottom();
        })
        .listen('TypingEvent', (e) => {
            typingUser.value = e.typing ? e.userId : null;
        });


    echo.private(`astrologer.${astrologerId}`)
        .listen('AstrologerStatusChanged', (e) => {
            astrologerOnline.value = e.online
        })
})

onUnmounted(() => {
    echo.leave(`chat.${props.chat.id}`)
})

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight - messagesContainer.value.clientHeight
    }
}

watch(liveMessages, async () => {
    await nextTick()
    scrollToBottom()
})

let isTyping = false

const handleTyping = () => {

    if (!isTyping) {
        axios.post(route('chat.typing', props.chat.id), { typing: true })
        isTyping = true
    }

    // Reset timeout each keystroke
    if (typingTimeout) clearTimeout(typingTimeout)

    typingTimeout = setTimeout(() => {
        // After 2s of no input, send false once
        axios.post(route('chat.typing', props.chat.id), { typing: false })
        isTyping = false
    }, 1000)
}

const messageInput = ref(null)

const autoResize = (event) => {
    const el = event.target
    el.style.height = 'auto' // reset
    const lineHeight = 24 // adjust to your CSS line-height
    const maxHeight = lineHeight * 5 // 5 rows max
    el.style.height = Math.min(el.scrollHeight, maxHeight) + 'px'
}

const startChat = async () => {
    try {
        showWaitingModal.value = true
        await axios.post(`/user/chats/${props.chat.id}/start`)
    } catch (err) {
        console.error(err)
        showWaitingModal.value = false
    }
}

</script>

<template>

    <Head :title="chat.participants.find(p => p.user_id !== auth.user.id)?.user.name" />
    <UserLayout>
        <div class="flex h-[calc(100vh-4rem)] items-center justify-center bg-gray-50 my-3">
            <div class="flex flex-col w-full max-w-4xl h-full bg-white border rounded-lg shadow-lg">
                <!-- Header -->
                <div class="border-b p-4 flex items-center justify-between bg-gray-100">
                    <h2 class="font-semibold text-gray-800">
                        Chat with {{chat.participants.find(p => p.user_id !== auth.user.id)?.user.name}}
                        <span class="text-sm ml-5" :class="astrologerOnline ? 'text-green-500' : 'text-gray-400'">
                            {{ astrologerOnline ? 'Online' : 'Offline' }}<span class="text-green-500" v-if="joined"> |
                                Joined</span>
                        </span>

                    </h2>
                    <span class="text-sm font-mono text-orange-600">
                        Time Remaining<br>
                        {{ formatTime(remainingSeconds) }}
                    </span>
                </div>

                <!-- Messages -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-1 bg-gray-50">
                    <div v-for="msg in liveMessages" :key="msg.id"
                        :class="msg.user_id === auth.user.id ? 'text-right' : 'text-left'">
                        <div class="group inline-block">
                            <div :class="msg.user_id === auth.user.id
                                ? 'px-4 py-2 rounded-lg bg-orange-500 text-white'
                                : 'px-4 py-2 rounded-lg bg-gray-200 text-gray-800'" style="white-space: pre-line;">
                                {{ msg.message }}
                            </div>
                            <!-- timestamp hidden until hover -->
                            <div class="text-xs text-gray-400 mt-1 hidden group-hover:block transition"
                                :class="msg.user_id === auth.user.id ? 'text-right' : 'text-left'">
                                {{ dayjs(msg.created_at).format('h:mm A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="typingUser && typingUser !== auth.user.id" class="text-sm text-gray-500 px-5 pb-2">
                    {{chat.participants.find(p => p.user_id === typingUser)?.user.name}} is typing...
                </div>


                <!-- Input -->
                <form @submit.prevent="sendMessage" class="border-t p-4 flex items-end bg-gray-100">
                    <textarea v-model="newMessage" @input="autoResize($event); handleTyping()"
                        @keydown.enter.shift.exact.prevent="newMessage += '\n'"
                        @keydown.enter.exact.prevent="sendMessage()"
                        class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                        placeholder="Type a message..." rows="1" ref="messageInput" :readonly="astrologerOnline" />

                    <button type="submit" :disabled="!astrologerOnline" v-if="joined"
                        class="ml-2 px-6 py-2 bg-orange-500 text-white rounded-full transition"
                        :class="!astrologerOnline ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600'"
                        :title="!astrologerOnline ? 'Astrologer is offline. You cannot send a message.' : ''">
                        Send
                    </button>
                    <!-- Start Chat Button -->
                    <button type="button" @click="startChat" v-if="!joined"
                        class="ml-2 px-6 py-2 bg-blue-500 text-white rounded-full transition hover:bg-blue-600">
                        Start Chat
                    </button>
                </form>

                <div v-if="!astrologerOnline" class="text-sm text-red-500 my-2 px-5">
                    Astrologer is offline. You cannot send a message.
                </div>

            </div>
        </div>

        <div v-if="showEndModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
                <h3 class="text-lg font-semibold mb-4">Chat Ended</h3>
                <p v-if="chatEndReason === 'balance'" class="text-gray-700">
                    Your balance is over. Chat ended.
                </p>
                <p v-else-if="chatEndReason === 'user'" class="text-gray-700">
                    You left the chat.
                </p>
                <p v-else-if="chatEndReason === 'astrologer'" class="text-gray-700">
                    The astrologer left the chat.
                </p>
                <div class="mt-4">
                    <Link :href="route('user.chat-with-astrologers')"
                        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                        Back to Astrologers
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="showWaitingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
                <h3 class="text-lg font-semibold mb-4">Waiting for Astrologer</h3>
                <p class="text-gray-700">
                    Please wait, the astrologer has not joined yet...
                </p>
                <div class="mt-4">
                    <Link :href="route('user.chat-with-astrologers')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </Link>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
