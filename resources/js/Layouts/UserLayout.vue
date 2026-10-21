<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";

// immersive = full-height app-like pages (chat / call): no footer, content fills the screen
defineProps({
    immersive: { type: Boolean, default: false },
});

const page = usePage();
const mobileMenuOpen = ref(false);

const balance = computed(() => page.props.auth?.user?.wallet?.balance ?? null);

const links = computed(() => [
    {
        label: "Home",
        href: "/",
        match: "/",
        exact: true,
        icon: "mdi:home-outline",
    },
    {
        label: "Chat",
        href: route("user.chat-with-astrologers"),
        match: "/user/chat-with-astrologers",
        icon: "mdi:chat-processing-outline",
    },
    {
        label: "Talk",
        href: route("user.talk-to-astrologers"),
        match: "/user/talk-to-astrologers",
        icon: "mdi:phone-in-talk-outline",
    },
    {
        label: "Chat Sessions",
        href: "/user/chat-sessions",
        match: "/user/chat-sessions",
        icon: "mdi:message-text-clock-outline",
    },
    {
        label: "Call History",
        href: "/user/call-history",
        match: "/user/call-history",
        icon: "mdi:phone-log-outline",
    },
    {
        label: "Transactions",
        href: "/user/transactions",
        match: "/user/transactions",
        icon: "mdi:swap-horizontal",
    },
    {
        label: "Profile",
        href: "/user/profile",
        match: "/user/profile",
        icon: "mdi:account-circle-outline",
    },
]);

const isActive = (l) => {
    const path = (page.url || "").split("?")[0];
    return l.exact
        ? path === l.match
        : path === l.match || path.startsWith(l.match + "/");
};
</script>

<template>
    <div class="usr-page" :class="{ 'is-immersive': immersive }">
        <!-- Header -->
        <header class="usr-header">
            <div class="usr-header-inner">
                <Link href="/" class="usr-logo">
                    <img src="/images/logo.png" alt="MyAstroSathi" />
                </Link>

                <!-- Desktop nav -->
                <nav class="usr-nav">
                    <Link
                        v-for="l in links"
                        :key="l.label"
                        :href="l.href"
                        class="usr-nav-link"
                        :class="{ 'is-active': isActive(l) }"
                    >
                        {{ l.label }}
                    </Link>
                </nav>

                <div class="usr-header-end">
                    <Link
                        v-if="balance !== null"
                        :href="route('user.recharge')"
                        class="usr-wallet"
                        title="Recharge wallet"
                    >
                        <Icon
                            icon="mdi:wallet-outline"
                            width="18"
                            height="18"
                        />
                        ₹{{ balance }}
                        <span class="usr-wallet-plus"
                            ><Icon icon="mdi:plus" width="14" height="14"
                        /></span>
                    </Link>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="usr-logout"
                    >
                        <Icon icon="mdi:logout" width="18" height="18" />
                        <span>Logout</span>
                    </Link>

                    <!-- Mobile menu button -->
                    <button
                        type="button"
                        class="usr-burger"
                        aria-label="Menu"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Icon
                            :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'"
                            width="26"
                            height="26"
                        />
                    </button>
                </div>
            </div>

            <!-- Mobile nav -->
            <Transition name="usr-drop">
                <nav v-if="mobileMenuOpen" class="usr-mobile">
                    <Link
                        v-for="l in links"
                        :key="l.label"
                        :href="l.href"
                        class="usr-mobile-link"
                        :class="{ 'is-active': isActive(l) }"
                        @click="mobileMenuOpen = false"
                    >
                        <Icon :icon="l.icon" width="20" height="20" />
                        {{ l.label }}
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="usr-mobile-link is-danger"
                    >
                        <Icon icon="mdi:logout" width="20" height="20" /> Logout
                    </Link>
                </nav>
            </Transition>
        </header>

        <!-- Main content -->
        <main class="usr-main">
            <slot />
        </main>

        <!-- Footer -->
        <footer v-if="!immersive" class="usr-footer">
            © {{ new Date().getFullYear() }} My Astro Sathi. All rights
            reserved.
        </footer>
    </div>
</template>

<!--
    Plain "usr-" prefixed CSS (shared by every user page, chat and call screens too).
    It does not depend on Tailwind having scanned these files.
-->
<style>
/* ---------- Shell ---------- */
.usr-page {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    background: linear-gradient(180deg, #fdf6f0 0, #fffdfb 460px);
    color: #1f2937;
}

.usr-page.is-immersive {
    height: 100vh;
    height: 100dvh;
    min-height: 0;
}

.usr-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: rgba(255, 255, 255, 0.88);
    border-bottom: 1px solid #ffe8d4;
    backdrop-filter: blur(10px);
}

