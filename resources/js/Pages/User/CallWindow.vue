<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'
import { Icon } from '@iconify/vue';

const props = defineProps({
    auth: Object,
    history: Object,
    astrologer: Object,
    chat: Object,
})

const is_busy = ref(props.astrologer?.user?.is_busy)
const newMessage = ref('')
const liveMessages = ref([...props.history])
const messagesContainer = ref(null)


const astrologerOnline = ref(
    props.astrologer?.online
)

const joined = ref(false)

// --- Countdown state ---
const userWalletBalance = ref(props.auth.user.wallet.balance ?? 0)
const astrologerRate = props.astrologer?.charged_call_price ?? 0
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

const callEndReason = ref(null) // 'user', 'astrologer', 'balance'

// Start countdown only when astrologer joins
function startCountdown() {
    if (countdown.value) clearInterval(countdown.value)
    countdown.value = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--
        } else {
            callEndReason.value = 'balance'
            clearInterval(countdown.value)
            endCall(true)
        }
    }, 1000)
}

onMounted(async () => {
    await nextTick()
    scrollToBottom()

    // listen for astrologer status changes
    const astrologerId = props.astrologer?.user_id

    let currentMembers = []

    echo.join(`chat.${props.chat.id}`)
        .here((users) => {
            currentMembers = users
            if (users.length === 2) {
                joined.value = true;
            }
        })
        .joining((user) => {
            currentMembers.push(user);
            if (currentMembers.length === 2) {
                joined.value = true;
            }
        })
        .leaving((user) => {
            currentMembers = currentMembers.filter(u => u.id !== user.id)
            if (user.id === props.auth.user.id) {
                callEndReason.value = 'user'
            } else {
                callEndReason.value = 'astrologer'
            }
            clearInterval(countdown.value);
            joined.value = false;
            endCall(user.id === props.auth.user.id);
            axios.post(`/astrologers/${astrologerId}/busy`, { busy: false })
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



const showCallScreen = ref(false)
const callStatus = ref('ringing...')
const muted = ref(false)
const remoteAudio = ref(null)
const localConnected = ref(false)
const remoteConnected = ref(false)
const timerStarted = ref(false)
const pendingRemoteCandidates = ref([])
const localTimestamp = ref(null)
const remoteTimestamp = ref(null)
const startTime = ref(null)
const callStartedAt = ref(null)
let pc = null
let localStream = null
let callTimeoutTimer = null

const speakerOn = ref(true)

function sendSignal(type, data) {
    axios.post('/call/signal', {
        roomId: props.chat.id,
        type,
        data
    })
}

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


const startCall = async () => {
    try {
        let currentMembers = []
        // Listen for astrologer joining
        echo.join(`call.${props.chat.id}`)
            .here(async (users) => {
                currentMembers = users
                if (users.length === 2) {
                    callStatus.value = 'connecting...'
                    const response = await axios.post('/call/start', { roomId: props.chat.id })
                    if (response.data?.call_history) {
                        liveMessages.value.push(response.data.call_history)
                    }
                }
            })
            .joining(async (user) => {
                currentMembers.push(user)
                if (currentMembers.length === 2) {
                    callStatus.value = 'connecting...'
                    const response = await axios.post('/call/start', { roomId: props.chat.id })
                    if (response.data?.call_history) {
                        liveMessages.value.push(response.data.call_history)
                    }
                }
            })
            .leaving((user) => {
                currentMembers = currentMembers.filter(u => u.id !== user.id)
                stopDurationTimer()
            })
            .listen('CallSignal', async (e) => {
                if (e.type === 'answer') {
                    if (!e.data.sdp.endsWith('\r\n')) {
                        e.data.sdp += '\r\n'
                    }
                    await pc.setRemoteDescription(e.data)
                    // Drain queued candidates after remote description is available
                    while (pendingRemoteCandidates.value.length > 0) {
                        const remoteCandidate = pendingRemoteCandidates.value.shift()
                        await pc.addIceCandidate(new RTCIceCandidate(remoteCandidate))
                    }
                } else if (e.type === 'candidate') {
                    if (pc && pc.remoteDescription) {
                        await pc.addIceCandidate(new RTCIceCandidate(e.data))
                    } else {
                        pendingRemoteCandidates.value.push(e.data)
                    }
                } else if (e.type === 'call_joined') {
                    // Astrologer has joined and accepted, now send offer
                    const offer = await pc.createOffer()
                    await pc.setLocalDescription(offer)
                    sendSignal('offer', { type: offer.type, sdp: offer.sdp })
                    callStatus.value = 'connecting...'
                } else if (e.type === 'ice_connected') {
                    remoteConnected.value = true
                    remoteTimestamp.value = e.data.timestamp
                    if (localConnected.value) {
                        startTime.value = Date.now()
                        callStatus.value = 'connected'
                        startDurationTimer()
                        startCountdown()
                    }
                } else if (e.type === 'call_ended') {
                    stopDurationTimer()
                    endCall(false)
                }
            })



        // Setup WebRTC
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
                sendSignal('candidate', event.candidate.toJSON())
            }
        }

        pc.oniceconnectionstatechange = () => {
            if (pc.iceConnectionState === 'connected') {
                localConnected.value = true
                localTimestamp.value = Date.now()
                sendSignal('ice_connected', { timestamp: localTimestamp.value })
            }
        };

        callStatus.value = 'waiting for astrologer...'
        showCallScreen.value = true
        callStartedAt.value = Date.now()

        // Set 60-second timeout for auto-disconnect if no response
        callTimeoutTimer = setTimeout(() => {
            if (callStatus.value !== 'connected') {
                callEndReason.value = 'no_response'
                endCall() //done
            }
        }, 60000)
    } catch (error) {
        console.error('[USER:startCall] Failed to start call:', error)
    }
}

const toggleMute = () => {
    muted.value = !muted.value
    localStream.getAudioTracks()[0].enabled = !muted.value
}

const toggleSpeaker = () => {
    speakerOn.value = !speakerOn.value
    // Note: Actual speaker toggle may require additional implementation
}

const endCall = async (send = false) => {
    // Clear timeout timer if set
    if (callTimeoutTimer) {
        clearTimeout(callTimeoutTimer)
        callTimeoutTimer = null
    }

    // Close peer connection
    if (pc) {
        pc.close()
        pc = null
    }

    // Stop local audio tracks
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop())
        localStream = null
    }

    // Reset remote audio
    if (remoteAudio.value) {
        remoteAudio.value.srcObject = null
    }

    // Reset UI state
    showCallScreen.value = false
    callStatus.value = 'Call ended'
    muted.value = false
    localConnected.value = false
    remoteConnected.value = false
    timerStarted.value = false
    pendingRemoteCandidates.value = []
    localTimestamp.value = null
    remoteTimestamp.value = null
    callStartedAt.value = null

    // Stop any timers
    stopDurationTimer()
    if (send) {
        callEndReason.value = 'user'
    }else{
        callEndReason.value = 'astrologer'
    }
    // Record call end if started
    if (callEndReason.value) {
        let elapsedSeconds = 0
        let status = 'ended'

        if (startTime.value) {
            elapsedSeconds = Math.floor((Date.now() - startTime.value) / 1000)
            status = elapsedSeconds > 0 ? 'answered' : 'ended'
        }
        if(callEndReason.value === 'no_response'){
            status = 'missed'
        }
        if(callEndReason.value === 'astrologer' && elapsedSeconds === 0){
            status = 'rejected'
        }

        try {
            await axios.post(route('user.call.end', props.chat.id), {
                reason: callEndReason.value,
                elapsed_seconds: elapsedSeconds,
                status: status
            })
        } catch (e) {
            console.error('Error ending call:', e)
        }
    }

    // If this side initiated the end
    if (send) {
        sendSignal('call_ended', { ended: true, reason: callEndReason.value })
        echo.leave(`call.${props.chat.id}`)
    }
}





