<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'
import { Icon } from '@iconify/vue';
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    astrologer: Object,
    chat: Object,
    history: Object,
})

const newMessage = ref('')
const liveMessages = ref([...props.history])
const messagesContainer = ref(null)


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

// async function endChat(elapsedSeconds, reason) {
//   try {
//     await axios.post(route('user.chat.end', props.chat.id), {
//       reason: reason,
//       elapsed_seconds: elapsedSeconds
//     })
//   } catch (e) {
//     console.error('Error ending chat:', e)
//   }
// }


onMounted(async () => {
    await nextTick()
    scrollToBottom()


    let currentMembers = []
    const callStartTime = ref(null)
    echo.join(`chat.${props.chat.id}`)
        .here((users) => {
            currentMembers = users
            if (users.length === 2) {
                callStartTime.value = Date.now()
                if (currentMembers.length === 2) {
                    axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: true })
                }
            }
        })
        .joining((user) => {
            currentMembers.push(user)
            if (currentMembers.length === 2) {
                axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: true })
            }
            if (currentMembers.length === 2) {
                callStartTime.value = Date.now()
            }
        })
        .leaving((user) => {
            currentMembers = currentMembers.filter(u => u.id !== user.id)
            axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: false })
            if (callStartTime.value) {
                const elapsedSeconds = Math.floor((Date.now() - callStartTime.value) / 1000)
                endCall(elapsedSeconds, 'user') // reason: user left
            }
        });

    echo.join(`presence.call.${props.chat.id}`)
        .here((users) => {
            currentMembers = users
            if (currentMembers.length === 2) {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            }
        })
        .joining((user) => {
            currentMembers.push(user)
            if (currentMembers.length === 2) {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            }
        })
        .leaving((user) => {
            currentMembers = currentMembers.filter(u => u.id !== user.id)
            if (currentMembers.length < 2) {
                stopDurationTimer()
                endCall(false)
            }
        })
        .listen('CallSignal', async (e) => {
            if (e.type === 'call_started') {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            } else if (e.type === 'offer') {
                offerData = e.data
            } else if (e.type === 'candidate') {
                if (pc && pc.remoteDescription) {
                    console.log('Adding remote candidate:', e.data)
                    await pc.addIceCandidate(new RTCIceCandidate(e.data))
                }
            } else if (e.type === 'call_ended') {
                stopDurationTimer()
                endCall(false)
            }
        })


})

onUnmounted(() => {
    echo.leave(`chat.${props.chat.id}`)
})

const callDuration = ref(0)
let durationInterval = null

const startDurationTimer = () => {
    callDuration.value = 0
    if (durationInterval) clearInterval(durationInterval)
    durationInterval = setInterval(() => {
        callDuration.value++
    }, 1000)
}

const stopDurationTimer = () => {
    clearInterval(durationInterval)
    durationInterval = null
}

const formatDuration = (seconds) => {
    const m = Math.floor(seconds / 60)
    const s = seconds % 60
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

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
            { urls: 'stun:stun.l.google.com:19302' },
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
            console.log('Candidate type:', event.candidate.type, event.candidate);
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
    setTimeout(() => {
        callStatus.value = 'connected'
        startDurationTimer()
    }, 3000)
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


</script>

<template>

    <Head :title="user?.name" />
    <AstrologerLayout>
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-white border rounded shadow-xl mx-auto max-w-7xl">
            <!-- Header -->
            <div class="border-b p-4 font-semibold bg-gray-200 flex justify-between items-center">
                <span>{{ user.name }}</span>
                <span class="text-sm text-gray-600">{{ callStatus }}</span>
            </div>

            <!-- Main Split Layout -->
            <!-- On md+ screens: row with 1/2 widths; on mobile: stack vertically -->
            <div class="flex flex-1 flex-col md:flex-row overflow-hidden">
                <!-- Left: Call History -->
                <div ref="messagesContainer"
                    class="w-full md:w-1/2 overflow-y-auto p-6 space-y-3 bg-gray-50 border-b md:border-b-0 md:border-r">
                    <h3 class="font-semibold text-gray-700 mb-2">Call History</h3>
                    <div v-for="msg in liveMessages" :key="msg.id">
                        <div class="w-full p-3 rounded-lg shadow bg-orange-50 border border-gray-200">
                            <div class="text-sm text-gray-700 space-y-1">
                                <div>Type: <span class="font-medium">{{ msg.call_type }}</span></div>
                                <div>Status:
                                    <span :class="{
                                        'text-green-600': msg.status === 'ended',
                                        'text-red-600': msg.status === 'failed' || msg.status === 'missed',
                                        'text-yellow-600': msg.status === 'answered'
                                    }">
                                        {{ msg.status }}
                                    </span>
                                </div>
                                <div v-if="msg.duration">Duration: {{ msg.duration }} sec</div>
                                <div v-if="msg.cost">Cost: ₹{{ msg.cost }}</div>
                            </div>
                            <span class="text-xs text-gray-500">
                                {{ dayjs(msg.started_at).format('MMM D, h:mm A') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Call Interface -->
                <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-6">
                    <!-- Incoming Call -->
                    <div v-if="showIncomingCall" class="text-center">
                        <h3 class="text-lg font-semibold mb-4">{{ user.name }}</h3>
                        <p class="mb-4">{{ callStatus }}</p>
                        <div class="flex justify-center space-x-6">
                            <button @click="acceptCall"
                                class="w-12 h-12 flex items-center justify-center rounded-full bg-green-500 hover:bg-green-600">
                                <Icon icon="mdi-phone" width="24" height="24" class="text-white" />
                            </button>
                            <button @click="endCall(true)"
                                class="w-12 h-12 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600">
                                <Icon icon="mdi-phone-hangup" width="24" height="24" class="text-white" />
                            </button>
                        </div>
                    </div>

                    <!-- Active Call -->
                    <div v-if="showCallScreen"
                        class="w-full max-w-md bg-white rounded-lg shadow-lg p-6 flex flex-col items-center">
                        <audio ref="remoteAudio" autoplay></audio>
                        <div class="text-gray-700 mt-2 font-medium">
                            {{ user.name }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            <span v-if="callStatus === 'connected'">
                                {{ formatDuration(callDuration) }}
                            </span>
                            <span v-else>{{ callStatus }}</span>
                        </div>

                        <div class="flex justify-center space-x-6 mt-6">
                            <!-- Mute -->
                            <button @click="toggleMute"
                                class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300">
                                <Icon :icon="muted ? 'mdi-microphone-off' : 'mdi-microphone'" width="24" height="24"
                                    :class="muted ? 'text-red-600' : 'text-gray-700'" />
                            </button>

                            <!-- Speaker -->
                            <button @click="toggleSpeaker"
                                class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300">
                                <Icon :icon="speakerOn ? 'mdi-volume-high' : 'mdi-volume-off'" width="24" height="24"
                                    :class="speakerOn ? 'text-blue-600' : 'text-gray-700'" />
                            </button>

                            <!-- End Call -->
                            <button @click="endCall(true)"
                                class="w-12 h-12 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600">
                                <Icon icon="mdi-phone-hangup" width="24" height="24" class="text-white" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AstrologerLayout>

    <!-- Footer -->
    <footer class="bg-white border-t mt-2">
        <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-500">
            © {{ new Date().getFullYear() }} My Astro Sathi. All rights reserved.
        </div>
    </footer>
</template>
