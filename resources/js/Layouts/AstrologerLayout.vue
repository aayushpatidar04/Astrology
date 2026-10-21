<script setup>
import { ref, computed, provide, onMounted, onBeforeUnmount } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import Alert from "@/Components/Alert.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import axios from "axios";
import echo from "@/echo";

// `flush` removes the content padding so full-height screens (Chats / Calls) can fill the area
defineProps({ flush: { type: Boolean, default: false } });

const alertRef = ref(null);
provide("alertRef", alertRef);

const sidebarOpen = ref(false);

const navItems = [
    {
        label: "Dashboard",
        href: "/astrologer/dashboard",
        name: "astrologer.dashboard",
        icon: "mdi:view-dashboard-outline",
    },
    {
        label: "Chats",
        href: "/astrologer/chats",
        name: "astrologer.chats",
        icon: "mdi:chat-processing-outline",
    },
    {
        label: "Calls",
        href: "/astrologer/calls",
        name: "astrologer.calls",
        icon: "mdi:phone-outline",
    },
    {
        label: "Profile",
        href: "/astrologer/profile",
        name: "astrologer.profile.edit",
        icon: "mdi:account-circle-outline",
    },
];

const page = usePage();
const initial = computed(() =>
    (page.props.auth?.user?.name || "A").charAt(0).toUpperCase(),
);
const incomingRequests = ref([]);
const currentRequest = computed(() => incomingRequests.value[0] ?? null);
const astrologerUserId = page.props.auth?.user?.id;

const dismissIncomingRequest = () => {
    incomingRequests.value.shift();
};

const openIncomingRequest = () => {
    dismissIncomingRequest();
};

// ---------- online / offline status ----------
const online = ref(!!page.props.auth.user.astrologer?.online);
const toggling = ref(false);

const toggleStatus = async () => {
    if (toggling.value) return;
    toggling.value = true;
    online.value = !online.value;
    try {
        await axios.post("/astrologer/status", {
            online: online.value ? 1 : 0,
        });
        if (online.value) {
            alertRef.value?.showAlert(
                "You are now online and available for chats!",
                "success",
            );
        } else {
            alertRef.value?.showAlert(
                "You are now offline. Users cannot reach you.",
                "success",
            );
        }
    } catch (error) {
        online.value = !online.value;
        alertRef.value?.showAlert(
            "Something went wrong, please try after some time!",
            "error",
        );
    } finally {
        toggling.value = false;
    }
};

// pages (e.g. Dashboard) can read / toggle the same state
provide("astroStatus", { online, toggling, toggleStatus });

function onKey(e) {
    if (e.key === "Escape") sidebarOpen.value = false;
}

