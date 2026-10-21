<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import dayjs from 'dayjs'
import echo from '@/echo'
import axios from 'axios'
import { Icon } from '@iconify/vue';
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

// ---------- UI-only helpers ----------
const stage = computed(() => (showCallScreen.value ? 'active' : showIncomingCall.value ? 'incoming' : 'idle'))
const isConnected = computed(() => callStatus.value === 'connected')
const initial = computed(() => (props.user?.name || '?').charAt(0).toUpperCase())

const statusTone = computed(() => {
    if (isConnected.value) return 'green'
    if (stage.value === 'incoming') return 'orange'
    if (stage.value === 'active') return 'amber'
    return 'gray'
})

const statusLabel = computed(() => {
    if (isConnected.value) return 'On call'
    return callStatus.value || 'Idle'
})

const statusMeta = {
    answered: { icon: 'mdi:phone-in-talk', tone: 'green', label: 'Answered' },
    missed: { icon: 'mdi:phone-missed', tone: 'red', label: 'Missed' },
    failed: { icon: 'mdi:phone-alert', tone: 'red', label: 'Failed' },
    rejected: { icon: 'mdi:phone-cancel', tone: 'red', label: 'Rejected' },
    ended: { icon: 'mdi:phone-hangup', tone: 'red', label: 'Ended' },
    ringing: { icon: 'mdi:phone-ring', tone: 'amber', label: 'Ringing' },
}
const metaFor = (s) => statusMeta[s] || { icon: 'mdi:phone-outline', tone: 'gray', label: s || 'Unknown' }



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
    // ask for the mic first, so a denied permission doesn't leave the UI stuck on "connecting"
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true })
    } catch (err) {
        console.error('Microphone access failed:', err)
        callStatus.value = 'Microphone access denied'
        return
    }

    showIncomingCall.value = false
    showCallScreen.value = true
    callStatus.value = 'connecting...'

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
    if (!localStream) return
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
        messagesContainer.value.scrollTo({ top: messagesContainer.value.scrollHeight, behavior: 'smooth' })
    }
}

watch(liveMessages, async () => {
    await nextTick()
    scrollToBottom()
}, { deep: true })


</script>

