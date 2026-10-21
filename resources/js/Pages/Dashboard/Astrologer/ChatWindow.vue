<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Icon } from '@iconify/vue'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'

const props = defineProps({
  user: Object,
  chat: Object,
  messages: Array,
})

const newMessage = ref('')
const liveMessages = ref([...props.messages])
const messagesContainer = ref(null)
const messageInput = ref(null)
const typingUser = ref(null)
const canSend = ref(true)
let typingTimeout = null

// ---------- header helpers ----------
const partner = computed(() => props.chat.participants.find(p => p.user_id !== props.user.id)?.user)
const partnerName = computed(() => partner.value?.name ?? 'Unknown')
const partnerTyping = computed(() => !!typingUser.value && typingUser.value !== props.user.id)

const statusText = computed(() => {
  if (partnerTyping.value) return 'typing…'
  return canSend.value ? 'In session' : 'Waiting for user to join'
})

// ---------- message list with day separators + grouping ----------
const items = computed(() =>
  liveMessages.value.map((msg, i, arr) => {
    const prev = arr[i - 1]
    const next = arr[i + 1]
    const mine = msg.user_id === props.user.id
    const newDay = !prev || !dayjs(prev.created_at).isSame(msg.created_at, 'day')
    const groupStart = newDay || prev.user_id !== msg.user_id
    const groupEnd = !next || next.user_id !== msg.user_id || !dayjs(next.created_at).isSame(msg.created_at, 'day')
    return { msg, mine, groupStart, groupEnd, day: newDay ? dayLabel(msg.created_at) : null }
  })
)

function dayLabel(date) {
  const d = dayjs(date)
  if (d.isSame(dayjs(), 'day')) return 'Today'
  if (d.isSame(dayjs().subtract(1, 'day'), 'day')) return 'Yesterday'
  return d.format('D MMM YYYY')
}

// ---------- scrolling ----------
const atBottom = ref(true)
const unread = ref(0)

const scrollToBottom = (smooth = false) => {
  const el = messagesContainer.value
  if (!el) return
  el.scrollTo({ top: el.scrollHeight, behavior: smooth ? 'smooth' : 'auto' })
  unread.value = 0
}

const onScroll = () => {
  const el = messagesContainer.value
  if (!el) return
  atBottom.value = el.scrollHeight - el.scrollTop - el.clientHeight < 80
  if (atBottom.value) unread.value = 0
}

watch(() => liveMessages.value.length, async () => {
  const last = liveMessages.value[liveMessages.value.length - 1]
  const mine = last?.user_id === props.user.id
  const stick = atBottom.value
  await nextTick()
  if (mine || stick) scrollToBottom(true)
  else unread.value++
})

watch(partnerTyping, async (v) => {
  if (v && atBottom.value) {
    await nextTick()
    scrollToBottom(true)
  }
})

// ---------- sending ----------
const sendMessage = async () => {
  if (!newMessage.value.trim() || !canSend.value) return

  let oldMessage = newMessage.value
  newMessage.value = ''
  if (messageInput.value) messageInput.value.style.height = 'auto'
  try {
    const response = await axios.post(route('astrologer.chats.storeMessage', props.chat.id), {
      message: oldMessage
    })
    if (response.data.success) {
      liveMessages.value.push(response.data.message)
    }
  } catch (error) {
    console.error('Failed to send message:', error)
    newMessage.value = oldMessage
  }
}

async function endChat(elapsedSeconds, reason) {
  try {
    await axios.post(route('user.chat.end', props.chat.id), {
      reason: reason,
      elapsed_seconds: elapsedSeconds
    })
  } catch (e) {
    console.error('Error ending chat:', e)
  }
}

