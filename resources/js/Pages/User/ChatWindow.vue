<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'

const props = defineProps({
    auth: Object,
    chat: Object,
    messages: Array,
    sessionReference: String,
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
const summary = ref({ seconds: 0, charged: 0 })

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

        summary.value = { seconds: usedSeconds, charged: deduction }

        await axios.post(route('user.chat.end', props.chat.id), {
            reason: chatEndReason.value,   // 'user', 'astrologer', 'balance'
            deduction: deduction,          // exact fractional deduction
            session_reference: props.sessionReference,
        })
    } catch (e) {
        console.error('Error ending chat:', e)
    }
    showEndModal.value = true
}


const sendAnim = ref(false)

const sendMessage = async () => {
    if (!newMessage.value) return

    sendAnim.value = true
    setTimeout(() => (sendAnim.value = false), 550)

    let oldMessage = newMessage.value
    newMessage.value = ''
    if (messageInput.value) messageInput.value.style.height = 'auto'
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
const startingChat = ref(false)
const startChatError = ref('')

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
}, { deep: true })

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
    if (startingChat.value) return

    try {
        startingChat.value = true
        startChatError.value = ''
        showWaitingModal.value = true
        await axios.post(`/user/chats/${props.chat.id}/start`)
    } catch (err) {
        console.error(err)
        showWaitingModal.value = false
        startChatError.value = err.response?.data?.message ?? 'Unable to start the chat. Please try again.'
    } finally {
        startingChat.value = false
    }
}

// ---------- presentation helpers (no behaviour change) ----------
const astrologer = computed(() => props.chat.participants.find(p => p.user_id !== props.auth.user.id)?.user)
const astrologerName = computed(() => astrologer.value?.name ?? 'Astrologer')
const avatarSrc = computed(() => {
    const img = astrologer.value?.astrologer?.profile_image
    if (!img) return null
    return /^(https?:)?\//.test(img) ? img : `/${img}`
})

const typingName = computed(() => props.chat.participants.find(p => p.user_id === typingUser.value)?.user.name)

// round countdown ring
const RING = 2 * Math.PI * 20
const ringProgress = computed(() => {
    if (!isFinite(totalSeconds) || totalSeconds <= 0) return 0
    return Math.max(0, Math.min(1, remainingSeconds.value / totalSeconds))
})
const lowBalance = computed(() => joined.value && remainingSeconds.value > 0 && remainingSeconds.value <= 120)

const dayLabel = (d) => {
    const x = dayjs(d)
    if (x.isSame(dayjs(), 'day')) return 'Today'
    if (x.isSame(dayjs().subtract(1, 'day'), 'day')) return 'Yesterday'
    return x.format('D MMM YYYY')
}

const timeline = computed(() =>
    liveMessages.value.map((m, i, arr) => ({
        ...m,
        mine: m.user_id === props.auth.user.id,
        label: i === 0 || !dayjs(m.created_at).isSame(arr[i - 1].created_at, 'day') ? dayLabel(m.created_at) : null,
    }))
)

const mmss = (s) => `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(Math.floor(s % 60)).padStart(2, '0')}`
</script>