<template>

    <Head :title="user?.name" />

    <div class="cc">
        <!-- remote audio lives outside the animated stage so the ref is always available -->
        <audio ref="remoteAudio" autoplay></audio>

        <!-- Header -->
        <header class="cc-head">
            <span class="adm-avatar cc-head-av">{{ initial }}</span>
            <div class="cc-head-main">
                <strong>{{ user.name }}</strong>
                <span class="cc-chip" :class="`is-${statusTone}`">
                    <i></i>{{ statusLabel }}
                </span>
            </div>
            <Transition name="cc-fade">
                <span v-if="isConnected" class="cc-head-timer">{{ formatDuration(callDuration) }}</span>
            </Transition>
        </header>

        <div class="cc-grid">
            <!-- Left: call history -->
            <section class="cc-history">
                <div class="cc-history-head">
                    <h3>Call history</h3>
                    <span class="adm-chip">{{ liveMessages.length }}</span>
                </div>

                <div ref="messagesContainer" class="cc-history-list">
                    <div v-if="!liveMessages.length" class="cc-empty">
                        <Icon icon="mdi:phone-clock" width="40" height="40" />
                        <span>No calls with {{ user.name }} yet</span>
                    </div>

                    <div v-for="(msg, i) in liveMessages" :key="msg.id" class="cc-item"
                        :style="{ '--i': Math.min(i, 8) }">
                        <span class="cc-item-ico" :class="`is-${metaFor(msg.status).tone}`">
                            <Icon :icon="metaFor(msg.status).icon" width="20" height="20" />
                        </span>

                        <div class="cc-item-main">
                            <div class="cc-item-top">
                                <strong :class="`t-${metaFor(msg.status).tone}`">{{ metaFor(msg.status).label }}</strong>
                                <span v-if="msg.call_type" class="adm-chip" style="text-transform: capitalize">{{ msg.call_type }}</span>
                            </div>
                            <span class="adm-muted">
                                {{ dayjs(msg.started_at).format('MMM D, h:mm A') }}
                                <template v-if="msg.duration"> · {{ formatDuration(+msg.duration) }}</template>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right: call stage -->
            <section class="cc-stage" :class="`is-${stage}`">
                <span class="cc-glow"></span>

                <Transition name="cc-swap" mode="out-in">
                    <!-- Idle -->
                    <div v-if="stage === 'idle'" key="idle" class="cc-panel">
                        <div class="cc-avatar-wrap">
                            <span class="cc-avatar">{{ initial }}</span>
                        </div>
                        <h3 class="cc-name">{{ user.name }}</h3>
                        <p class="cc-sub">
                            {{ callStatus && callStatus !== 'Call ended' ? callStatus : callStatus === 'Call ended' ? 'Call ended' : 'Waiting for an incoming call' }}
                        </p>
                        <div class="cc-idle-hint">
                            <Icon icon="mdi:phone-outline" width="18" height="18" />
                            Calls from this user will ring here
                        </div>
                    </div>

                    <!-- Incoming -->
                    <div v-else-if="stage === 'incoming'" key="incoming" class="cc-panel">
                        <p class="cc-kicker">Incoming call</p>
                        <div class="cc-avatar-wrap is-ringing">
                            <span class="cc-ripple"></span>
                            <span class="cc-ripple cc-ripple-2"></span>
                            <span class="cc-ripple cc-ripple-3"></span>
                            <span class="cc-avatar">{{ initial }}</span>
                        </div>
                        <h3 class="cc-name">{{ user.name }}</h3>
                        <p class="cc-sub cc-blink">{{ callStatus }}</p>

                        <div class="cc-actions">
                            <div class="cc-action">
                                <button type="button" class="cc-btn cc-btn-end" aria-label="Decline" @click="endCall(true)">
                                    <Icon icon="mdi:phone-hangup" width="28" height="28" />
                                </button>
                                <span>Decline</span>
                            </div>
                            <div class="cc-action">
                                <button type="button" class="cc-btn cc-btn-accept" aria-label="Accept" @click="acceptCall">
                                    <Icon icon="mdi:phone" width="28" height="28" class="cc-shake" />
                                </button>
                                <span>Accept</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active -->
                    <div v-else key="active" class="cc-panel">
                        <p class="cc-kicker">{{ isConnected ? 'Call in progress' : 'Connecting' }}</p>

                        <div class="cc-avatar-wrap" :class="isConnected ? 'is-live' : 'is-connecting'">
                            <span class="cc-spinner"></span>
                            <span class="cc-ripple cc-ripple-live"></span>
                            <span class="cc-ripple cc-ripple-live cc-ripple-2"></span>
                            <span class="cc-avatar">{{ initial }}</span>
                            <Transition name="cc-pop">
                                <span v-if="muted" class="cc-mute-badge">
                                    <Icon icon="mdi:microphone-off" width="16" height="16" />
                                </span>
                            </Transition>
                        </div>

                        <h3 class="cc-name">{{ user.name }}</h3>

                        <div v-if="isConnected" class="cc-timer">{{ formatDuration(callDuration) }}</div>
                        <p v-else class="cc-sub cc-blink">{{ callStatus }}</p>

                        <!-- sound bars -->
                        <div class="cc-bars" :class="{ 'is-on': isConnected && !muted }" aria-hidden="true">
                            <span v-for="n in 7" :key="n" :style="{ '--n': n }"></span>
                        </div>

                        <div class="cc-actions">
                            <div class="cc-action">
                                <button type="button" class="cc-btn cc-btn-mute" :class="{ 'is-on': muted }"
                                    :aria-pressed="muted" aria-label="Mute" @click="toggleMute">
                                    <Icon :icon="muted ? 'mdi:microphone-off' : 'mdi:microphone'" width="26" height="26" />
                                </button>
                                <span>{{ muted ? 'Unmute' : 'Mute' }}</span>
                            </div>
                            <div class="cc-action">
                                <button type="button" class="cc-btn cc-btn-end" aria-label="End call" @click="endCall(true)">
                                    <Icon icon="mdi:phone-hangup" width="28" height="28" />
                                </button>
                                <span>End call</span>
                            </div>
                        </div>
                    </div>
                </Transition>
            </section>
        </div>
    </div>