onMounted(async () => {
  await nextTick()
  scrollToBottom()

  let currentMembers = []
  const chatStartTime = ref(null)
  echo.join(`chat.${props.chat.id}`)
    .here((users) => {
      currentMembers = users
      canSend.value = currentMembers.length === 2
      if (users.length === 2) {
        chatStartTime.value = Date.now()
        if (currentMembers.length === 2) {
          axios.post(`/astrologers/${props.user.id}/busy`, { busy: true })
        }
      }
    })
    .joining((user) => {
      currentMembers.push(user)
      canSend.value = currentMembers.length === 2
      if (currentMembers.length === 2) {
        axios.post(`/astrologers/${props.user.id}/busy`, { busy: true })
      }
      if (currentMembers.length === 2) {
        chatStartTime.value = Date.now()
      }
    })
    .leaving((user) => {
      currentMembers = currentMembers.filter(u => u.id !== user.id)
      canSend.value = currentMembers.length === 2
      axios.post(`/astrologers/${props.user.id}/busy`, { busy: false })
      if (chatStartTime.value) {
        const elapsedSeconds = Math.floor((Date.now() - chatStartTime.value) / 1000)
        endChat(elapsedSeconds, 'user') // reason: user left
      }
    })
    .listen('MessageSent', (e) => {
      if (props.user.id != e.message.user_id) {
        liveMessages.value.push(e.message)
      }
    })
    .listen('TypingEvent', (e) => {
      typingUser.value = e.typing ? e.userId : null
    })
})

onUnmounted(() => {
  echo.leave(`chat.${props.chat.id}`)
  if (typingTimeout) clearTimeout(typingTimeout)
})

// ---------- typing indicator (outgoing) ----------
let isTyping = false

const handleTyping = () => {
  // If not already typing, send true once
  if (!isTyping) {
    axios.post(route('chat.typing', props.chat.id), { typing: true })
    isTyping = true
  }

  // Reset timeout each keystroke
  if (typingTimeout) clearTimeout(typingTimeout)

  typingTimeout = setTimeout(() => {
    // After 1s of no input, send false once
    axios.post(route('chat.typing', props.chat.id), { typing: false })
    isTyping = false
  }, 1000)
}

const autoResize = (event) => {
  const el = event.target
  el.style.height = 'auto' // reset
  const lineHeight = 24 // adjust to your CSS line-height
  const maxHeight = lineHeight * 5 // 5 rows max
  el.style.height = Math.min(el.scrollHeight, maxHeight) + 'px'
}
</script>

<template>
  <div class="cw">
    <!-- Header -->
    <header class="cw-head">
      <span class="adm-avatar cw-av">{{ partnerName.charAt(0).toUpperCase() }}</span>
      <div class="cw-head-main">
        <strong>{{ partnerName }}</strong>
        <span class="cw-status" :class="{ 'is-live': canSend, 'is-typing': partnerTyping }">
          <i></i>{{ statusText }}
        </span>
      </div>
    </header>

    <!-- Messages -->
    <div ref="messagesContainer" class="cw-body" @scroll="onScroll">
      <div v-if="!items.length" class="cw-empty">
        <Icon icon="mdi:message-text-outline" width="40" height="40" />
        <span>No messages yet — say hello 👋</span>
      </div>

      <TransitionGroup name="cw-msg" tag="div" class="cw-list">
        <div v-for="it in items" :key="it.msg.id" class="cw-row" :class="{ 'is-mine': it.mine, 'is-start': it.groupStart }">
          <div v-if="it.day" class="cw-day"><span>{{ it.day }}</span></div>

          <div class="cw-bubble-wrap">
            <div class="cw-bubble" :class="{ 'is-start': it.groupStart, 'is-end': it.groupEnd }">
              {{ it.msg.message }}
            </div>
            <span class="cw-time">{{ dayjs(it.msg.created_at).format('h:mm A') }}</span>
          </div>
        </div>
      </TransitionGroup>

      <!-- Typing indicator -->
      <Transition name="cw-typing">
        <div v-if="partnerTyping" class="cw-row cw-typing">
          <div class="cw-bubble-wrap">
            <div class="cw-bubble cw-dots" aria-label="typing">
              <span></span><span></span><span></span>
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- New messages pill -->
    <Transition name="cw-pill">
      <button v-if="unread > 0" type="button" class="cw-newpill" @click="scrollToBottom(true)">
        <Icon icon="mdi:arrow-down" width="16" height="16" />
        {{ unread }} new {{ unread === 1 ? 'message' : 'messages' }}
      </button>
    </Transition>

    <!-- Input -->
    <form class="cw-input" @submit.prevent="sendMessage">
      <textarea v-model="newMessage" ref="messageInput" rows="1" class="cw-textarea"
        :placeholder="canSend ? 'Type a message...' : 'Waiting for the user to join…'" :readonly="!canSend"
        @input="autoResize($event); handleTyping()" @keydown.enter.shift.exact.prevent="newMessage += '\n'"
        @keydown.enter.exact.prevent="sendMessage()" />
      <button type="submit" class="cw-send" :disabled="!canSend || !newMessage.trim()" aria-label="Send message">
        <Icon icon="mdi:send" width="20" height="20" />
      </button>
    </form>
  </div>