.usr-header-inner {
    display: flex;
    max-width: 82rem;
    height: 4.25rem;
    align-items: center;
    gap: 1rem;
    margin: 0 auto;
    padding: 0 1rem;
}

.usr-logo img {
    display: block;
    height: 2.6rem;
    width: auto;
    transition: transform 0.3s ease;
}

.usr-logo:hover img {
    transform: scale(1.05);
}

.usr-nav {
    display: none;
    flex: 1;
    align-items: center;
    justify-content: center;
    gap: 0.15rem;
}

.usr-nav-link {
    position: relative;
    padding: 0.5rem 0.7rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: color 0.2s ease;
}

.usr-nav-link::after {
    content: "";
    position: absolute;
    left: 0.7rem;
    right: 0.7rem;
    bottom: 0.15rem;
    height: 2px;
    border-radius: 9999px;
    background: #ff7010;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.usr-nav-link:hover,
.usr-nav-link.is-active {
    color: #ff7010;
}

.usr-nav-link:hover::after,
.usr-nav-link.is-active::after {
    transform: scaleX(1);
}

.usr-nav-link.is-active {
    font-weight: 700;
}

.usr-header-end {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-left: auto;
}

.usr-wallet {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.5rem 0.35rem 0.8rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 0.875rem;
    font-weight: 700;
    color: #c2410c;
    text-decoration: none;
    transition:
        background 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.usr-wallet:hover {
    transform: translateY(-1px);
    background: #fed7aa;
    box-shadow: 0 6px 14px rgba(255, 112, 16, 0.25);
}

.usr-wallet-plus {
    display: flex;
    width: 1.3rem;
    height: 1.3rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: #ff7010;
    color: #fff;
}

.usr-logout {
    display: none;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.7rem;
    border: 0;
    border-radius: 0.6rem;
    background: transparent;
    font-size: 0.875rem;
    font-weight: 600;
    color: #ef4444;
    cursor: pointer;
    transition: background 0.2s ease;
}

.usr-logout:hover {
    background: #fee2e2;
}

.usr-burger {
    display: flex;
    padding: 0.35rem;
    border: 0;
    border-radius: 0.6rem;
    background: transparent;
    color: #4b5563;
    cursor: pointer;
}

.usr-burger:hover {
    background: #fff4ea;
    color: #ff7010;
}

.usr-mobile {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.5rem 1rem 1rem;
    border-top: 1px solid #fff0e1;
    background: #fff;
}

.usr-mobile-link {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 0.8rem;
    border: 0;
    border-radius: 0.7rem;
    background: transparent;
    font-size: 0.95rem;
    font-weight: 500;
    color: #4b5563;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.usr-mobile-link:hover,
.usr-mobile-link.is-active {
    background: #fff4ea;
    color: #ff7010;
}

.usr-mobile-link.is-danger {
    color: #ef4444;
}

.usr-mobile-link.is-danger:hover {
    background: #fee2e2;
    color: #dc2626;
}

.usr-main {
    flex: 1;
    min-height: 0;
}

.usr-footer {
    padding: 1.1rem 1rem;
    border-top: 1px solid #ffe8d4;
    background: #fff;
    text-align: center;
    font-size: 0.85rem;
    color: #9ca3af;
}

@media (min-width: 1024px) {
    .usr-nav {
        display: flex;
    }

    .usr-logout {
        display: inline-flex;
    }

    .usr-burger {
        display: none;
    }

    .usr-header-end {
        margin-left: 0;
    }
}

/* ---------- Page pieces ---------- */
.usr-container {
    max-width: 82rem;
    margin: 0 auto;
    padding: 1.5rem 1rem 3rem;
    animation: usr-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.usr-narrow {
    max-width: 52rem;
}

.usr-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.usr-head h1 {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1f2937;
}

.usr-head p {
    margin: 0.25rem 0 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.usr-card {
    overflow: hidden;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 12px 32px -14px rgba(124, 45, 18, 0.14);
}

.usr-pad {
    padding: 1.4rem;
}

.usr-table {
    overflow-x: auto;
}

.usr-empty {
    padding: 3rem 1rem;
    text-align: center;
    color: #9ca3af;
}

.usr-strong {
    display: block;
    font-weight: 600;
    color: #1f2937;
}

.usr-muted {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
}

.usr-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.usr-stat {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.1rem;
    border: 1px solid #ffe8d4;
    border-radius: 1rem;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 12px 32px -14px rgba(124, 45, 18, 0.14);
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.usr-stat:hover {
    transform: translateY(-3px);
    box-shadow:
        0 1px 3px rgba(124, 45, 18, 0.06),
        0 20px 36px -14px rgba(124, 45, 18, 0.25);
}

.usr-stat-icon {
    display: flex;
    width: 2.75rem;
    height: 2.75rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.85rem;
    background: #ffedd5;
    color: #ff7010;
}

.usr-stat-icon.is-blue {
    background: #dbeafe;
    color: #2563eb;
}
.usr-stat-icon.is-green {
    background: #dcfce7;
    color: #16a34a;
}
.usr-stat-icon.is-purple {
    background: #f3e8ff;
    color: #9333ea;
}

.usr-stat strong {
    display: block;
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.15;
    color: #1f2937;
}

.usr-stat span {
    font-size: 0.78rem;
    font-weight: 600;
    color: #9ca3af;
}

/* ---------- Buttons ---------- */
.usr-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    overflow: hidden;
    padding: 0.6rem 1.2rem;
    border: 1px solid #ff7010;
    border-radius: 0.7rem;
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

.usr-btn::before {
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

.usr-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    background: #e85f00;
    border-color: #e85f00;
    box-shadow: 0 10px 20px rgba(255, 112, 16, 0.38);
}

.usr-btn:hover:not(:disabled)::before {
    transform: translateX(100%);
}

.usr-btn:active:not(:disabled) {
    transform: translateY(0);
}

.usr-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 112, 16, 0.3);
}

.usr-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    box-shadow: none;
}

.usr-btn-sm {
    padding: 0.35rem 0.8rem;
    border-radius: 0.55rem;
    font-size: 0.8rem;
    box-shadow: none;
}

.usr-btn-lg {
    padding: 0.8rem 1.6rem;
    font-size: 1rem;
}

.usr-btn-ghost {
    border-color: #e5e7eb;
    background: #fff;
    color: #4b5563;
    box-shadow: none;
}

.usr-btn-ghost::before {
    display: none;
}

.usr-btn-ghost:hover:not(:disabled) {
    border-color: #d1d5db;
    background: #f9fafb;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
}

.usr-btn-danger {
    border-color: #ef4444;
    background: #ef4444;
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.28);
}

.usr-btn-danger:hover:not(:disabled) {
    border-color: #dc2626;
    background: #dc2626;
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.38);
}

.usr-btn-green {
    border-color: #16a34a;
    background: #16a34a;
    box-shadow: 0 6px 14px rgba(22, 163, 74, 0.28);
}

.usr-btn-green:hover:not(:disabled) {
    border-color: #15803d;
    background: #15803d;
    box-shadow: 0 10px 20px rgba(22, 163, 74, 0.38);
}

.usr-spin {
    animation: usr-spin 1s linear infinite;
}

/* ---------- Badges ---------- */
.usr-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.7rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.usr-badge-green {
    background: #dcfce7;
    color: #15803d;
}
.usr-badge-red {
    background: #fee2e2;
    color: #b91c1c;
}
.usr-badge-yellow {
    background: #fef9c3;
    color: #854d0e;
}
.usr-badge-blue {
    background: #dbeafe;
    color: #1d4ed8;
}
.usr-badge-orange {
    background: #ffedd5;
    color: #c2410c;
}
.usr-badge-purple {
    background: #f3e8ff;
    color: #7e22ce;
}
.usr-badge-gray {
    background: #f3f4f6;
    color: #4b5563;
}

.usr-avatar {
    display: flex;
    width: 2.75rem;
    height: 2.75rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 0.8rem;
    background: #ffedd5;
    font-weight: 700;
    color: #c2410c;
    object-fit: cover;
}

.usr-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* ---------- Forms ---------- */
.usr-field {
    margin-bottom: 1rem;
}

.usr-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

.usr-input {
    display: block;
    box-sizing: border-box;
    width: 100%;
    padding: 0.6rem 0.85rem;
    border: 1px solid #fed7aa;
    border-radius: 0.7rem;
    background-color: #fff;
    font-size: 0.95rem;
    color: #1f2937;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.usr-input::placeholder {
    color: #9ca3af;
}

.usr-input:hover {
    border-color: #fb923c;
}

.usr-input:focus {
    outline: none;
    border-color: #ff7010;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.usr-input.is-error {
    border-color: #f87171;
}

.usr-error {
    margin: 0.3rem 0 0;
    font-size: 0.8rem;
    color: #dc2626;
}

.usr-hint {
    margin: 0.3rem 0 0;
    font-size: 0.78rem;
    color: #9ca3af;
}

/* ---------- Modal ---------- */
.usr-overlay {
    position: fixed;
    inset: 0;
    z-index: 60;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
}

.usr-modal {
    width: 100%;
    max-width: 24rem;
    padding: 2rem 1.5rem 1.5rem;
    border-radius: 1.25rem;
    background: #fff;
    text-align: center;
    box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
}

.usr-modal h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    font-weight: 800;
    color: #1f2937;
}

