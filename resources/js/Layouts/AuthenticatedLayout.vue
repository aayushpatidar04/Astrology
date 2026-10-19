<script setup>
import { ref, computed, provide, onMounted, onBeforeUnmount } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import Alert from "@/Components/Alert.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";

const alertRef = ref(null);
provide("alertRef", alertRef);

const sidebarOpen = ref(false);

const navItems = [
    {
        label: "Dashboard",
        route: "dashboard",
        icon: "mdi:view-dashboard-outline",
    },
    { label: "Blogs", route: "blogs", icon: "mdi:post-outline" },
    {
        label: "Horoscopes",
        route: "admin.horoscopes",
        icon: "mdi:star-four-points-outline",
    },
    {
        label: "Banners",
        route: "admin.banners",
        icon: "mdi:image-multiple-outline",
    },
    {
        label: "Astrologers",
        route: "admin.astrologers",
        icon: "mdi:account-star-outline",
    },
    { label: "Users", route: "admin.users", icon: "mdi:account-group-outline" },
    {
        label: "Packages",
        route: "admin.recharge-packages",
        icon: "mdi:wallet-giftcard",
    },
    {
        label: "Transactions",
        route: "admin.transactions.index",
        icon: "mdi:swap-horizontal",
    },
];

const page = usePage();
const initial = computed(() =>
    (page.props.auth?.user?.name || "A").charAt(0).toUpperCase(),
);

function onKey(e) {
    if (e.key === "Escape") sidebarOpen.value = false;
}

onMounted(() => document.addEventListener("keydown", onKey));
onBeforeUnmount(() => document.removeEventListener("keydown", onKey));
</script>

<template>
    <Alert ref="alertRef" />

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
                <Link :href="route('dashboard')">
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
                <div class="adm-nav-label">Manage</div>
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="adm-nav-link"
                    :class="{ 'is-active': route().current(item.route) }"
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
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </header>

            <!-- Page Content -->
            <main class="adm-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<!--
    Plain, "adm-" prefixed CSS on purpose: every admin page and modal shares it, and it does
    not depend on Tailwind having scanned the new files.
-->
<style>
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

.adm-table {
    overflow-x: auto;
}

.adm-empty {
    padding: 3rem 1rem;
    text-align: center;
    color: #9ca3af;
}

.adm-cell-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
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

.adm-actions {
    max-width: 15rem;
}

.adm-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
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

.adm-btn-success {
    border-color: #16a34a;
    background: #16a34a;
    box-shadow: 0 6px 14px rgba(22, 163, 74, 0.25);
}

.adm-btn-success:hover:not(:disabled) {
    border-color: #15803d;
    background: #15803d;
    box-shadow: 0 10px 20px rgba(22, 163, 74, 0.35);
}

.adm-btn-square {
    padding: 0;
    width: 2.6rem;
    height: 2.6rem;
    flex-shrink: 0;
    font-size: 1.25rem;
}

.adm-spin {
    animation: adm-spin 1s linear infinite;
}