</template>

<style>
.cw {
  position: relative;
  display: flex;
  height: 100%;
  min-height: 0;
  flex-direction: column;
  background: #fffdfb;
}

/* ---------- Header ---------- */
.cw-head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 1.1rem;
  border-bottom: 1px solid #ffedd5;
  background: linear-gradient(90deg, #fff7ed, #fff);
}

.cw-av {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 9999px;
}

.cw-head-main {
  display: flex;
  min-width: 0;
  flex-direction: column;
}

.cw-head-main strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.95rem;
  color: #1f2937;
}

.cw-status {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  color: #9ca3af;
  transition: color 0.3s ease;
}

.cw-status i {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 9999px;
  background: #d1d5db;
  transition: background 0.3s ease;
}

.cw-status.is-live {
  color: #16a34a;
}

.cw-status.is-live i {
  background: #22c55e;
  animation: cw-pulse 2s ease-out infinite;
}

.cw-status.is-typing {
  color: #ff7010;
  font-style: italic;
}

.cw-status.is-typing i {
  background: #ff7010;
}

/* ---------- Messages ---------- */
.cw-body {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 1rem 1.1rem 0.5rem;
  background:
    radial-gradient(circle at 1px 1px, rgba(255, 112, 16, 0.07) 1px, transparent 0) 0 0 / 22px 22px,
    #fff8f1;
  scroll-behavior: auto;
}

.cw-list {
  display: flex;
  flex-direction: column;
}

.cw-empty {
  display: flex;
  height: 100%;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #9ca3af;
  animation: cw-fade 0.5s ease both;
}

.cw-row {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-top: 2px;
}

.cw-row.is-start {
  margin-top: 0.65rem;
}

.cw-row.is-mine {
  align-items: flex-end;
}

.cw-day {
  align-self: center;
  margin: 0.5rem 0 0.4rem;
}

.cw-day span {
  display: inline-block;
  padding: 0.2rem 0.75rem;
  border-radius: 9999px;
  background: #ffedd5;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #c2410c;
}

.cw-row.is-mine .cw-day,
.cw-row .cw-day {
  align-self: center;
}

.cw-bubble-wrap {
  display: flex;
  max-width: min(78%, 34rem);
  flex-direction: column;
}

.cw-row.is-mine .cw-bubble-wrap {
  align-items: flex-end;
}