onMounted(() => {
    document.addEventListener("keydown", onKey);

    if (astrologerUserId) {
        echo.private(`astrologer-inbox.${astrologerUserId}`)
            .listen("IncomingAstrologerRequest", (request) => {
                const duplicate = incomingRequests.value.some(
                    (item) => item.requestType === request.requestType && item.chatId === request.chatId,
                );
                if (!duplicate) incomingRequests.value.push(request);
            });
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", onKey);
    if (astrologerUserId) {
        echo.leave(`astrologer-inbox.${astrologerUserId}`);
    }
});
</script>

<template>
    <Alert ref="alertRef" />

    <Transition name="adm-fade">
        <div v-if="currentRequest" class="ast-incoming-request" role="status">
            <Icon
                :icon="currentRequest.requestType === 'call' ? 'mdi:phone-incoming' : 'mdi:chat-processing-outline'"
                width="22"
                height="22"
            />
            <div class="ast-incoming-request-copy">
                <strong>{{ currentRequest.requestType === 'call' ? 'Incoming call' : 'New chat request' }}</strong>
                <span>{{ currentRequest.userName }} is {{ currentRequest.requestType === 'call' ? 'calling' : 'waiting to chat' }}.</span>
            </div>
            <Link
                :href="currentRequest.requestType === 'call'
                    ? route('astrologer.calls', currentRequest.userId)
                    : route('astrologer.chats', currentRequest.chatId)"
                class="ast-incoming-request-action"
                @click="openIncomingRequest"
            >
                {{ currentRequest.requestType === 'call' ? 'Answer' : 'Open chat' }}
            </Link>
            <button type="button" class="ast-incoming-request-close" aria-label="Dismiss notification"
                @click="dismissIncomingRequest">
                <Icon icon="mdi:close" width="18" height="18" />
            </button>
        </div>
    </Transition>

    <div class="adm-page">
        <!-- Mobile overlay -->
        <Transition name="adm-fade">
            <div
                v-if="sidebarOpen"
                class="adm-overlay"
                @click="sidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside class="adm-sidebar" :class="{ 'is-open': sidebarOpen }">
            <div class="adm-brand">
                <Link href="/astrologer/dashboard">
                    <img src="/images/logo.png" alt="MyAstroSathi" />
                </Link>
                <button
                    type="button"
                    class="adm-icon-btn adm-menu-close"
                    aria-label="Close menu"
                    @click="sidebarOpen = false"
                >
                    <Icon icon="mdi:close" />
                </button>
            </div>

            <nav class="adm-nav">
                <div class="adm-nav-label">Astrologer</div>
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.href"
                    class="adm-nav-link"
                    :class="{ 'is-active': route().current(item.name) }"
                    @click="sidebarOpen = false"
                >
                    <Icon :icon="item.icon" width="20" height="20" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="adm-side-foot">
                <a href="/" target="_blank" class="adm-nav-link">
                    <Icon icon="mdi:open-in-new" width="20" height="20" />
                    <span>View website</span>
                </a>
            </div>
        </aside>

        <div class="adm-main">
            <!-- Top bar -->
            <header class="adm-topbar">
                <button
                    type="button"
                    class="adm-icon-btn adm-menu-btn"
                    aria-label="Open menu"
                    @click="sidebarOpen = true"
                >
                    <Icon icon="mdi:menu" />
                </button>

                <div class="adm-topbar-title">
                    <slot name="header" />
                </div>

                <!-- Online / offline -->
                <label
                    class="ast-status"
                    :class="{ 'is-on': online }"
                    :title="
                        online
                            ? 'You are visible to users'
                            : 'You are hidden from users'
                    "
                >
                    <span class="adm-switch">
                        <input
                            type="checkbox"
                            :checked="online"
                            :disabled="toggling"
                            aria-label="Toggle online status"
                            @change="toggleStatus"
                        />
                        <span class="adm-switch-track"></span>
                    </span>
                    <span class="ast-status-text">{{
                        online ? "Online" : "Offline"
                    }}</span>
                </label>

                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button type="button" class="adm-user-btn">
                            <span class="adm-user-avatar">{{ initial }}</span>
                            <span class="adm-user-name">{{
                                $page.props.auth.user.name
                            }}</span>
                            <Icon
                                icon="mdi:chevron-down"
                                width="18"
                                height="18"
                            />
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink href="/astrologer/profile">
                            Profile
                        </DropdownLink>
                        <DropdownLink href="/logout" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </header>

            <!-- Page Content -->
            <main class="adm-content" :class="{ 'is-flush': flush }">
                <slot />
            </main>
        </div>
    </div>
</template>

<!--
    Same "adm-" design tokens as the admin layout (subset needed on the astrologer side),
    plus a few "ast-" classes for the astrologer-only screens.
    Tip: if you later move the admin CSS into a shared file (e.g. resources/css/adm.css),
    import that one file in both layouts and delete this block.
-->
<style>
.ast-incoming-request {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 100;
    display: flex;
    max-width: min(30rem, calc(100vw - 2rem));
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1rem;
    border: 1px solid #fed7aa;
    border-radius: 0.9rem;
    background: #fff;
    color: #9a3412;
    box-shadow: 0 12px 30px rgba(124, 45, 18, 0.18);
}

.ast-incoming-request-copy {
    display: grid;
    flex: 1;
    gap: 0.15rem;
    color: #374151;
    font-size: 0.85rem;
}

.ast-incoming-request-copy strong {
    color: #9a3412;
    font-size: 0.95rem;
}

.ast-incoming-request-action {
    flex-shrink: 0;
    padding: 0.55rem 0.75rem;
    border-radius: 0.55rem;
    background: #ff7010;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
}

.ast-incoming-request-action:hover {
    background: #ea580c;
}

.ast-incoming-request-close {
    display: inline-flex;
    flex-shrink: 0;
    padding: 0.25rem;
    border: 0;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
}

.ast-incoming-request-close:hover {
    color: #4b5563;
}

/* ---------- Shell ---------- */
.adm-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #fdf6f0 0, #fffdfb 420px);
    color: #1f2937;
}