/* small soft "pill" buttons for table rows */
.adm-pill {
    padding: 0.3rem 0.75rem;
    border: 0;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.adm-pill:hover {
    transform: translateY(-1px);
}

.adm-pill-green {
    background: #dcfce7;
    color: #166534;
}
.adm-pill-green:hover {
    background: #bbf7d0;
}
.adm-pill-blue {
    background: #dbeafe;
    color: #1e40af;
}
.adm-pill-blue:hover {
    background: #bfdbfe;
}
.adm-pill-yellow {
    background: #fef9c3;
    color: #854d0e;
}
.adm-pill-yellow:hover {
    background: #fef08a;
}
.adm-pill-red {
    background: #fee2e2;
    color: #991b1b;
}
.adm-pill-red:hover {
    background: #fecaca;
}
.adm-pill-purple {
    background: #f3e8ff;
    color: #6b21a8;
}
.adm-pill-purple:hover {
    background: #e9d5ff;
}
.adm-pill-orange {
    background: #ffedd5;
    color: #c2410c;
}
.adm-pill-orange:hover {
    background: #fed7aa;
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

.adm-icon-edit {
    background: #fef9c3;
    color: #ca8a04;
}
.adm-icon-edit:hover {
    background: #fde68a;
}
.adm-icon-del {
    background: #fee2e2;
    color: #dc2626;
}
.adm-icon-del:hover {
    background: #fecaca;
}
.adm-icon-add {
    background: #dcfce7;
    color: #16a34a;
}
.adm-icon-add:hover {
    background: #bbf7d0;
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

/* ---------- Forms ---------- */
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

textarea.adm-input {
    min-height: 5rem;
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

.adm-check {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
}

.adm-check input {
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 0.3rem;
    accent-color: #ff7010;
    cursor: pointer;
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

.adm-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.adm-section-label {
    margin: 0 0 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.adm-thumbs {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(6rem, 1fr));
    gap: 0.75rem;
}

.adm-thumb {
    position: relative;
}

.adm-thumb img {
    display: block;
    width: 100%;
    height: 6rem;
    border-radius: 0.65rem;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.adm-thumb button {
    position: absolute;
    top: 0.3rem;
    right: 0.3rem;
    display: flex;
    width: 1.35rem;
    height: 1.35rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9999px;
    background: #dc2626;
    font-size: 0.85rem;
    line-height: 1;
    color: #fff;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.adm-thumb button:hover {
    transform: scale(1.15);
}

.adm-swatches {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}

.adm-swatch {
    width: 1.6rem;
    height: 1.6rem;
    border: 2px solid #fff;
    border-radius: 9999px;
    box-shadow: 0 0 0 1px #e5e7eb;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.adm-swatch:hover {
    transform: scale(1.15);
}

/* ---------- Modals ---------- */
.adm-modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid #ffedd5;
    background: linear-gradient(90deg, #fff7ed, #fff);
}

.adm-modal-head h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.adm-modal-body {
    max-height: 68vh;
    overflow-y: auto;
    padding: 1.5rem;
}

.adm-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #ffedd5;
    background: #fffaf5;
}

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

.adm-confirm-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* Quill editor */
.adm-modal-body .ql-toolbar.ql-snow {
    border-color: #fed7aa;
    border-radius: 0.65rem 0.65rem 0 0;
    background: #fffaf5;
}

.adm-modal-body .ql-container.ql-snow {
    border-color: #fed7aa;
    border-radius: 0 0 0.65rem 0.65rem;
}

/* ---------- Switch (banner toggle) ---------- */
.adm-switch {
    position: relative;
    display: inline-block;
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

/* ---------- Tabs ---------- */
.adm-tabs {
    display: flex;
    gap: 0.25rem;
    width: fit-content;
    max-width: 100%;
    margin-bottom: 1rem;
    padding: 0.3rem;
    overflow-x: auto;
    border: 1px solid #ffe8d4;
    border-radius: 0.9rem;
    background: #fff;
}

.adm-tab {
    padding: 0.45rem 1rem;
    border: 0;
    border-radius: 0.65rem;
    background: transparent;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    color: #6b7280;
    cursor: pointer;
    transition:
        background 0.25s ease,
        color 0.25s ease,
        box-shadow 0.25s ease;
}

.adm-tab:hover {
    background: #fff4ea;
    color: #ff7010;
}

.adm-tab.is-active {
    background: #ff7010;
    color: #fff;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.3);
}

.adm-tab-count {
    margin-left: 0.4rem;
    padding: 0.05rem 0.45rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.07);
    font-size: 0.7rem;
}

.adm-tab.is-active .adm-tab-count {
    background: rgba(255, 255, 255, 0.25);
}

/* ---------- Banner cards ---------- */
.adm-banner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(17rem, 1fr));
    gap: 1.5rem;
}

.adm-banner {
    position: relative;
    overflow: hidden;
    height: 20rem;
    border-radius: 1rem;
    background: #1f2937;
    box-shadow: 0 10px 30px -12px rgba(124, 45, 18, 0.35);
    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease;
}

.adm-banner:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -14px rgba(124, 45, 18, 0.45);
}

.adm-banner-img {
    position: absolute;
    inset: 0;
    background-position: center;
    background-size: cover;
    transition: transform 0.7s ease;
}

.adm-banner:hover .adm-banner-img {
    transform: scale(1.07);
}

.adm-banner-eye {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.adm-banner:hover .adm-banner-eye {
    opacity: 1;
}

.adm-banner-eye button {
    display: flex;
    width: 3.5rem;
    height: 3.5rem;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.2);
    font-size: 1.75rem;
    color: #fff;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition:
        transform 0.25s ease,
        background 0.25s ease;
}

.adm-banner-eye button:hover {
    transform: scale(1.1);
    background: rgba(255, 255, 255, 0.35);
}

.adm-banner-info {
    position: absolute;
    inset: auto 0 0 0;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding: 2.5rem 1rem 1rem;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.78), rgba(0, 0, 0, 0));
}