</template>

<style>
.cc {
    display: flex;
    height: 100%;
    min-height: 0;
    flex-direction: column;
    background: #fffdfb;
}

/* ---------- Header ---------- */
.cc-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.8rem 1.1rem;
    border-bottom: 1px solid #ffedd5;
    background: linear-gradient(90deg, #fff7ed, #fff);
}

.cc-head-av {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
}

.cc-head-main {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 0.15rem;
}

.cc-head-main strong {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.95rem;
    color: #1f2937;
}

.cc-head-timer {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background: #dcfce7;
    font-size: 0.85rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #15803d;
}

.cc-chip {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: #9ca3af;
    transition: color 0.3s ease;
}

.cc-chip i {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background: #d1d5db;
    transition: background 0.3s ease;
}

.cc-chip.is-green { color: #16a34a; }
.cc-chip.is-green i { background: #22c55e; animation: cc-dot 2s ease-out infinite; }
.cc-chip.is-orange { color: #ea580c; }
.cc-chip.is-orange i { background: #ff7010; animation: cc-dot-o 1.2s ease-out infinite; }
.cc-chip.is-amber { color: #b45309; }
.cc-chip.is-amber i { background: #f59e0b; }

/* ---------- Layout ---------- */
.cc-grid {
    display: flex;
    min-height: 0;
    flex: 1;
    flex-direction: column;
}

.cc-stage {
    order: -1;
    flex: 0 0 auto;
    min-height: 21rem;
}

.cc-history {
    display: flex;
    min-height: 0;
    flex: 1;
    flex-direction: column;
    border-top: 1px solid #ffedd5;
}

@media (min-width: 768px) {
    .cc-grid {
        flex-direction: row;
    }

    .cc-stage {
        order: 0;
        flex: 1 1 50%;
        min-height: 0;
    }

    .cc-history {
        flex: 1 1 50%;
        border-top: 0;
        border-right: 1px solid #ffedd5;
    }
}

/* ---------- History ---------- */
.cc-history-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.9rem 1.1rem;
    border-bottom: 1px solid #ffedd5;
    background: #fff;
}

.cc-history-head h3 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2937;
}

.cc-history-list {
    display: flex;
    min-height: 0;
    flex: 1;
    flex-direction: column;
    gap: 0.6rem;
    overflow-y: auto;
    padding: 1rem 1.1rem;
    background: #fffaf5;
}

.cc-empty {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2rem 1rem;
    color: #9ca3af;
}

.cc-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.75rem 0.9rem;
    border: 1px solid #ffe8d4;
    border-radius: 0.9rem;
    background: #fff;
    box-shadow: 0 1px 2px rgba(124, 45, 18, 0.05);
    animation: cc-rise 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: calc(var(--i, 0) * 50ms);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.cc-item:hover {
    transform: translateY(-2px);
    border-color: #fdba74;
    box-shadow: 0 12px 24px -14px rgba(124, 45, 18, 0.35);
}

.cc-item-ico {
    display: flex;
    width: 2.5rem;
    height: 2.5rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.8rem;
}

.cc-item-ico.is-green { background: #dcfce7; color: #16a34a; }
.cc-item-ico.is-red { background: #fee2e2; color: #dc2626; }
.cc-item-ico.is-amber { background: #fef9c3; color: #ca8a04; }
.cc-item-ico.is-gray { background: #f3f4f6; color: #6b7280; }

.cc-item-main {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    gap: 0.1rem;
}

.cc-item-top {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cc-item-top strong {
    font-size: 0.9rem;
}

.t-green { color: #15803d; }
.t-red { color: #b91c1c; }
.t-amber { color: #a16207; }
.t-gray { color: #4b5563; }

/* ---------- Stage ---------- */
.cc-stage {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 1.5rem;
    background: linear-gradient(180deg, #fff7ed 0%, #fff 70%);
    transition: background 0.6s ease;
}

.cc-stage.is-incoming {
    background: linear-gradient(180deg, #ffedd5 0%, #fff7ed 70%);
}

.cc-stage.is-active {
    background: linear-gradient(180deg, #dcfce7 0%, #f0fdf4 45%, #fff 100%);
}

.cc-glow {
    position: absolute;
    top: -6rem;
    left: 50%;
    width: 22rem;
    height: 22rem;
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(255, 112, 16, 0.22), transparent 65%);
    transform: translateX(-50%);
    pointer-events: none;
    transition: background 0.6s ease;
}

.cc-stage.is-active .cc-glow {
    background: radial-gradient(circle, rgba(34, 197, 94, 0.25), transparent 65%);
}

.cc-panel {
    position: relative;
    display: flex;
    width: 100%;
    max-width: 22rem;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.cc-kicker {
    margin: 0 0 1.1rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #c2410c;
}

.cc-stage.is-active .cc-kicker {
    color: #15803d;
}

.cc-name {
    margin: 1.1rem 0 0;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.cc-sub {
    margin: 0.3rem 0 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.cc-blink {
    animation: cc-blink 1.4s ease-in-out infinite;
}

.cc-timer {
    margin-top: 0.35rem;
    font-size: 2.1rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.04em;
    color: #15803d;
}

.cc-idle-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 1.25rem;
    padding: 0.45rem 0.9rem;
    border: 1px dashed #fdba74;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    color: #c2410c;
}

/* avatar + rings */
.cc-avatar-wrap {
    position: relative;
    display: flex;
    width: 7rem;
    height: 7rem;
    align-items: center;
    justify-content: center;
}

.cc-avatar {
    position: relative;
    z-index: 2;
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    border: 4px solid #fff;
    border-radius: 9999px;
    background: linear-gradient(135deg, #ff7010, #ff9248);
    font-size: 2.6rem;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 18px 34px -12px rgba(255, 112, 16, 0.6);
    transition: background 0.5s ease, box-shadow 0.5s ease;
}

.cc-avatar-wrap.is-live .cc-avatar {
    background: linear-gradient(135deg, #16a34a, #4ade80);
    box-shadow: 0 18px 34px -12px rgba(22, 163, 74, 0.6);
}

.cc-ripple {
    position: absolute;
    inset: 0;
    z-index: 1;
    border-radius: 9999px;
    background: rgba(255, 112, 16, 0.35);
    opacity: 0;
    pointer-events: none;
}

.cc-avatar-wrap.is-ringing .cc-ripple {
    animation: cc-ripple 2.2s cubic-bezier(0.2, 0.6, 0.3, 1) infinite;
}

.cc-ripple-2 { animation-delay: 0.7s !important; }
.cc-ripple-3 { animation-delay: 1.4s !important; }

.cc-ripple-live {
    background: rgba(34, 197, 94, 0.35);
}

.cc-avatar-wrap.is-live .cc-ripple-live {
    animation: cc-ripple 3s cubic-bezier(0.2, 0.6, 0.3, 1) infinite;
}

.cc-avatar-wrap.is-live .cc-ripple-2 {
    animation-delay: 1.5s !important;
}

.cc-spinner {
    position: absolute;
    inset: -0.55rem;
    z-index: 1;
    border: 3px solid transparent;
    border-top-color: #f59e0b;
    border-right-color: #fcd34d;
    border-radius: 9999px;
    opacity: 0;
}

.cc-avatar-wrap.is-connecting .cc-spinner {
    opacity: 1;
    animation: cc-spin 1s linear infinite;
}

.cc-mute-badge {
    position: absolute;
    right: -0.1rem;
    bottom: 0.1rem;
    z-index: 3;
    display: flex;
    width: 1.9rem;
    height: 1.9rem;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    border-radius: 9999px;
    background: #ef4444;
    color: #fff;
}

/* sound bars */
.cc-bars {
    display: flex;
    height: 2rem;
    align-items: center;
    gap: 0.28rem;
    margin-top: 0.9rem;
}

.cc-bars span {
    width: 0.28rem;
    height: 0.3rem;
    border-radius: 9999px;
    background: #86efac;
    transition: height 0.3s ease, background 0.3s ease;
}

.cc-bars.is-on span {
    background: #22c55e;
    animation: cc-wave 1s ease-in-out infinite;
    animation-delay: calc(var(--n) * 0.11s);
}

/* action buttons */
.cc-actions {
    display: flex;
    justify-content: center;
    gap: 2.25rem;
    margin-top: 1.5rem;
}

.cc-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
}

.cc-btn {
    position: relative;
    display: flex;
    width: 4rem;
    height: 4rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9999px;
    color: #fff;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.cc-btn:hover {
    transform: translateY(-3px) scale(1.06);
}

.cc-btn:active {
    transform: scale(0.92);
}

.cc-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 112, 16, 0.35);
}

.cc-btn-accept {
    background: #16a34a;
    box-shadow: 0 14px 26px -8px rgba(22, 163, 74, 0.7);
    animation: cc-glow-green 1.6s ease-out infinite;
}

.cc-btn-accept:hover { background: #15803d; }

.cc-btn-end {
    background: #ef4444;
    box-shadow: 0 14px 26px -8px rgba(239, 68, 68, 0.7);
}

.cc-btn-end:hover { background: #dc2626; }

.cc-btn-end:hover svg {
    transform: rotate(135deg);
}

.cc-btn svg {
    transition: transform 0.3s ease;
}

.cc-btn-mute {
    background: #fff;
    color: #374151;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 18px -10px rgba(0, 0, 0, 0.3);
}

.cc-btn-mute:hover { background: #f9fafb; }

.cc-btn-mute.is-on {
    border-color: #fecaca;
    background: #fee2e2;
    color: #dc2626;
}

.cc-shake {
    animation: cc-shake 1.2s ease-in-out infinite;
}

/* ---------- Transitions ---------- */
.cc-swap-enter-active,
.cc-swap-leave-active {
    transition: opacity 0.3s ease, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}

.cc-swap-enter-from {
    opacity: 0;
    transform: translateY(18px) scale(0.96);
}

.cc-swap-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(0.98);
}

.cc-fade-enter-active,
.cc-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.cc-fade-enter-from,
.cc-fade-leave-to {
    opacity: 0;
    transform: scale(0.85);
}

.cc-pop-enter-active {
    animation: cc-pop 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.cc-pop-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.cc-pop-leave-to {
    opacity: 0;
    transform: scale(0.4);
}

/* ---------- Keyframes ---------- */
@keyframes cc-ripple {
    0% { opacity: 0.7; transform: scale(1); }
    100% { opacity: 0; transform: scale(2.1); }
}

@keyframes cc-spin {
    to { transform: rotate(360deg); }
}

@keyframes cc-shake {
    0%, 55%, 100% { transform: rotate(0); }
    10% { transform: rotate(-16deg); }
    20% { transform: rotate(14deg); }
    30% { transform: rotate(-12deg); }
    40% { transform: rotate(10deg); }
    50% { transform: rotate(-6deg); }
}

@keyframes cc-glow-green {
    0% { box-shadow: 0 14px 26px -8px rgba(22, 163, 74, 0.7), 0 0 0 0 rgba(34, 197, 94, 0.55); }
    70%, 100% { box-shadow: 0 14px 26px -8px rgba(22, 163, 74, 0.7), 0 0 0 1.1rem rgba(34, 197, 94, 0); }
}

@keyframes cc-wave {
    0%, 100% { height: 0.3rem; }
    50% { height: 1.9rem; }
}

@keyframes cc-blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.45; }
}

@keyframes cc-pop {
    from { opacity: 0; transform: scale(0.3); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes cc-rise {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: none; }
}

@keyframes cc-dot {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55); }
    70%, 100% { box-shadow: 0 0 0 0.45rem rgba(34, 197, 94, 0); }
}

@keyframes cc-dot-o {
    0% { box-shadow: 0 0 0 0 rgba(255, 112, 16, 0.6); }
    70%, 100% { box-shadow: 0 0 0 0.45rem rgba(255, 112, 16, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .cc *,
    .cc *::before,
    .cc *::after {
        animation: none !important;
        transition-duration: 0.01ms !important;
    }
}
</style>