.usr-modal p {
    margin: 0;
    color: #4b5563;
}

.usr-modal-icon {
    display: flex;
    width: 4rem;
    height: 4rem;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    border-radius: 9999px;
    background: #ffedd5;
    font-size: 2rem;
    color: #ff7010;
    animation: usr-pop 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.usr-modal-icon.is-red {
    background: #fee2e2;
    color: #dc2626;
}
.usr-modal-icon.is-green {
    background: #dcfce7;
    color: #16a34a;
}
.usr-modal-icon.is-blue {
    background: #dbeafe;
    color: #2563eb;
}

.usr-modal-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.7rem;
    margin-top: 1.5rem;
}

.usr-modal-stats {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.1rem;
}

.usr-modal-stats div {
    min-width: 6rem;
    padding: 0.6rem 0.9rem;
    border-radius: 0.85rem;
    background: #fff7ed;
}

.usr-modal-stats strong {
    display: block;
    font-size: 1.1rem;
    color: #1f2937;
}

.usr-modal-stats span {
    font-size: 0.72rem;
    font-weight: 600;
    color: #9ca3af;
}

.usr-modal-enter-active,
.usr-modal-leave-active {
    transition: opacity 0.25s ease;
}

.usr-modal-enter-from,
.usr-modal-leave-to {
    opacity: 0;
}