.adm-banner-info h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
}

.adm-banner-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.adm-banner-info .adm-icon-btn {
    width: 2.1rem;
    height: 2.1rem;
}

/* ---------- Pagination ---------- */
.adm-pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 1.75rem;
}

.adm-pg {
    padding: 0.4rem 0.85rem;
    border: 1px solid #fed7aa;
    border-radius: 0.6rem;
    background: #fff;
    font-size: 0.875rem;
    color: #4b5563;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}

.adm-pg:hover {
    background: #fff4ea;
    color: #ff7010;
    transform: translateY(-1px);
}

.adm-pg.is-active {
    border-color: #ff7010;
    background: #ff7010;
    color: #fff;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.3);
}

.adm-pg.is-disabled {
    border-color: #e5e7eb;
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.adm-pg.is-disabled:hover {
    transform: none;
}

/* ---------- vue-good-table restyle ---------- */
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}

.adm-page .vgt-wrap {
    border: 0;
}

.adm-page .vgt-inner-wrap {
    border-radius: 0;
    box-shadow: none;
}

.adm-page .vgt-global-search {
    padding: 0.9rem 1rem;
    border: 0;
    border-bottom: 1px solid #ffedd5;
    background: #fff;
}

.adm-page .vgt-global-search__input {
    position: relative;
}

.adm-page .vgt-global-search__input .vgt-input,
.adm-page .vgt-global-search__input input {
    border: 1px solid #fed7aa;
    border-radius: 0.65rem;
    background: #fff;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.adm-page .vgt-global-search__input input:focus {
    outline: none;
    border-color: #ff7010;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.adm-page table.vgt-table {
    border: 0;
    font-size: 0.9rem;
    background: #fff;
}

.adm-page table.vgt-table thead th {
    padding: 0.85rem 0.75rem;
    border: 0;
    border-bottom: 1px solid #fed7aa;
    background: #fff7ed;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #9a3412;
    vertical-align: middle;
}

.adm-page table.vgt-table thead th.line-numbers {
    background: #fff7ed;
    color: #9a3412;
}

.adm-page table.vgt-table td {
    padding: 0.85rem 0.75rem;
    border: 0;
    border-bottom: 1px solid #fff0e1;
    vertical-align: middle;
    color: #374151;
    transition: background 0.2s ease;
}

.adm-page table.vgt-table tbody th.line-numbers {
    padding: 0.85rem 0.75rem;
    border: 0;
    border-bottom: 1px solid #fff0e1;
    background: #fffaf5;
    font-size: 0.8rem;
    font-weight: 600;
    color: #9ca3af;
}

.adm-page table.vgt-table tbody tr:hover td {
    background: #fffaf5;
}

.adm-page table.vgt-table.bordered td,
.adm-page table.vgt-table.bordered th {
    border: 0;
    border-bottom: 1px solid #fff0e1;
}

.adm-page table.vgt-table th.sorting-asc .vgt-sort-btn,
.adm-page .vgt-table th.sortable:hover {
    color: #ff7010;
}

.adm-page .vgt-table th.sortable button:before {
    border-top-color: #fdba74;
}

.adm-page .vgt-table th.sortable button:after {
    border-bottom-color: #fdba74;
}

.adm-page .vgt-table th.sorting-asc button:after {
    border-bottom-color: #ff7010;
}

.adm-page .vgt-table th.sorting-desc button:before {
    border-top-color: #ff7010;
}

.adm-page .vgt-wrap__footer {
    padding: 0.8rem 1rem;
    border: 0;
    border-top: 1px solid #ffedd5;
    background: #fff;
    color: #6b7280;
}

.adm-page .vgt-wrap__footer .footer__row-count__select {
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    background-color: #fff;
}

.adm-page .vgt-wrap__footer .footer__navigation__page-btn {
    color: #ff7010;
    transition: background 0.2s ease;
}

.adm-page .vgt-wrap__footer .footer__navigation__page-btn:hover:not(.disabled) {
    border-radius: 0.5rem;
    background: #fff4ea;
    color: #e85f00;
}

.adm-page .vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
    border-right-color: #ff7010;
}

.adm-page
    .vgt-wrap__footer
    .footer__navigation__page-btn
    .chevron.right::after {
    border-left-color: #ff7010;
}

.adm-page
    .vgt-wrap__footer
    .footer__navigation__page-btn.disabled
    .chevron::after {
    opacity: 0.35;
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