.adm-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 50;
    display: flex;
    width: 16rem;
    flex-direction: column;
    background: #fff;
    border-right: 1px solid #ffe8d4;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.adm-sidebar.is-open {
    transform: none;
    box-shadow: 0 25px 50px -12px rgba(124, 45, 18, 0.35);
}

.adm-overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
    background: rgba(17, 24, 39, 0.45);
    backdrop-filter: blur(2px);
}

.adm-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #fff0e1;
}

.adm-brand img {
    height: 2.75rem;
    width: auto;
    transition: transform 0.3s ease;
}

.adm-brand a:hover img {
    transform: scale(1.05);
}

.adm-nav {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 0.25rem;
    overflow-y: auto;
    padding: 1rem 0.75rem;
}

.adm-nav-label {
    padding: 0.4rem 0.85rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
}

.adm-nav-link {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    border-radius: 0.7rem;
    font-size: 0.925rem;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.adm-nav-link svg {
    flex-shrink: 0;
    transition: transform 0.25s ease;
}

.adm-nav-link:hover {
    background: #fff4ea;
    color: #ff7010;
    transform: translateX(3px);
}

.adm-nav-link:hover svg {
    transform: scale(1.12);
}

.adm-nav-link.is-active {
    background: linear-gradient(135deg, #ff7010, #ff9248);
    color: #fff;
    box-shadow: 0 8px 18px rgba(255, 112, 16, 0.3);
}

.adm-nav-link.is-active:hover {
    transform: none;
}

.adm-side-foot {
    padding: 0.75rem;
    border-top: 1px solid #fff0e1;
}

.adm-main {
    min-height: 100vh;
}

.adm-topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    display: flex;
    height: 4rem;
    align-items: center;
    gap: 0.75rem;
    padding: 0 1rem;
    background: rgba(255, 255, 255, 0.85);
    border-bottom: 1px solid #ffe8d4;
    backdrop-filter: blur(10px);
}

.adm-topbar-title {
    flex: 1;
    min-width: 0;
}

.adm-title {
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #1f2937;
}

.adm-user-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.6rem 0.3rem 0.3rem;
    border: 1px solid #ffe8d4;
    border-radius: 9999px;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.adm-user-btn:hover {
    border-color: #fdba74;
    box-shadow: 0 4px 12px rgba(255, 112, 16, 0.15);
}

.adm-user-avatar {
    display: flex;
    width: 1.9rem;
    height: 1.9rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: linear-gradient(135deg, #ff7010, #ff9248);
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
}

.adm-user-name {
    display: none;
}

.adm-content {
    max-width: 96rem;
    margin: 0 auto;
    padding: 1.25rem 1rem 3rem;
    animation: adm-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
}

/* full-height screens (chats / calls) */
.adm-content.is-flush {
    max-width: none;
    margin: 0;
    padding: 0;
    animation: none;
}

.adm-menu-close {
    display: inline-flex;
}

@media (min-width: 640px) {
    .adm-user-name {
        display: inline;
    }
}

@media (min-width: 1024px) {
    .adm-sidebar {
        transform: none;
    }

    .adm-sidebar.is-open {
        box-shadow: none;
    }

    .adm-main {
        margin-left: 16rem;
    }

    .adm-overlay,
    .adm-topbar .adm-menu-btn,
    .adm-brand .adm-menu-close {
        display: none;
    }

    .adm-topbar {
        padding: 0 2rem;
    }

    .adm-content {
        padding: 2rem 2rem 4rem;
    }

    .adm-content.is-flush {
        padding: 0;
    }
}

/* ---------- Page pieces ---------- */
.adm-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.adm-sub {
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.adm-card {
    overflow: hidden;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 12px 32px -14px rgba(124, 45, 18, 0.14);
}

.adm-empty {
    padding: 3rem 1rem;
    text-align: center;
    color: #9ca3af;
}

.adm-avatar {
    display: flex;
    width: 3rem;
    height: 3rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.8rem;
    background: #ffedd5;
    font-weight: 700;
    color: #c2410c;
    object-fit: cover;
}

.adm-strong {
    display: block;
    font-weight: 600;
    color: #1f2937;
}

.adm-muted {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
}

.adm-lc {
    text-transform: lowercase;
}

.adm-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-weight: 500;
    color: #ff7010;
    text-decoration: none;
    transition: color 0.2s ease;
}

.adm-link:hover {
    color: #e85f00;
    text-decoration: underline;
}

/* ---------- Buttons ---------- */
.adm-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    overflow: hidden;
    padding: 0.55rem 1.1rem;
    border: 1px solid #ff7010;
    border-radius: 0.65rem;
    background: #ff7010;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.28);
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

.adm-btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        120deg,
        transparent 30%,
        rgba(255, 255, 255, 0.35) 50%,
        transparent 70%
    );
    transform: translateX(-100%);
    transition: transform 0.7s ease;
}

