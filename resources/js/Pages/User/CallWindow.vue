<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
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
const startingCall = ref(false)
const startCallError = ref('')
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
    if (startingCall.value) return

    startingCall.value = true
    startCallError.value = ''
    try {
        let currentMembers = []
        // Listen for astrologer joining
        echo.join(`call.${props.chat.id}`)
            .here((users) => {
                currentMembers = users
                if (users.length === 2) {
                    callStatus.value = 'connecting...'
                }
            })
            .joining((user) => {
                currentMembers.push(user)
                if (currentMembers.length === 2) {
                    callStatus.value = 'connecting...'
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

        const response = await axios.post('/call/start', { roomId: props.chat.id })
        if (response.data?.call_history) {
            liveMessages.value.push(response.data.call_history)
        }

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
        startCallError.value = error.response?.data?.message ?? 'Unable to start call. Please try again.'
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop())
            localStream = null
        }
        if (pc) {
            pc.close()
            pc = null
        }
        echo.leave(`call.${props.chat.id}`)
    } finally {
        startingCall.value = false
    }
}

const toggleMute = () => {
    muted.value = !muted.value
    localStream.getAudioTracks()[0].enabled = !muted.value
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






// ---------- presentation helpers (no behaviour change) ----------
const astrologerName = computed(() => props.astrologer?.user?.name ?? 'Astrologer')
const avatarSrc = computed(() => {
    const img = props.astrologer?.profile_image
    if (!img) return null
    return /^(https?:)?\//.test(img) ? img : `/${img}`
})

// ringing | connecting | connected  (derived from callStatus)
const phase = computed(() =>
    callStatus.value === 'connected' ? 'connected' : callStatus.value === 'connecting...' ? 'connecting' : 'ringing'
)
const phaseText = computed(() => ({ ringing: 'Ringing', connecting: 'Connecting', connected: 'Connected' }[phase.value]))

const talkMinutes = isFinite(totalSeconds) ? Math.floor(totalSeconds / 60) : 0
const balanceProgress = computed(() => {
    if (!isFinite(totalSeconds) || totalSeconds <= 0) return 0
    return Math.max(0, Math.min(1, remainingSeconds.value / totalSeconds))
})
const lowBalance = computed(() => phase.value === 'connected' && remainingSeconds.value > 0 && remainingSeconds.value <= 60)

const statusMeta = (s) => ({
    answered: { icon: 'mdi:phone-check', cls: 'is-ok', label: 'Answered' },
    missed: { icon: 'mdi:phone-missed', cls: 'is-bad', label: 'Missed' },
    rejected: { icon: 'mdi:phone-cancel', cls: 'is-bad', label: 'Rejected' },
    failed: { icon: 'mdi:phone-alert', cls: 'is-bad', label: 'Failed' },
    ended: { icon: 'mdi:phone-hangup', cls: 'is-bad', label: 'Ended' },
    ringing: { icon: 'mdi:phone-ring', cls: 'is-wait', label: 'Ringing' },
}[s] || { icon: 'mdi:phone', cls: 'is-wait', label: s || '—' })

const fmtSecs = (sec) => {
    sec = Math.round(+sec || 0)
    const m = Math.floor(sec / 60)
    const s = sec % 60
    return m ? `${m}m ${s}s` : `${s}s`
}

const historyList = computed(() => [...liveMessages.value].reverse())

const bars = Array.from({ length: 24 }, (_, i) => ({ d: ((i * 37) % 11) / 10, h: 40 + ((i * 53) % 60) }))
</script>

<template>

    <Head :title="astrologerName" />
    <UserLayout immersive>
        <div class="call-stage">
            <div class="call-shell">
                <!-- Header -->
                <div class="call-header">
                    <div class="call-who">
                        <div class="call-avatar-sm" :class="{ 'is-online': astrologerOnline }">
                            <img v-if="avatarSrc" :src="avatarSrc" :alt="astrologerName" />
                            <span v-else>{{ astrologerName.charAt(0).toUpperCase() }}</span>
                            <i></i>
                        </div>
                        <div>
                            <h2>Call with {{ astrologerName }}</h2>
                            <p>
                                <span :class="astrologerOnline ? 'is-on' : 'is-off'">{{ astrologerOnline ? 'Online' : 'Offline' }}</span>
                                <span v-if="joined" class="is-on"><Icon icon="mdi:check-all" width="14" height="14" /> Joined</span>
                                <span v-if="astrologerRate" class="call-rate">₹{{ astrologerRate }}/min</span>
                            </p>
                        </div>
                    </div>

                    <div class="call-remaining">
                        <span>Time remaining</span>
                        <b>{{ formatTime(remainingSeconds) }}</b>
                    </div>
                </div>

                <Transition name="strip">
                    <div v-if="!astrologerOnline" class="call-strip">
                        <Icon icon="mdi:cloud-off-outline" width="18" height="18" />
                        Astrologer is offline right now.
                    </div>
                </Transition>

                <!-- Main content: call panel + history -->
                <div class="call-main">
                    <!-- Call panel -->
                    <section class="call-panel">
                        <div class="call-panel-bg"></div>

                        <div class="call-ready">
                            <div class="call-hero" :class="{ 'is-online': astrologerOnline && !is_busy }">
                                <span class="call-hero-ring r1"></span>
                                <span class="call-hero-ring r2"></span>
                                <div class="call-hero-avatar">
                                    <img v-if="avatarSrc" :src="avatarSrc" :alt="astrologerName" />
                                    <span v-else>{{ astrologerName.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>

                            <h3>{{ astrologerName }}</h3>
                            <p class="call-sub">
                                <span class="call-pill" :class="is_busy ? 'is-busy' : 'is-free'">
                                    <i></i>{{ is_busy ? 'Busy on another call' : 'Available' }}
                                </span>
                            </p>

                            <div class="call-facts">
                                <div><strong>₹{{ astrologerRate }}</strong><span>per minute</span></div>
                                <div><strong>{{ talkMinutes }} min</strong><span>you can talk</span></div>
                            </div>

                            <button type="button" @click="startCall" :disabled="is_busy || startingCall" class="call-btn"
                                :class="{ 'is-busy': is_busy }" :aria-label="is_busy ? 'Busy' : 'Call'">
                                <span class="call-btn-ripple"></span>
                                <span class="call-btn-ripple r2"></span>
                                <Icon :icon="startingCall ? 'mdi:loading' : is_busy ? 'mdi:phone-lock' : 'mdi:phone'"
                                    :class="{ 'call-spin': startingCall }" width="30" height="30" />
                            </button>
                            <p class="call-btn-label">{{ startingCall ? 'Starting call…' : is_busy ? 'Busy' : 'Tap to call' }}</p>
                            <p v-if="startCallError" class="call-ended-note" role="alert">{{ startCallError }}</p>

                            <Transition name="strip">
                                <p v-if="callStatus === 'Call ended'" class="call-ended-note">
                                    <Icon icon="mdi:phone-hangup" width="16" height="16" />
                                    Call ended<span v-if="callDuration"> · {{ formatDuration(callDuration) }}</span>
                                </p>
                            </Transition>
                        </div>
                    </section>

                    <!-- History -->
                    <aside class="call-history">
                        <h3><Icon icon="mdi:phone-log-outline" width="20" height="20" /> Call History</h3>

                        <div v-if="!historyList.length" class="call-history-empty">
                            <Icon icon="mdi:phone-off-outline" width="34" height="34" />
                            <span>No calls with {{ astrologerName }} yet</span>
                        </div>

                        <TransitionGroup name="hist" tag="div" class="call-history-list">
                            <div v-for="msg in historyList" :key="msg.id" class="call-item" :class="statusMeta(msg.status).cls">
                                <span class="call-item-icon">
                                    <Icon :icon="statusMeta(msg.status).icon" width="20" height="20" />
                                </span>
                                <div class="call-item-main">
                                    <b>{{ statusMeta(msg.status).label }} <em>· {{ msg.call_type }}</em></b>
                                    <span>{{ dayjs(msg.started_at).format('MMM D, h:mm A') }}</span>
                                </div>
                                <div class="call-item-end">
                                    <b v-if="msg.duration">{{ fmtSecs(msg.duration) }}</b>
                                    <span v-if="msg.cost != 0">₹{{ msg.cost }}</span>
                                </div>
                            </div>
                        </TransitionGroup>
                    </aside>
                </div>
            </div>
        </div>

        <!-- Full-screen call screen -->
        <Transition name="callscreen">
            <div v-if="showCallScreen" class="cs" :class="`is-${phase}`">
                <div class="cs-bg"></div>
                <span v-for="n in 14" :key="n" class="cs-star"
                    :style="{ top: (n * 37) % 100 + '%', left: (n * 53) % 100 + '%', animationDelay: (n % 6) * 0.45 + 's' }"></span>

                <audio ref="remoteAudio" autoplay></audio>

                <div class="cs-top">
                    <span class="cs-badge"><Icon icon="mdi:lock-outline" width="14" height="14" /> Audio call</span>
                    <span v-if="phase === 'connected'" class="cs-balance" :class="{ 'is-low': lowBalance }">
                        <Icon icon="mdi:timer-sand" width="14" height="14" /> {{ formatTime(remainingSeconds) }} left
                    </span>
                </div>

                <div class="cs-center">
                    <div class="cs-avatar-wrap">
                        <span class="cs-ring r1"></span>
                        <span class="cs-ring r2"></span>
                        <span class="cs-ring r3"></span>
                        <div class="cs-avatar">
                            <img v-if="avatarSrc" :src="avatarSrc" :alt="astrologerName" />
                            <span v-else>{{ astrologerName.charAt(0).toUpperCase() }}</span>
                        </div>
                    </div>

                    <h2>{{ astrologerName }}</h2>

                    <p v-if="phase !== 'connected'" class="cs-status">
                        {{ phaseText }}<span class="cs-dots"><i>.</i><i>.</i><i>.</i></span>
                    </p>
                    <p v-else class="cs-timer">{{ formatDuration(callDuration) }}</p>

                    <!-- equalizer -->
                    <div class="cs-eq" :class="{ 'is-live': phase === 'connected' && !muted }" aria-hidden="true">
                        <i v-for="(b, i) in bars" :key="i" :style="{ animationDelay: b.d + 's', '--h': b.h + '%' }"></i>
                    </div>

                    <div v-if="phase === 'connected'" class="cs-meter" :class="{ 'is-low': lowBalance }">
                        <div :style="{ width: balanceProgress * 100 + '%' }"></div>
                    </div>
                </div>

                <div class="cs-controls">
                    <div class="cs-control">
                        <button @click="toggleMute" class="cs-round" :class="{ 'is-on': muted }"
                            :aria-label="muted ? 'Unmute' : 'Mute'">
                            <Icon :icon="muted ? 'mdi-microphone-off' : 'mdi-microphone'" width="26" height="26" />
                        </button>
                        <span>{{ muted ? 'Unmute' : 'Mute' }}</span>
                    </div>

                    <div class="cs-control">
                        <button @click="endCall(true)" class="cs-round cs-end" aria-label="End call">
                            <span class="cs-end-ping"></span>
                            <Icon icon="mdi-phone-hangup" width="30" height="30" />
                        </button>
                        <span>End</span>
                    </div>
                </div>
            </div>
        </Transition>
    </UserLayout>
</template>

<style scoped>
.call-spin {
    animation: call-spin 1s linear infinite;
}

@keyframes call-spin {
    to {
        transform: rotate(360deg);
    }
}

.call-stage {
    display: flex;
    height: 100%;
    justify-content: center;
    padding: 0.75rem;
}

.call-shell {
    display: flex;
    width: 100%;
    max-width: 72rem;
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
.call-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.2rem;
    border-bottom: 1px solid #ffe8d4;
    background: linear-gradient(90deg, #fff7ed, #fff);
}

.call-who {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 0.8rem;
}

.call-who h2 {
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}

.call-who p {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem 0.7rem;
    margin: 0.1rem 0 0;
    font-size: 0.78rem;
}

.call-who .is-on { display: inline-flex; align-items: center; gap: 0.15rem; color: #16a34a; font-weight: 600; }
.call-who .is-off { color: #9ca3af; font-weight: 600; }

.call-rate {
    padding: 0.05rem 0.5rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-weight: 600;
    color: #c2410c;
}

.call-avatar-sm {
    position: relative;
    display: flex;
    width: 2.9rem;
    height: 2.9rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
}

.call-avatar-sm img {
    width: 100%;
    height: 100%;
    border-radius: 9999px;
    object-fit: cover;
}

.call-avatar-sm i {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 0.8rem;
    height: 0.8rem;
    border: 2px solid #fff;
    border-radius: 9999px;
    background: #9ca3af;
}

.call-avatar-sm.is-online i {
    background: #22c55e;
}

.call-remaining {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.call-remaining span {
    font-size: 0.68rem;
    font-weight: 600;
    color: #9ca3af;
}

.call-remaining b {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 1.05rem;
    color: #c2410c;
}

.call-strip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.2rem;
    background: #fee2e2;
    font-size: 0.82rem;
    font-weight: 500;
    color: #b91c1c;
}

.strip-enter-active,
.strip-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.strip-enter-from,
.strip-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* ---- main ---- */
.call-main {
    display: flex;
    flex: 1;
    min-height: 0;
    flex-direction: column;
    overflow-y: auto;
}

@media (min-width: 900px) {
    .call-main {
        flex-direction: row;
        overflow: hidden;
    }
}

/* ---- ready panel ---- */
.call-panel {
    position: relative;
    display: flex;
    flex: 1.15;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 2rem 1.25rem;
    background: #fffaf5;
}

.call-panel-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 15%, rgba(255, 112, 16, 0.12) 0, transparent 32%),
        radial-gradient(circle at 85% 85%, rgba(252, 211, 77, 0.2) 0, transparent 34%);
    pointer-events: none;
}

.call-ready {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.call-hero {
    position: relative;
    display: flex;
    width: 10rem;
    height: 10rem;
    align-items: center;
    justify-content: center;
}

.call-hero-ring {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid #FF7010;
    opacity: 0;
}

.call-hero.is-online .call-hero-ring {
    animation: call-ring 3s ease-out infinite;
}

.call-hero-ring.r2 {
    animation-delay: 1.5s !important;
}

.call-hero-avatar {
    position: relative;
    display: flex;
    width: 7rem;
    height: 7rem;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 4px solid #fff;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    font-size: 2.6rem;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 18px 36px -10px rgba(255, 112, 16, 0.6);
    animation: call-breathe 4s ease-in-out infinite;
}

.call-hero-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.call-ready h3 {
    margin: 1rem 0 0.4rem;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.call-sub {
    margin: 0;
}

.call-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.8rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
}

.call-pill i {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background: currentColor;
}

.call-pill.is-free { background: #dcfce7; color: #15803d; }
.call-pill.is-free i { animation: call-blink 1.6s ease-in-out infinite; }
.call-pill.is-busy { background: #fee2e2; color: #b91c1c; }

.call-facts {
    display: flex;
    gap: 0.8rem;
    margin: 1.4rem 0 1.8rem;
}

.call-facts div {
    min-width: 7rem;
    padding: 0.7rem 1rem;
    border: 1px solid #ffe8d4;
    border-radius: 0.95rem;
    background: #fff;
}

.call-facts strong {
    display: block;
    font-size: 1.1rem;
    color: #1f2937;
}

.call-facts span {
    font-size: 0.72rem;
    font-weight: 600;
    color: #9ca3af;
}

.call-btn {
    position: relative;
    display: flex;
    width: 4.8rem;
    height: 4.8rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9999px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    cursor: pointer;
    box-shadow: 0 16px 30px -8px rgba(22, 163, 74, 0.7);
    transition: transform 0.25s ease;
}

.call-btn:hover:not(:disabled) {
    transform: scale(1.08);
}

.call-btn:hover:not(:disabled) svg {
    animation: call-shake 0.6s ease-in-out infinite;
}

.call-btn-ripple {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid #22c55e;
    animation: call-ping 2s ease-out infinite;
    pointer-events: none;
}

.call-btn-ripple.r2 {
    animation-delay: 1s;
}

.call-btn.is-busy {
    background: #9ca3af;
    box-shadow: none;
    cursor: not-allowed;
}

.call-btn.is-busy .call-btn-ripple {
    display: none;
}

.call-btn-label {
    margin: 0.7rem 0 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
}

.call-ended-note {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin: 1rem 0 0;
    padding: 0.35rem 0.9rem;
    border-radius: 9999px;
    background: #f3f4f6;
    font-size: 0.82rem;
    font-weight: 600;
    color: #4b5563;
}

/* ---- history ---- */
.call-history {
    display: flex;
    flex: 0.85;
    min-height: 16rem;
    flex-direction: column;
    overflow-y: auto;
    padding: 1.25rem;
    border-top: 1px solid #ffe8d4;
    background: #fff;
}

@media (min-width: 900px) {
    .call-history {
        border-top: 0;
        border-left: 1px solid #ffe8d4;
    }
}

.call-history h3 {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.9rem;
    font-size: 1rem;
    font-weight: 700;
    color: #374151;
}

.call-history h3 svg {
    color: #FF7010;
}

.call-history-empty {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #b0b7c3;
    font-size: 0.85rem;
}

.call-history-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.call-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.8rem;
    border: 1px solid #ffe8d4;
    border-radius: 0.95rem;
    background: #fffaf5;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.call-item:hover {
    transform: translateX(3px);
    box-shadow: 0 10px 20px -12px rgba(124, 45, 18, 0.35);
}

.call-item-icon {
    display: flex;
    width: 2.4rem;
    height: 2.4rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.8rem;
}

.call-item.is-ok .call-item-icon { background: #dcfce7; color: #16a34a; }
.call-item.is-bad .call-item-icon { background: #fee2e2; color: #dc2626; }
.call-item.is-wait .call-item-icon { background: #fef3c7; color: #d97706; }

.call-item-main {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
}

.call-item-main b {
    font-size: 0.9rem;
    color: #1f2937;
}

.call-item-main em {
    font-style: normal;
    font-weight: 500;
    color: #9ca3af;
    text-transform: capitalize;
}

.call-item-main span,
.call-item-end span {
    font-size: 0.75rem;
    color: #9ca3af;
}

.call-item-end {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.call-item-end b {
    font-size: 0.85rem;
    color: #374151;
}

.hist-enter-active {
    transition: opacity 0.45s ease, transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

.hist-enter-from {
    opacity: 0;
    transform: translateY(-14px) scale(0.96);
}

.hist-move {
    transition: transform 0.35s ease;
}

/* ================= full-screen call ================= */
.cs {
    position: fixed;
    inset: 0;
    z-index: 70;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    padding: 1.5rem 1.25rem 2.5rem;
    color: #fff;
}

.cs-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 50% 28%, rgba(255, 112, 16, 0.55) 0, transparent 55%),
        linear-gradient(180deg, #2a1206 0%, #5a230a 55%, #8a3510 100%);
    transition: filter 1s ease;
}

.cs.is-connected .cs-bg {
    filter: hue-rotate(-8deg) saturate(1.1);
}

.cs-star {
    position: absolute;
    width: 3px;
    height: 3px;
    border-radius: 9999px;
    background: #fde7c8;
    animation: call-twinkle 3s ease-in-out infinite;
}

.cs-top {
    position: relative;
    display: flex;
    width: 100%;
    max-width: 30rem;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
}

.cs-badge,
.cs-balance {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.8rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.14);
    font-size: 0.78rem;
    font-weight: 600;
    backdrop-filter: blur(6px);
}

.cs-balance.is-low {
    background: rgba(239, 68, 68, 0.85);
    animation: call-throb 1s ease-in-out infinite;
}

.cs-center {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.cs-avatar-wrap {
    position: relative;
    display: flex;
    width: 15rem;
    height: 15rem;
    align-items: center;
    justify-content: center;
}

.cs-ring {
    position: absolute;
    inset: 2.5rem;
    border-radius: 9999px;
    border: 2px solid rgba(255, 176, 102, 0.85);
    opacity: 0;
    animation: call-ring 2.6s ease-out infinite;
}

.cs-ring.r2 { animation-delay: 0.85s; }
.cs-ring.r3 { animation-delay: 1.7s; }

/* when connected, rings become a calm steady glow */
.cs.is-connected .cs-ring {
    border-color: rgba(74, 222, 128, 0.7);
    animation-duration: 3.6s;
}

.cs-avatar {
    position: relative;
    display: flex;
    width: 9rem;
    height: 9rem;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 4px solid rgba(255, 255, 255, 0.9);
    border-radius: 9999px;
    background: linear-gradient(135deg, #FF7010, #FF9248);
    font-size: 3.4rem;
    font-weight: 700;
    box-shadow: 0 0 60px rgba(255, 112, 16, 0.7);
}

.cs.is-connected .cs-avatar {
    box-shadow: 0 0 60px rgba(74, 222, 128, 0.55);
}

.cs-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cs-center h2 {
    margin: 0.4rem 0 0.35rem;
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.cs-status {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    color: #fdd9b5;
}

.cs-dots i {
    display: inline-block;
    font-style: normal;
    animation: call-blink 1.2s infinite;
}

.cs-dots i:nth-child(2) { animation-delay: 0.2s; }
.cs-dots i:nth-child(3) { animation-delay: 0.4s; }

.cs-timer {
    margin: 0;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    animation: call-fade 0.5s ease both;
}

/* equalizer */
.cs-eq {
    display: flex;
    height: 3rem;
    align-items: center;
    gap: 0.22rem;
    margin-top: 1.4rem;
}

.cs-eq i {
    display: block;
    width: 0.28rem;
    height: 12%;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.45);
    transform-origin: center;
    transition: height 0.4s ease, background 0.4s ease;
}

.cs-eq.is-live i {
    background: #86efac;
    animation: call-eq 1.1s ease-in-out infinite;
}

.cs-meter {
    width: 12rem;
    height: 0.3rem;
    margin-top: 1.1rem;
    overflow: hidden;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.18);
}

.cs-meter div {
    height: 100%;
    border-radius: 9999px;
    background: linear-gradient(90deg, #4ade80, #fde68a);
    transition: width 1s linear;
}

.cs-meter.is-low div {
    background: #f87171;
}

/* controls */
.cs-controls {
    position: relative;
    display: flex;
    gap: 2.5rem;
}

.cs-control {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #fdd9b5;
}

.cs-round {
    position: relative;
    display: flex;
    width: 4.2rem;
    height: 4.2rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.16);
    color: #fff;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
}

.cs-round:hover {
    transform: translateY(-3px) scale(1.06);
    background: rgba(255, 255, 255, 0.28);
}

.cs-round.is-on {
    background: #fff;
    color: #dc2626;
}

.cs-end {
    border-color: #ef4444;
    background: #ef4444;
    box-shadow: 0 14px 28px -6px rgba(239, 68, 68, 0.7);
}

.cs-end:hover {
    background: #dc2626;
}

.cs-end-ping {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid #ef4444;
    animation: call-ping 2s ease-out infinite;
    pointer-events: none;
}

.callscreen-enter-active,
.callscreen-leave-active {
    transition: opacity 0.4s ease, transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.callscreen-enter-from,
.callscreen-leave-to {
    opacity: 0;
    transform: scale(1.04);
}

/* ---- keyframes ---- */
@keyframes call-ring {
    0% { transform: scale(0.7); opacity: 0.8; }
    100% { transform: scale(1.35); opacity: 0; }
}

@keyframes call-ping {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.6); opacity: 0; }
}

@keyframes call-breathe {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.04); }
}

@keyframes call-shake {
    0%, 100% { transform: rotate(0); }
    20% { transform: rotate(-18deg); }
    40% { transform: rotate(16deg); }
    60% { transform: rotate(-12deg); }
    80% { transform: rotate(10deg); }
}

@keyframes call-blink {
    0%, 100% { opacity: 0.25; }
    50% { opacity: 1; }
}

@keyframes call-eq {
    0%, 100% { height: 14%; }
    50% { height: var(--h, 70%); }
}

@keyframes call-twinkle {
    0%, 100% { opacity: 0.15; transform: scale(0.8); }
    50% { opacity: 0.9; transform: scale(1.4); }
}

@keyframes call-throb {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.07); }
}

@keyframes call-fade {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 899px) {
    .call-stage { padding: 0; }
    .call-shell { border-radius: 0; border-left: 0; border-right: 0; }
    .call-remaining span { display: none; }
    .call-panel { flex: none; }
}

@media (prefers-reduced-motion: reduce) {
    .call-shell,
    .call-hero-ring,
    .call-hero-avatar,
    .call-btn-ripple,
    .cs-ring,
    .cs-star,
    .cs-eq i,
    .cs-end-ping {
        animation: none !important;
    }
}
</style>