</script>

<template>

    <Head :title="astrologer?.user?.name" />
    <UserLayout>
        <div class="flex h-[calc(100vh-4rem)] items-center justify-center bg-gray-50 my-3">
            <div class="flex flex-col w-full max-w-6xl h-full bg-white border rounded-lg shadow-lg">
                <!-- Header -->
                <div class="border-b p-4 flex items-center justify-between bg-gray-100">
                    <h2 class="font-semibold text-gray-800">
                        Call with {{ astrologer?.user?.name }}
                        <span class="text-sm ml-5" :class="astrologerOnline ? 'text-green-500' : 'text-gray-400'">
                            {{ astrologerOnline ? 'Online' : 'Offline' }}
                            <span class="text-green-500" v-if="joined"> | Joined</span>
                        </span>
                    </h2>
                    <span class="text-sm font-mono text-orange-600">
                        Time Remaining<br>
                        {{ formatTime(remainingSeconds) }}
                    </span>
                </div>

                <!-- Main content split into two columns on desktop, stacked on mobile -->
                <div class="flex flex-1 flex-col md:flex-row overflow-hidden">
                    <!-- Left: Call History -->
                    <div
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
                                    <div v-if="msg.cost != 0">Cost: ₹{{ msg.cost }}</div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ dayjs(msg.started_at).format('MMM D, h:mm A') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Call Interface -->
                    <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-6">
                        <div v-if="!showCallScreen" class="text-center">
                            <p class="text-gray-600 mb-4">Ready to start a call</p>
                            <button type="button" @click="startCall" :disabled="is_busy"
                                class="px-6 py-2 rounded-full transition" :class="is_busy
                                    ? 'bg-gray-400 text-white cursor-not-allowed'
                                    : 'bg-blue-500 text-white hover:bg-blue-600'">
                                {{ is_busy ? 'Busy' : 'Call' }}
                            </button>
                        </div>

                        <!-- Call Screen -->
                        <div v-if="showCallScreen"
                            class="w-full max-w-md bg-white rounded-lg shadow-lg p-6 flex flex-col h-[400px]">
                            <!-- Top Section -->
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-semibold">Audio Call</h3>
                                <p class="text-sm text-gray-600">{{ callStatus }}</p>
                            </div>

                            <!-- Middle Section -->
                            <div class="flex-1 flex flex-col items-center justify-center">
                                <audio ref="remoteAudio" autoplay></audio>
                                <div class="text-gray-700 mt-2 font-medium">
                                    {{ astrologer?.user?.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span v-if="callStatus === 'connected'">
                                        {{ formatDuration(callDuration) }}
                                    </span>
                                    <span v-else>
                                        {{ callStatus }}
                                    </span>
                                </div>
                            </div>

                            <!-- Bottom Section -->
                            <div class="flex justify-center space-x-6 mt-4">
                                <!-- Mute -->
                                <button @click="toggleMute"
                                    class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300">
                                    <Icon :icon="muted ? 'mdi-microphone-off' : 'mdi-microphone'" width="24" height="24"
                                        :class="muted ? 'text-red-600' : 'text-gray-700'" />
                                </button>

                                <!-- Speaker -->
                                <button @click="toggleSpeaker"
                                    class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300">
                                    <Icon :icon="speakerOn ? 'mdi-volume-high' : 'mdi-volume-off'" width="24"
                                        height="24" :class="speakerOn ? 'text-blue-600' : 'text-gray-700'" />
                                </button>

                                <!-- End/Reject Call -->
                                <button @click="endCall(true)"
                                    class="w-12 h-12 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600">
                                    <Icon icon="mdi-phone-hangup" width="24" height="24" class="text-white" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Offline notice -->
                <div v-if="!astrologerOnline" class="text-sm text-red-500 my-2 px-5">
                    Astrologer is offline. You cannot send a message.
                </div>
            </div>
        </div>
    </UserLayout>
</template>