.adm-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    background: #e85f00;
    border-color: #e85f00;
    box-shadow: 0 10px 20px rgba(255, 112, 16, 0.38);
}

.adm-btn:hover:not(:disabled)::before {
    transform: translateX(100%);
}

.adm-btn:active:not(:disabled) {
    transform: translateY(0);
}

.adm-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 112, 16, 0.3);
}

.adm-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.adm-btn-sm {
    padding: 0.3rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    box-shadow: none;
}

.adm-btn-ghost {
    border-color: #e5e7eb;
    background: #fff;
    color: #4b5563;
    box-shadow: none;
}

.adm-btn-ghost::before {
    display: none;
}

.adm-btn-ghost:hover:not(:disabled) {
    border-color: #d1d5db;
    background: #f9fafb;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
}

.adm-icon-btn {
    display: inline-flex;
    width: 2rem;
    height: 2rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 0.55rem;
    background: transparent;
    font-size: 1.15rem;
    color: #6b7280;
    text-decoration: none;
    cursor: pointer;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}

.adm-icon-btn:hover {
    transform: translateY(-1px);
}

/* ---------- Badges / chips ---------- */
.adm-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.7rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.adm-dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 9999px;
    background: currentColor;
}

.adm-badge-green {
    background: #dcfce7;
    color: #15803d;
}
.adm-badge-red {
    background: #fee2e2;
    color: #b91c1c;
}
.adm-badge-yellow {
    background: #fef9c3;
    color: #854d0e;
}
.adm-badge-blue {
    background: #dbeafe;
    color: #1d4ed8;
}
.adm-badge-orange {
    background: #ffedd5;
    color: #c2410c;
}
.adm-badge-gray {
    background: #f3f4f6;
    color: #4b5563;
}

.adm-chip {
    display: inline-block;
    padding: 0.15rem 0.6rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 0.75rem;
    color: #c2410c;
}