.usr-modal-enter-active .usr-modal,
.usr-modal-leave-active .usr-modal {
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.usr-modal-enter-from .usr-modal,
.usr-modal-leave-to .usr-modal {
    transform: scale(0.9) translateY(16px);
}

.usr-drop-enter-active,
.usr-drop-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.usr-drop-enter-from,
.usr-drop-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* ---------- vue-good-table restyle ---------- */
[id^="vgt-select-rpp-"] {
    background-image: none !important;
}

.usr-page .vgt-wrap {
    border: 0;
}

.usr-page .vgt-inner-wrap {
    border-radius: 0;
    box-shadow: none;
}

.usr-page .vgt-global-search {
    padding: 0.9rem 1rem;
    border: 0;
    border-bottom: 1px solid #ffedd5;
    background: #fff;
}

.usr-page .vgt-global-search__input input {
    border: 1px solid #fed7aa;
    border-radius: 0.7rem;
    background: #fff;
}

.usr-page .vgt-global-search__input input:focus {
    outline: none;
    border-color: #ff7010;
    box-shadow: 0 0 0 3px rgba(255, 112, 16, 0.2);
}

.usr-page table.vgt-table {
    border: 0;
    font-size: 0.9rem;
    background: #fff;
}

.usr-page table.vgt-table thead th {
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

.usr-page table.vgt-table td {
    padding: 0.85rem 0.75rem;
    border: 0;
    border-bottom: 1px solid #fff0e1;
    vertical-align: middle;
    color: #374151;
    transition: background 0.2s ease;
}

.usr-page table.vgt-table tbody th.line-numbers {
    padding: 0.85rem 0.75rem;
    border: 0;
    border-bottom: 1px solid #fff0e1;
    background: #fffaf5;
    font-size: 0.8rem;
    font-weight: 600;
    color: #9ca3af;
}

.usr-page table.vgt-table tbody tr:hover td {
    background: #fffaf5;
}

.usr-page .vgt-table th.sortable button:before {
    border-top-color: #fdba74;
}
.usr-page .vgt-table th.sortable button:after {
    border-bottom-color: #fdba74;
}
.usr-page .vgt-table th.sorting-asc button:after {
    border-bottom-color: #ff7010;
}
.usr-page .vgt-table th.sorting-desc button:before {
    border-top-color: #ff7010;
}

.usr-page .vgt-wrap__footer {
    padding: 0.8rem 1rem;
    border: 0;
    border-top: 1px solid #ffedd5;
    background: #fff;
    color: #6b7280;
}

.usr-page .vgt-wrap__footer .footer__row-count__select {
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    background-color: #fff;
}

.usr-page .vgt-wrap__footer .footer__navigation__page-btn {
    color: #ff7010;
}

.usr-page .vgt-wrap__footer .footer__navigation__page-btn:hover:not(.disabled) {
    border-radius: 0.5rem;
    background: #fff4ea;
    color: #e85f00;
}

.usr-page .vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
    border-right-color: #ff7010;
}
.usr-page
    .vgt-wrap__footer
    .footer__navigation__page-btn
    .chevron.right::after {
    border-left-color: #ff7010;
}
.usr-page
    .vgt-wrap__footer
    .footer__navigation__page-btn.disabled
    .chevron::after {
    opacity: 0.35;
}

/* ---------- Keyframes ---------- */
@keyframes usr-rise {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes usr-pop {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes usr-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .usr-page *,
    .usr-page *::before,
    .usr-page *::after {
        animation: none !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