<template>

    <Head :title="astrologerName" />
    <UserLayout immersive>
        <div class="chat-stage">
            <div class="chat-shell">
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-who">
                        <div class="chat-avatar" :class="{ 'is-online': astrologerOnline }">
                            <img v-if="avatarSrc" :src="avatarSrc" :alt="astrologerName" />
                            <span v-else>{{ astrologerName.charAt(0).toUpperCase() }}</span>
                            <i class="chat-presence"></i>
                        </div>
                        <div class="chat-who-text">
                            <h2>{{ astrologerName }}</h2>
                            <p>
                                <span :class="astrologerOnline ? 'is-on' : 'is-off'">
                                    {{ astrologerOnline ? 'Online' : 'Offline' }}
                                </span>
                                <span v-if="joined" class="chat-joined"><Icon icon="mdi:check-all" width="14" height="14" /> Joined</span>
                                <span v-if="astrologerRate" class="chat-rate">₹{{ astrologerRate }}/min</span>
                            </p>
                        </div>
                    </div>

                    <div class="chat-timer" :class="{ 'is-low': lowBalance, 'is-live': joined }">
                        <svg viewBox="0 0 48 48" width="46" height="46">
                            <circle cx="24" cy="24" r="20" fill="none" stroke="#ffe8d4" stroke-width="4" />
                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="4"
                                stroke-linecap="round" :stroke-dasharray="RING"
                                :stroke-dashoffset="RING * (1 - ringProgress)" transform="rotate(-90 24 24)"
                                class="chat-ring" />
                        </svg>
                        <div>
                            <span>Time remaining</span>
                            <b>{{ formatTime(remainingSeconds) }}</b>
                        </div>
                    </div>
                </div>

                <!-- Status strips -->
                <Transition name="strip">
                    <div v-if="lowBalance" class="chat-strip is-warn">
                        <Icon icon="mdi:alert-circle-outline" width="18" height="18" />
                        Low balance — about {{ Math.max(1, Math.ceil(remainingSeconds / 60)) }} min left.
                        <Link :href="route('user.recharge')">Recharge now</Link>
                    </div>
                </Transition>
                <Transition name="strip">
                    <div v-if="!astrologerOnline" class="chat-strip is-off">
                        <Icon icon="mdi:cloud-off-outline" width="18" height="18" />
                        Astrologer is offline. You cannot send a message.
                    </div>
                </Transition>
                <Transition name="strip">
                    <div v-if="startChatError" class="chat-strip is-off" role="alert">
                        <Icon icon="mdi:alert-circle-outline" width="18" height="18" />
                        {{ startChatError }}
                    </div>
                </Transition>

                <!-- Messages -->
                <div ref="messagesContainer" class="chat-body">
                    <!-- empty state -->
                    <div v-if="!timeline.length" class="chat-empty">
                        <div class="chat-empty-art">
                            <img src="/images/zodiac-wheel.png" alt="" />
                        </div>
                        <h3>{{ joined ? 'Say hello 👋' : 'Ready when you are' }}</h3>
                        <p v-if="!joined">
                            Your session starts as soon as {{ astrologerName }} joins.
                            <span v-if="astrologerRate">You'll be charged ₹{{ astrologerRate }} per minute.</span>
                        </p>
                        <p v-else>Ask anything about love, career, health or destiny.</p>
                    </div>

                    <TransitionGroup name="msg" tag="div" class="chat-list">
                        <template v-for="msg in timeline" :key="msg.id">
                            <div v-if="msg.label" :key="`d-${msg.id}`" class="chat-day"><span>{{ msg.label }}</span></div>
                            <div class="chat-row" :class="msg.mine ? 'is-mine' : 'is-theirs'">
                                <div class="chat-bubble" style="white-space: pre-line">{{ msg.message }}</div>
                                <span class="chat-time">
                                    {{ dayjs(msg.created_at).format('h:mm A') }}
                                    <Icon v-if="msg.mine" icon="mdi:check-all" width="14" height="14" />
                                </span>
                            </div>
                        </template>
                    </TransitionGroup>

                    <!-- typing -->
                    <Transition name="typing">
                        <div v-if="typingUser && typingUser !== auth.user.id" class="chat-row is-theirs">
                            <div class="chat-bubble chat-typing">
                                <span class="chat-dot"></span><span class="chat-dot"></span><span class="chat-dot"></span>
                            </div>
                            <span class="chat-time">{{ typingName }} is typing…</span>
                        </div>
                    </Transition>
                </div>

                <!-- Input -->
                <form @submit.prevent="sendMessage" class="chat-input">
                    <textarea v-model="newMessage" @input="autoResize($event); handleTyping()"
                        @keydown.enter.shift.exact.prevent="newMessage += '\n'"
                        @keydown.enter.exact.prevent="sendMessage()" class="chat-textarea"
                        :placeholder="joined ? 'Type a message…' : 'Start the chat to send a message'" rows="1"
                        ref="messageInput" :readonly="!joined" />

                    <button type="submit" :disabled="!astrologerOnline || !newMessage.trim()" v-if="joined"
                        class="chat-send" :class="{ 'is-flying': sendAnim }"
                        :title="!astrologerOnline ? 'Astrologer is offline. You cannot send a message.' : 'Send'">
                        <Icon icon="mdi:send" width="22" height="22" />
                    </button>

                    <!-- Start Chat Button -->
                    <button type="button" @click="startChat" v-if="!joined" class="chat-start"
                        :disabled="startingChat || !astrologerOnline">
                        <span class="chat-start-ring"></span>
                        <Icon v-if="startingChat" icon="mdi:loading" width="20" height="20" class="chat-start-spin" />
                        <Icon v-else icon="mdi:chat-plus-outline" width="20" height="20" />
                        {{ startingChat ? 'Requesting…' : 'Start Chat' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- End modal -->
        <Transition name="usr-modal">
            <div v-if="showEndModal" class="usr-overlay">
                <div class="usr-modal">
                    <div class="usr-modal-icon" :class="chatEndReason === 'balance' ? 'is-red' : 'is-blue'">
                        <Icon :icon="chatEndReason === 'balance' ? 'mdi:wallet-outline' : 'mdi:chat-sleep-outline'" />
                    </div>
                    <h3>Chat Ended</h3>
                    <p v-if="chatEndReason === 'balance'">
                        Your balance is over. Chat ended.
                    </p>
                    <p v-else-if="chatEndReason === 'user'">
                        You left the chat.
                    </p>
                    <p v-else-if="chatEndReason === 'astrologer'">
                        The astrologer left the chat.
                    </p>

                    <div class="usr-modal-stats">
                        <div><strong>{{ mmss(summary.seconds) }}</strong><span>Duration</span></div>
                        <div><strong>₹{{ summary.charged.toFixed(2) }}</strong><span>Charged</span></div>
                    </div>

                    <div class="usr-modal-actions">
                        <Link :href="route('user.chat-with-astrologers')" class="usr-btn">
                            Back to Astrologers
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Waiting modal -->
        <Transition name="usr-modal">
            <div v-if="showWaitingModal" class="usr-overlay">
                <div class="usr-modal">
                    <div class="chat-wait">
                        <span class="chat-wait-ring r1"></span>
                        <span class="chat-wait-ring r2"></span>
                        <span class="chat-wait-ring r3"></span>
                        <div class="chat-wait-avatar">
                            <img v-if="avatarSrc" :src="avatarSrc" :alt="astrologerName" />
                            <span v-else>{{ astrologerName.charAt(0).toUpperCase() }}</span>
                        </div>
                    </div>
                    <h3>Waiting for {{ astrologerName }}</h3>
                    <p>
                        Please wait, the astrologer has not joined yet
                        <span class="chat-ellipsis"><i>.</i><i>.</i><i>.</i></span>
                    </p>
                    <div class="usr-modal-actions">
                        <Link :href="route('user.chat-with-astrologers')" class="usr-btn usr-btn-ghost">
                            Cancel
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </UserLayout>
</template>

<style scoped>
.chat-stage {
    display: flex;
    height: 100%;
    justify-content: center;
    padding: 0.75rem;
}

.chat-shell {
    display: flex;
    width: 100%;
    max-width: 56rem;
    height: 100%;
    min-height: 0;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #ffe8d4;
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 30px 60px -30px rgba(124, 45, 18, 0.35);
    animation: usr-rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

/* ---- header ---- */
.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid #ffe8d4;
    background: linear-gradient(90deg, #fff7ed, #fff);
}

.chat-who {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 0.8rem;
}

.chat-avatar {
    position: relative;
    display: flex;
    width: 3rem;
    height: 3rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    font-size: 1.2rem;
    font-weight: 700;
    color: #fff;
}

.chat-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 9999px;
    object-fit: cover;
}

.chat-presence {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 0.85rem;
    height: 0.85rem;
    border: 2px solid #fff;
    border-radius: 9999px;
    background: #9ca3af;
}

.chat-avatar.is-online .chat-presence {
    background: #22c55e;
    animation: chat-presence 2s ease-out infinite;
}

.chat-who-text {
    min-width: 0;
}

.chat-who-text h2 {
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.chat-who-text p {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.7rem;
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
}

.chat-who-text .is-on { color: #16a34a; font-weight: 600; }
.chat-who-text .is-off { color: #9ca3af; font-weight: 600; }

.chat-joined {
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    color: #16a34a;
    font-weight: 600;
}

.chat-rate {
    padding: 0.05rem 0.5rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-weight: 600;
    color: #c2410c;
}

.chat-timer {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    gap: 0.6rem;
    color: #FF7010;
}

.chat-timer div {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.chat-timer span {
    font-size: 0.68rem;
    font-weight: 600;
    color: #9ca3af;
}

.chat-timer b {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 1rem;
    color: #1f2937;
}

.chat-ring {
    transition: stroke-dashoffset 1s linear, color 0.3s ease;
}

.chat-timer.is-low {
    color: #dc2626;
    animation: chat-throb 1s ease-in-out infinite;
}

.chat-timer.is-low b {
    color: #dc2626;
}

/* ---- strips ---- */
.chat-strip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.1rem;
    font-size: 0.82rem;
    font-weight: 500;
}

.chat-strip a {
    margin-left: auto;
    font-weight: 700;
    color: inherit;
    text-decoration: underline;
}

.chat-strip.is-warn { background: #fef3c7; color: #92400e; }
.chat-strip.is-off { background: #fee2e2; color: #b91c1c; }

.strip-enter-active,
.strip-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.strip-enter-from,
.strip-leave-to {
    opacity: 0;
    transform: translateY(-100%);
}

/* ---- body ---- */
.chat-body {
    position: relative;
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 1.1rem 1.1rem 0.5rem;
    scroll-behavior: smooth;
    background-color: #fffaf5;
    background-image:
        radial-gradient(circle at 12% 18%, rgba(255, 112, 16, 0.07) 0, transparent 22%),
        radial-gradient(circle at 88% 82%, rgba(252, 211, 77, 0.12) 0, transparent 26%);
}

.chat-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.chat-day {
    display: flex;
    justify-content: center;
    margin: 0.75rem 0 0.4rem;
}

.chat-day span {
    padding: 0.2rem 0.8rem;
    border-radius: 9999px;
    background: #fff;
    border: 1px solid #ffe8d4;
    font-size: 0.72rem;
    font-weight: 600;
    color: #9ca3af;
}

.chat-row {
    display: flex;
    max-width: 78%;
    flex-direction: column;
    margin-bottom: 0.35rem;
}

.chat-row.is-mine {
    align-self: flex-end;
    align-items: flex-end;
}

.chat-row.is-theirs {
    align-self: flex-start;
    align-items: flex-start;
}

.chat-bubble {
    padding: 0.6rem 0.95rem;
    border-radius: 1.1rem;
    font-size: 0.95rem;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.is-mine .chat-bubble {
    border-bottom-right-radius: 0.3rem;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    color: #fff;
    box-shadow: 0 6px 14px -6px rgba(255, 112, 16, 0.6);
}

.is-theirs .chat-bubble {
    border: 1px solid #ffe8d4;
    border-bottom-left-radius: 0.3rem;
    background: #fff;
    color: #1f2937;
    box-shadow: 0 6px 14px -8px rgba(124, 45, 18, 0.25);
}

.chat-time {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    margin-top: 0.2rem;
    padding: 0 0.25rem;
    font-size: 0.68rem;
    color: #b0b7c3;
}

.is-mine .chat-time svg {
    color: #22c55e;
}

/* message enter animation */
.msg-enter-active {
    transition: opacity 0.4s ease, transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.msg-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.9);
}

.msg-enter-from.is-mine { transform-origin: bottom right; }
.msg-enter-from.is-theirs { transform-origin: bottom left; }

.msg-move {
    transition: transform 0.3s ease;
}

/* typing dots */
.chat-typing {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.8rem 1rem;
}

.chat-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background: #FF9248;
    animation: chat-bounce 1.2s ease-in-out infinite;
}

.chat-dot:nth-child(2) { animation-delay: 0.15s; }
.chat-dot:nth-child(3) { animation-delay: 0.3s; }

.typing-enter-active,
.typing-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.typing-enter-from,
.typing-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

/* empty state */
.chat-empty {
    display: flex;
    height: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    text-align: center;
}

.chat-empty-art img {
    width: 9rem;
    opacity: 0.18;
    animation: usr-spin 50s linear infinite;
}

.chat-empty h3 {
    margin: 1rem 0 0.35rem;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
}

.chat-empty p {
    max-width: 22rem;
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}

/* ---- input ---- */
.chat-input {
    display: flex;
    align-items: flex-end;
    gap: 0.6rem;
    padding: 0.8rem 1rem;
    border-top: 1px solid #ffe8d4;
    background: #fff;
}

.chat-textarea {
    box-sizing: border-box;
    flex: 1;
    min-height: 2.9rem;
    max-height: 7.5rem;
    padding: 0.7rem 1.1rem;
    border: 1px solid #fed7aa;
    border-radius: 1.5rem;
    background: #fffaf5;
    font-size: 0.95rem;
    line-height: 1.5rem;
    color: #1f2937;
    resize: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.chat-textarea:focus {
    outline: none;
    border-color: #FF7010;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.chat-textarea[readonly] {
    cursor: not-allowed;
    color: #9ca3af;
}

.chat-send {
    display: flex;
    width: 2.9rem;
    height: 2.9rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 0;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    color: #fff;
    cursor: pointer;
    box-shadow: 0 8px 18px -6px rgba(255, 112, 16, 0.7);
    transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
}

.chat-send:hover:not(:disabled) {
    transform: translateY(-2px) scale(1.05);
}

.chat-send:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    box-shadow: none;
}

.chat-send svg {
    transition: transform 0.25s ease;
}

.chat-send:hover:not(:disabled) svg {
    transform: translateX(2px) rotate(-10deg);
}

.chat-send.is-flying svg {
    animation: chat-fly 0.55s ease-in both;
}

.chat-start {
    position: relative;
    display: inline-flex;
    height: 2.9rem;
    align-items: center;
    gap: 0.5rem;
    padding: 0 1.5rem;
    border: 0;
    border-radius: 9999px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 8px 18px -6px rgba(22, 163, 74, 0.7);
    transition: transform 0.25s ease;
}

.chat-start:hover {
    transform: translateY(-2px) scale(1.03);
}

.chat-start:disabled {
    cursor: not-allowed;
    opacity: 0.65;
    transform: none;
}

.chat-start-spin {
    animation: chat-start-spin 1s linear infinite;
}

@keyframes chat-start-spin {
    to {
        transform: rotate(360deg);
    }
}

.chat-start-ring {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid #22c55e;
    animation: chat-ping 1.8s ease-out infinite;
    pointer-events: none;
}

/* ---- waiting modal ---- */
.chat-wait {
    position: relative;
    display: flex;
    width: 8rem;
    height: 8rem;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
}

.chat-wait-ring {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid #FF7010;
    opacity: 0;
    animation: chat-ring 2.4s ease-out infinite;
}

.chat-wait-ring.r2 { animation-delay: 0.8s; }
.chat-wait-ring.r3 { animation-delay: 1.6s; }

.chat-wait-avatar {
    position: relative;
    display: flex;
    width: 4.5rem;
    height: 4.5rem;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 10px 24px -6px rgba(255, 112, 16, 0.6);
}

.chat-wait-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-ellipsis i {
    display: inline-block;
    font-style: normal;
    animation: chat-blink 1.2s infinite;
}

.chat-ellipsis i:nth-child(2) { animation-delay: 0.2s; }
.chat-ellipsis i:nth-child(3) { animation-delay: 0.4s; }

/* ---- keyframes ---- */
@keyframes chat-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-5px); opacity: 1; }
}

@keyframes chat-presence {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
    100% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
}

@keyframes chat-throb {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.06); }
}

@keyframes chat-ping {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.25, 1.5); opacity: 0; }
}

@keyframes chat-ring {
    0% { transform: scale(0.55); opacity: 0.7; }
    100% { transform: scale(1.25); opacity: 0; }
}

@keyframes chat-blink {
    0%, 100% { opacity: 0.2; }
    50% { opacity: 1; }
}

@keyframes chat-fly {
    0% { transform: translate(0, 0) rotate(0); opacity: 1; }
    45% { transform: translate(14px, -14px) rotate(-12deg); opacity: 0; }
    46% { transform: translate(-14px, 14px) rotate(0); opacity: 0; }
    100% { transform: translate(0, 0) rotate(0); opacity: 1; }
}

@media (max-width: 640px) {
    .chat-stage { padding: 0; }
    .chat-shell { border-radius: 0; border-left: 0; border-right: 0; }
    .chat-row { max-width: 88%; }
    .chat-timer span { display: none; }
    .chat-timer svg { display: none; }
}

@media (prefers-reduced-motion: reduce) {
    .chat-shell,
    .chat-empty-art img,
    .chat-dot,
    .chat-wait-ring,
    .chat-start-ring,
    .chat-timer.is-low {
        animation: none !important;
    }
}
</style>