/* ---------- Switch ---------- */
.adm-switch {
    position: relative;
    display: inline-block;
    flex-shrink: 0;
    width: 2.75rem;
    height: 1.5rem;
    cursor: pointer;
}

.adm-switch input {
    position: absolute;
    inset: 0;
    z-index: 1;
    width: 100%;
    height: 100%;
    margin: 0;
    opacity: 0;
    cursor: pointer;
}

.adm-switch-track {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: #d1d5db;
    transition:
        background 0.25s ease,
        box-shadow 0.25s ease;
}

.adm-switch-track::after {
    content: "";
    position: absolute;
    top: 0.2rem;
    left: 0.2rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 9999px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}

.adm-switch input:checked + .adm-switch-track {
    background: #22c55e;
}

.adm-switch input:checked + .adm-switch-track::after {
    transform: translateX(1.25rem);
}

.adm-switch input:focus-visible + .adm-switch-track {
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.35);
}

.adm-switch input:disabled {
    cursor: wait;
}

/* ---------- Astrologer-only pieces ---------- */
.ast-status {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.3rem 0.8rem 0.3rem 0.4rem;
    border: 1px solid #e5e7eb;
    border-radius: 9999px;
    background: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        color 0.2s ease,
        background 0.2s ease;
}

.ast-status.is-on {
    border-color: #bbf7d0;
    background: #f0fdf4;
    color: #15803d;
}

.ast-status-text {
    display: none;
}

@media (min-width: 480px) {
    .ast-status-text {
        display: inline;
    }
}

/* two-pane screens: chats & calls */
.ast-split {
    display: flex;
    height: calc(100vh - 4rem);
    background: #fff;
}

.ast-list {
    display: flex;
    width: 100%;
    flex-direction: column;
    overflow-y: auto;
    border-right: 1px solid #ffe8d4;
    background: #fff;
}

.ast-list.has-active {
    display: none;
}

.ast-list-head {
    position: sticky;
    top: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.1rem;
    border-bottom: 1px solid #ffedd5;
    background: linear-gradient(90deg, #fff7ed, #fff);
}

.ast-list-head h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
}

.ast-item {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.1rem;
    border: 0;
    border-bottom: 1px solid #fff0e1;
    background: #fff;
    text-align: left;
    cursor: pointer;
    transition: background 0.2s ease;
}

.ast-item:hover {
    background: #fffaf5;
}

.ast-item.is-active {
    background: #fff4ea;
    box-shadow: inset 3px 0 0 #ff7010;
}

.ast-av {
    width: 2.6rem;
    height: 2.6rem;
}

.ast-item-main {
    min-width: 0;
    flex: 1;
}

.ast-trunc {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ast-pane {
    display: none;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    background: #fffdfb;
}

.ast-pane.has-active {
    display: flex;
}

.ast-pane-body {
    min-height: 0;
    flex: 1;
}

.ast-pane-empty {
    display: flex;
    height: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #9ca3af;
}

.ast-back {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid #ffedd5;
    background: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    color: #ff7010;
    text-decoration: none;
}

@media (min-width: 768px) {
    .ast-list,
    .ast-list.has-active {
        display: flex;
        width: 20rem;
        flex-shrink: 0;
    }

    .ast-pane {
        display: flex;
    }

    .ast-back {
        display: none;
    }
}

/* profile screen */
.ast-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    align-items: start;
}