.cw-bubble {
  padding: 0.5rem 0.9rem;
  border: 1px solid #ffe8d4;
  border-radius: 1.1rem;
  background: #fff;
  font-size: 0.93rem;
  line-height: 1.45;
  color: #1f2937;
  white-space: pre-line;
  overflow-wrap: anywhere;
  box-shadow: 0 1px 2px rgba(124, 45, 18, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cw-bubble:hover {
  box-shadow: 0 6px 16px -8px rgba(124, 45, 18, 0.3);
}

.cw-row.is-mine .cw-bubble {
  border-color: transparent;
  background: linear-gradient(135deg, #ff7010, #ff9248);
  color: #fff;
  box-shadow: 0 6px 14px -8px rgba(255, 112, 16, 0.7);
}

/* "tail" corners on the first / last bubble of a group */
.cw-row:not(.is-mine) .cw-bubble:not(.is-start) {
  border-top-left-radius: 0.35rem;
}
.cw-row:not(.is-mine) .cw-bubble:not(.is-end) {
  border-bottom-left-radius: 0.35rem;
}
.cw-row.is-mine .cw-bubble:not(.is-start) {
  border-top-right-radius: 0.35rem;
}
.cw-row.is-mine .cw-bubble:not(.is-end) {
  border-bottom-right-radius: 0.35rem;
}
.cw-row:not(.is-mine) .cw-bubble.is-end {
  border-bottom-left-radius: 0.3rem;
}
.cw-row.is-mine .cw-bubble.is-end {
  border-bottom-right-radius: 0.3rem;
}

/* timestamp: slides open on hover */
.cw-time {
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  font-size: 0.7rem;
  color: #9ca3af;
  transition: max-height 0.2s ease, opacity 0.2s ease, margin 0.2s ease;
}

.cw-bubble-wrap:hover .cw-time {
  max-height: 1.2rem;
  margin-top: 0.2rem;
  opacity: 1;
}

/* enter animation for new messages */
.cw-msg-enter-active {
  transition: opacity 0.35s ease, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}

.cw-msg-enter-from {
  opacity: 0;
  transform: translateY(14px) scale(0.94);
}

.cw-row.is-mine.cw-msg-enter-from {
  transform: translate(10px, 14px) scale(0.94);
}

.cw-msg-move {
  transition: transform 0.3s ease;
}

/* ---------- Typing dots ---------- */
.cw-typing {
  margin-top: 0.65rem;
}

.cw-dots {
  display: inline-flex;
  align-items: center;
  gap: 0.28rem;
  padding: 0.75rem 0.9rem;
  border-bottom-left-radius: 0.3rem;
}

.cw-dots span {
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 9999px;
  background: #fb923c;
  animation: cw-bounce 1.2s ease-in-out infinite;
}

.cw-dots span:nth-child(2) {
  animation-delay: 0.15s;
}

.cw-dots span:nth-child(3) {
  animation-delay: 0.3s;
}

.cw-typing-enter-active,
.cw-typing-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.cw-typing-enter-from,
.cw-typing-leave-to {
  opacity: 0;
  transform: translateY(8px) scale(0.9);
}

/* ---------- New-messages pill ---------- */
.cw-newpill {
  position: absolute;
  bottom: 5.2rem;
  left: 50%;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.4rem 0.9rem;
  border: 0;
  border-radius: 9999px;
  background: #1f2937;
  font-size: 0.8rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transform: translateX(-50%);
  box-shadow: 0 10px 24px -8px rgba(0, 0, 0, 0.5);
}

.cw-pill-enter-active,
.cw-pill-leave-active {
  transition: opacity 0.25s ease, transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.cw-pill-enter-from,
.cw-pill-leave-to {
  opacity: 0;
  transform: translate(-50%, 12px);
}

/* ---------- Input ---------- */
.cw-input {
  display: flex;
  align-items: flex-end;
  gap: 0.6rem;
  padding: 0.8rem 1rem calc(0.8rem + env(safe-area-inset-bottom, 0px));
  border-top: 1px solid #ffedd5;
  background: #fff;
}

.cw-textarea {
  flex: 1;
  min-height: 2.75rem;
  max-height: 7.5rem;
  padding: 0.65rem 1rem;
  border: 1px solid #fed7aa;
  border-radius: 1.4rem;
  background: #fffaf5;
  font-family: inherit;
  font-size: 0.93rem;
  line-height: 1.5rem;
  color: #1f2937;
  resize: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.cw-textarea::placeholder {
  color: #9ca3af;
}

.cw-textarea:focus {
  outline: none;
  border-color: #ff7010;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.cw-textarea[readonly] {
  background: #f3f4f6;
  cursor: not-allowed;
}

.cw-send {
  display: flex;
  width: 2.75rem;
  height: 2.75rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 9999px;
  background: linear-gradient(135deg, #ff7010, #ff9248);
  color: #fff;
  cursor: pointer;
  box-shadow: 0 8px 18px -6px rgba(255, 112, 16, 0.7);
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.cw-send svg {
  transition: transform 0.25s ease;
}

.cw-send:hover:not(:disabled) {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 12px 22px -6px rgba(255, 112, 16, 0.8);
}

.cw-send:hover:not(:disabled) svg {
  transform: translateX(2px) rotate(-12deg);
}

.cw-send:active:not(:disabled) {
  transform: scale(0.92);
}

.cw-send:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

/* ---------- Keyframes ---------- */
@keyframes cw-bounce {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.5;
  }
  30% {
    transform: translateY(-0.3rem);
    opacity: 1;
  }
}

@keyframes cw-pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55);
  }
  70%, 100% {
    box-shadow: 0 0 0 0.45rem rgba(34, 197, 94, 0);
  }
}

@keyframes cw-fade {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .cw *,
  .cw *::before,
  .cw *::after {
    animation: none !important;
    transition-duration: 0.01ms !important;
  }
}
</style>