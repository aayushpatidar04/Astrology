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
    chat: Object, // can be chat or call object
    history: {
        type: Array,
        default: () => []
    },
})

const newMessage = ref('')
const liveMessages = ref(Array.isArray(props.history) ? [...props.history] : [])
const messagesContainer = ref(null)


const showIncomingCall = ref(false)
const showCallScreen = ref(false)
const callStatus = ref('')
const muted = ref(false)
const remoteAudio = ref(null)
const callStartTime = ref(null)
const localConnected = ref(false)
const remoteConnected = ref(false)
const timerStarted = ref(false)
const pendingRemoteCandidates = ref([])
const localTimestamp = ref(null)
const remoteTimestamp = ref(null)
const startTime = ref(null)

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

function upsertLiveMessage(callHistory) {
    if (!callHistory) return

    const existingIndex = liveMessages.value.findIndex(msg => msg.id === callHistory.id)
    if (existingIndex >= 0) {
        liveMessages.value[existingIndex] = callHistory
    } else {
        liveMessages.value.push(callHistory)
    }
}

onMounted(async () => {
    await nextTick()
    scrollToBottom()


    let currentMembers = []
    // Use chat or call id for channel
    const channelId = props.chat?.id
    echo.join(`chat.${channelId}`)
        .here((users) => {
            currentMembers = users
            if (users.length === 2) {
                if (currentMembers.length === 2) {

                }
            }
        })
        .joining((user) => {
            currentMembers.push(user)
            if (currentMembers.length === 2) {

            }
        })
        .leaving((user) => {
            currentMembers = currentMembers.filter(u => u.id !== user.id)
            axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: false })
            endCall(false)
        });

    let currentMembers2 = []
    echo.join(`call.${channelId}`)
        .here((users) => {
            currentMembers2 = users
            if (currentMembers2.length === 2) {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            }
        })
        .joining((user) => {
            currentMembers2.push(user)
            if (currentMembers2.length === 2) {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            }
        })
        .leaving(async (user) => {
            currentMembers2 = currentMembers2.filter(u => u.id !== user.id)
            if (currentMembers2.length < 2) {
                stopDurationTimer()

                let elapsedSeconds = 0
                let status = 'ended'
                // Record call end

                if (callStartTime.value) {
                    elapsedSeconds = Math.floor((Date.now() - callStartTime.value) / 1000)
                    status = elapsedSeconds > 0 ? 'answered' : 'ended'
                }
                try {
                    await axios.post(route('user.call.end', props.chat.id), {
                        reason: 'user',
                        status: status,
                        elapsed_seconds: elapsedSeconds
                    })
                } catch (e) {
                    console.error('Error ending call:', e)
                }
                endCall(false)
                axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: false })
            }
        })
        .listen('CallSignal', async (e) => {
            if (e.type === 'call_started') {
                showIncomingCall.value = true
                callStatus.value = 'Incoming call...'
            } else if (e.type === 'offer') {
                offerData = e.data
                if (!offerData.sdp.endsWith('\r\n')) {
                    offerData.sdp += '\r\n';
                }
                await pc.setRemoteDescription(offerData)
                // Drain any pending candidates queued while PC was not ready
                while (pendingRemoteCandidates.value.length > 0) {
                    const candidate = pendingRemoteCandidates.value.shift()
                    await pc.addIceCandidate(new RTCIceCandidate(candidate))
                }
                const answer = await pc.createAnswer()
                await pc.setLocalDescription(answer)
                sendSignal('answer', { type: answer.type, sdp: answer.sdp })
            } else if (e.type === 'candidate') {
                if (pc && pc.remoteDescription) {
                    await pc.addIceCandidate(new RTCIceCandidate(e.data))
                } else {
                    pendingRemoteCandidates.value.push(e.data)
                }
            } else if (e.type === 'ice_connected') {
                remoteConnected.value = true
                remoteTimestamp.value = e.data.timestamp
                if (localConnected.value) {
                    startTime.value = Date.now()
                    callStartTime.value = startTime.value
                    callStatus.value = 'connected'
                    startDurationTimer()
                    try {
                        const response = await axios.post('/call/update-status', { chatId: props.chat.id, status: 'answered' })
                        if (response.data?.call_history) {
                            upsertLiveMessage(response.data.call_history)
                        }
                    } catch (err) {
                        console.error('Error updating call status:', err)
                    }
                }
            } else if (e.type === 'call_ended') {
                stopDurationTimer()
                // Check if call was rejected/no response and update status
                if (!pc || pc.connectionState !== 'connected') {
                    try {
                        if (e.data.ended) {

                        } else {
                            const response = await axios.post('/call/update-status', {
                                chatId: props.chat.id,
                                status: e.data.reason === 'no_response' ? 'missed' : 'failed'
                            })
                            if (response.data?.call_history) {
                                upsertLiveMessage(response.data.call_history)
                            }
                        }
                    } catch (err) {
                        console.error('Error updating call status:', err)
                    }
                }
                endCall(false)
                axios.post(`/astrologers/${props.astrologer?.id}/busy`, { busy: false })
            }
        })


})

onUnmounted(() => {
    const channelId = props.chat?.id
    echo.leave(`chat.${channelId}`)
})

const callDuration = ref(0)
let durationInterval = null

const startDurationTimer = () => {
    if (timerStarted.value) return
    timerStarted.value = true
    callDuration.value = Math.floor((Date.now() - startTime.value) / 1000)
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
    showIncomingCall.value = false
    showCallScreen.value = true
    callStatus.value = 'connecting...'

    localStream = await navigator.mediaDevices.getUserMedia({ audio: true })

    pc = new RTCPeerConnection({
        iceServers: [
            {
                urls: [
                    "stun:52.66.24.208:3478",
                    "turn:52.66.24.208:3478?transport=udp",
                    "turn:52.66.24.208:3478?transport=tcp",
                    "turn:52.66.24.208:443?transport=tcp",
                ],
                username: 'myastrosathi',
                credential: 'myastrosathi'
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
            console.log('Candidate type:', event.candidate.type, event.candidate);
            sendSignal('candidate', event.candidate.toJSON())
        }
    }

    pc.oniceconnectionstatechange = async () => {
        if (pc.iceConnectionState === 'connected') {
            localConnected.value = true
            localTimestamp.value = Date.now()
            sendSignal('ice_connected', { timestamp: localTimestamp.value })
        }
    }

    sendSignal('call_joined', { joined: true })
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
    localConnected.value = false
    remoteConnected.value = false
    timerStarted.value = false
    pendingRemoteCandidates.value = []
    localTimestamp.value = null
    remoteTimestamp.value = null
    startTime.value = null

    stopDurationTimer()

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
                                    'text-green-600': msg.status === 'answered',
                                    'text-red-600': msg.status === 'failed' || msg.status === 'missed' || msg.status === 'ended' || msg.status === 'rejected',
                                    'text-yellow-600': msg.status === 'ringing'
                                }">
                                    {{ msg.status }}
                                </span>
                            </div>
                            <div v-if="msg.duration">Duration: {{ msg.duration }} sec</div>
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
</template>
