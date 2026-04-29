<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
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
const typingUser = ref(null)
let typingTimeout = null

const sendMessage = async () => {
  if (!newMessage.value) return

  let oldMessage = newMessage.value
  newMessage.value = ''
  try {
    const response = await axios.post(route('astrologer.chats.storeMessage', props.chat.id), {
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


const canSend = ref(true)

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
      scrollToBottom()
    })
    .listen('TypingEvent', (e) => {
      typingUser.value = e.typing ? e.userId : null
    })
})

onUnmounted(() => {
  echo.leave(`chat.${props.chat.id}`)
})


const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(liveMessages, async () => {
  await nextTick()
  scrollToBottom()
})

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

</script>

<template>
  <div class="flex h-[calc(100vh-4rem)] flex-col bg-white border rounded shadow">
    <!-- Header -->
    <div class="border-b p-4 font-semibold bg-gray-200">
      {{chat.participants.find(p => p.user_id !== user.id)?.user.name}}
    </div>

    <!-- Messages -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-1 bg-gray-100">
      <div v-for="msg in liveMessages" :key="msg.id" :class="msg.user_id === user.id ? 'text-right' : 'text-left'">
        <div class="group inline-block">
          <div :class="msg.user_id === user.id
            ? 'px-4 py-2 rounded-lg bg-orange-500 text-white'
            : 'px-4 py-2 rounded-lg bg-gray-200 text-gray-800'" style="white-space: pre-line;">
            {{ msg.message }}
          </div>
          <!-- timestamp hidden until hover -->
          <div class="text-xs text-gray-400 mt-1 hidden group-hover:block transition"
            :class="msg.user_id === user.id ? 'text-right' : 'text-left'">
            {{ dayjs(msg.created_at).format('h:mm A') }}
          </div>
        </div>
      </div>
    </div>

    <div v-if="typingUser && typingUser !== user.id" class="text-sm text-gray-500 px-5 pb-2">
      {{chat.participants.find(p => p.user_id === typingUser)?.user.name}} is typing...
    </div>

    <!-- Input -->
    <form @submit.prevent="sendMessage" class="border-t p-4 flex items-end bg-gray-200">
      <textarea v-model="newMessage" @input="autoResize($event); handleTyping()"
        @keydown.enter.shift.exact.prevent="newMessage += '\n'" @keydown.enter.exact.prevent="sendMessage()"
        class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
        placeholder="Type a message..." rows="1" ref="messageInput" :readonly="!canSend" />
      <button type="submit" :disabled="!canSend"
        :class="!canSend ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600'"
        class="ml-2 px-6 py-2 bg-orange-500 text-white rounded-full hover:bg-orange-600 transition">Send</button>
    </form>
    
  </div>

</template>