.ast-stack {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ast-pad {
    padding: 1.25rem 1.4rem;
}

@media (min-width: 1024px) {
    .ast-grid {
        grid-template-columns: 1fr 1fr;
    }

    .ast-pad {
        padding: 1.75rem 2rem;
    }
}

/* ---------- Forms ---------- */
.ast-form-head {
    margin-bottom: 1.25rem;
}

.ast-form-head h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.ast-form-head p {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: #6b7280;
}

.ast-form-foot {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.25rem;
}

.ast-saved {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #16a34a;
}

.adm-field {
    margin-bottom: 1rem;
}

.adm-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.adm-input {
    display: block;
    box-sizing: border-box;
    width: 100%;
    padding: 0.55rem 0.8rem;
    border: 1px solid #fed7aa;
    border-radius: 0.65rem;
    background-color: #fff;
    font-size: 0.925rem;
    color: #1f2937;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.adm-input::placeholder {
    color: #9ca3af;
}

.adm-input:hover {
    border-color: #fb923c;
}

.adm-input:focus {
    outline: none;
    border-color: #ff7010;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.adm-input[readonly] {
    background-color: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

textarea.adm-input {
    min-height: 6rem;
    resize: vertical;
}

.adm-error {
    margin: 0.3rem 0 0;
    font-size: 0.8rem;
    color: #dc2626;
}

.adm-hint {
    margin: 0.3rem 0 0;
    font-size: 0.75rem;
    color: #9ca3af;
}

.adm-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    column-gap: 1.25rem;
}

@media (min-width: 640px) {
    .adm-grid-2 {
        grid-template-columns: 1fr 1fr;
    }
}

.adm-file {
    display: block;
    width: 100%;
    font-size: 0.85rem;
    color: #6b7280;
}

.adm-file::file-selector-button {
    margin-right: 0.9rem;
    padding: 0.5rem 1rem;
    border: 0;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 0.85rem;
    font-weight: 600;
    color: #c2410c;
    cursor: pointer;
    transition: background 0.2s ease;
}

.adm-file::file-selector-button:hover {
    background: #fed7aa;
}

.adm-section-label {
    margin: 0 0 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.ast-photo-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
}

.ast-photo {
    width: 5.5rem;
    height: 5.5rem;
    flex-shrink: 0;
    border-radius: 1rem;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.ast-photo-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffedd5;
    font-size: 2rem;
    color: #c2410c;
}

.ast-docs {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.ast-doc {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem;
    border: 1px solid #ffe8d4;
    border-radius: 0.8rem;
    background: #fffaf5;
}

.ast-doc .adm-input {
    flex: 1 1 10rem;
}

.ast-doc-file {
    flex: 2 1 14rem;
    min-width: 0;
}

.adm-btn-danger {
    border-color: #ef4444;
    background: #ef4444;
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.28);
}

.adm-btn-danger:hover:not(:disabled) {
    border-color: #dc2626;
    background: #dc2626;
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.38);
}

.adm-icon-del {
    background: #fee2e2;
    color: #dc2626;
}

.adm-icon-del:hover {
    background: #fecaca;
}

.adm-link-btn {
    padding: 0;
    border: 0;
    background: none;
    font: inherit;
    font-weight: 500;
    color: #ff7010;
    cursor: pointer;
    text-decoration: underline;
}

/* ---------- Modals ---------- */
.adm-confirm {
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}

.adm-confirm-icon {
    display: flex;
    width: 3.5rem;
    height: 3.5rem;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    border-radius: 9999px;
    background: #fee2e2;
    font-size: 1.75rem;
    color: #dc2626;
    animation: adm-pop 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.adm-confirm h3 {
    margin: 0 0 0.5rem;
    font-size: 1.2rem;
    font-weight: 700;
    color: #1f2937;
}

.adm-confirm p {
    margin: 0;
    color: #4b5563;
}

.adm-confirm-field {
    margin-top: 1.25rem;
    text-align: left;
}

.adm-confirm-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.adm-spin {
    animation: adm-spin 1s linear infinite;
}

.adm-err-enter-active,
.adm-err-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.adm-err-enter-from,
.adm-err-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

@keyframes adm-pop {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* ---------- Transitions & keyframes ---------- */
.adm-fade-enter-active,
.adm-fade-leave-active {
    transition: opacity 0.3s ease;
}

.adm-fade-enter-from,
.adm-fade-leave-to {
    opacity: 0;
}

@keyframes adm-rise {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes adm-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .adm-page *,
    .adm-page *::before,
    .adm-page *::after {
        animation: none !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
