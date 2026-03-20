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

const showIncomingCall = ref(false)
const showCallScreen = ref(false)
const callStatus = ref('')
const muted = ref(false)
const remoteAudio = ref(null)

let pc = null
let localStream = null
let offerData = null

function sendSignal(type, data) {

  axios.post('/call/signal', {
    roomId: props.chat.id,
    type,
    data
  })
}


onMounted(async () => {
  await nextTick()
  scrollToBottom()
  echo.private(`chat.${props.chat.id}`)
    .listen('MessageSent', (e) => {
      if (props.user.id != e.message.user_id) {
        liveMessages.value.push(e.message)
      }
      scrollToBottom()
    })
    .listen('TypingEvent', (e) => {
      typingUser.value = e.typing ? e.userId : null
    })

  echo.private(`call.${props.chat.id}`)
    .listen('CallSignal', async (e) => {
      if (e.type === 'call_started') {
        showIncomingCall.value = true
        callStatus.value = 'Incoming call...'
      } else if (e.type === 'offer') {
        offerData = e.data
      } else if (e.type === 'candidate') {
        if (pc && pc.remoteDescription) {
          console.log('Adding remote candidate:', e.data);
          await pc.addIceCandidate(new RTCIceCandidate(e.data))
        }
      } else if (e.type === 'call_ended') {
        endCall(false)
      }
    })
})

onUnmounted(() => {
  echo.leave(`chat.${props.chat.id}`)
})

const acceptCall = async () => {
  if (!offerData) {
    return
  }
  showIncomingCall.value = false
  showCallScreen.value = true
  callStatus.value = 'Connecting...'

  localStream = await navigator.mediaDevices.getUserMedia({ audio: true })

  pc = new RTCPeerConnection({
    iceServers: [
      // { urls: 'stun:stun.l.google.com:19302' },
      {
        urls: [
          'turn:openrelay.metered.ca:80',
          'turn:openrelay.metered.ca:443',
          'turn:openrelay.metered.ca:443?transport=tcp'
        ],
        username: 'openrelayproject',
        credential: 'openrelayproject'
      }
    ]
  })


  localStream.getTracks().forEach(track => {
    pc.addTrack(track, localStream)
  })


  pc.ontrack = event => {
    remoteAudio.value.srcObject = event.streams[0];
    remoteAudio.value.play().catch(err => console.error('Play failed:', err));
  }

  pc.onicecandidate = event => {
    if (event.candidate) {
      sendSignal('candidate', event.candidate.toJSON())
    }
  }



  if (!offerData.sdp.endsWith('\r\n')) {
    offerData.sdp += '\r\n';
  }

  await pc.setRemoteDescription(offerData)


  const answer = await pc.createAnswer()

  await pc.setLocalDescription(answer)

  sendSignal('answer', { type: answer.type, sdp: answer.sdp })

  sendSignal('call_joined', { joined: true })
  callStatus.value = 'In call with user'
}

const toggleMute = () => {
  muted.value = !muted.value
  localStream.getAudioTracks()[0].enabled = !muted.value
}

const endCall = (send = true) => {
  if (pc) {
    pc.close()
    pc = null
  }
  if (localStream) {
    localStream.getTracks().forEach(track => track.stop())
    localStream = null
  }
  if (remoteAudio.value) {
    remoteAudio.value.srcObject = null
  }
  showCallScreen.value = false
  showIncomingCall.value = false
  callStatus.value = 'Call ended'
  muted.value = false

  // Only send signal if this side initiated the end
  if (send) {
    sendSignal('call_ended', { ended: true })
    send = false
  }
}


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
        placeholder="Type a message..." rows="1" ref="messageInput" />
      <button type="submit"
        class="ml-2 px-6 py-2 bg-orange-500 text-white rounded-full hover:bg-orange-600 transition">Send</button>
    </form>
  </div>

  <!-- Incoming Call Popup -->
  <div v-if="showIncomingCall" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
      <h3 class="text-lg font-semibold mb-4">Incoming Call</h3>
      <p class="mb-4">{{ callStatus }}</p>
      <button @click="acceptCall" class="px-6 py-2 bg-green-500 text-white rounded-full hover:bg-green-600 mr-4">
        Accept Call
      </button>
      <button @click="endCall(true)" class="px-4 py-2 rounded-full bg-red-500 text-white">
        End Call
      </button>
    </div>
  </div>

  <!-- Call Screen -->
  <div v-if="showCallScreen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
      <h3 class="text-lg font-semibold mb-4">Audio Call</h3>
      <p class="mb-2">{{ callStatus }}</p>
      <audio ref="remoteAudio" autoplay></audio>
      <div class="mt-4 flex justify-center space-x-4">
        <button @click="toggleMute" class="px-4 py-2 rounded bg-gray-200">
          {{ muted ? 'Unmute' : 'Mute' }}
        </button>
        <button @click="endCall(true)" class="px-4 py-2 rounded bg-red-500 text-white">
          End Call
        </button>
      </div>
    </div>
  </div>

</template>
