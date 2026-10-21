<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user)
const initial = computed(() => (user.value?.name || 'U').charAt(0).toUpperCase())
const balance = computed(() => user.value?.wallet?.balance ?? null)
</script>

<template>
    <Head title="Profile" />

    <UserLayout>
        <div class="usr-container">
            <div class="usr-head">
                <div>
                    <h1>My Profile</h1>
                    <p>Manage your account details, password and security.</p>
                </div>
            </div>

            <div class="pf-grid">
                <!-- Summary -->
                <aside class="pf-summary pf-rise">
                    <span class="pf-ring"></span>
                    <div class="pf-avatar">{{ initial }}</div>
                    <h2>{{ user?.name }}</h2>
                    <p>{{ user?.email }}</p>

                    <div v-if="balance !== null" class="pf-balance">
                        <span><Icon icon="mdi:wallet-outline" width="16" height="16" /> Wallet balance</span>
                        <b>₹{{ balance }}</b>
                    </div>

                    <div class="pf-links">
                        <Link :href="route('user.recharge')" class="usr-btn usr-btn-sm">
                            <Icon icon="mdi:plus" width="16" height="16" /> Add money
                        </Link>
                        <Link href="/user/transactions" class="usr-btn usr-btn-ghost usr-btn-sm">
                            <Icon icon="mdi:receipt-text-outline" width="16" height="16" /> Transactions
                        </Link>
                    </div>
                </aside>

                <!-- Forms -->
                <div class="pf-forms">
                    <div class="pf-card pf-rise" style="--d: 0.08s">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </div>

                    <div class="pf-card pf-rise" style="--d: 0.16s">
                        <UpdatePasswordForm />
                    </div>

                    <div class="pf-card is-danger pf-rise" style="--d: 0.24s">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<!-- Plain "pf-" prefixed CSS, shared with the three partial forms -->
<style>
.pf-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 960px) {
    .pf-grid {
        grid-template-columns: 19rem 1fr;
        align-items: start;
    }
}

.pf-rise {
    animation: usr-rise 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: var(--d, 0s);
}

.pf-summary {
    position: relative;
    overflow: hidden;
    padding: 2rem 1.5rem 1.5rem;
    border-radius: 1.25rem;
    background: linear-gradient(160deg, #FF7010 0%, #FF8A3D 60%, #FFB066 100%);
    text-align: center;
    color: #fff;
    box-shadow: 0 24px 48px -20px rgba(255, 112, 16, 0.6);
}

@media (min-width: 960px) {
    .pf-summary {
        position: sticky;
        top: 5.5rem;
    }
}

.pf-ring {
    position: absolute;
    top: -5rem;
    right: -4rem;
    width: 14rem;
    height: 14rem;
    border: 2px dashed rgba(255, 255, 255, 0.35);
    border-radius: 9999px;
    animation: usr-spin 60s linear infinite;
    pointer-events: none;
}

.pf-avatar {
    position: relative;
    display: flex;
    width: 5.5rem;
    height: 5.5rem;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    border: 4px solid rgba(255, 255, 255, 0.85);
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.2);
    font-size: 2.2rem;
    font-weight: 800;
    backdrop-filter: blur(6px);
}

.pf-summary h2 {
    position: relative;
    margin: 0;
    font-size: 1.3rem;
    font-weight: 800;
}

.pf-summary p {
    position: relative;
    margin: 0.2rem 0 0;
    overflow-wrap: anywhere;
    font-size: 0.85rem;
    opacity: 0.92;
}

.pf-balance {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 1.25rem 0;
    padding: 0.7rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.18);
}

.pf-balance span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.pf-balance b {
    font-size: 1.6rem;
    font-weight: 800;
}

.pf-links {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
}

.pf-links .usr-btn {
    border-color: rgba(255, 255, 255, 0.55);
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    box-shadow: none;
}

.pf-links .usr-btn:hover {
    background: #fff;
    border-color: #fff;
    color: #E85F00;
}

.pf-forms {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pf-card {
    padding: 1.6rem;
    border: 1px solid #ffe8d4;
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(124, 45, 18, 0.06), 0 12px 32px -14px rgba(124, 45, 18, 0.14);
}

.pf-card.is-danger {
    border-color: #fecaca;
    background: linear-gradient(180deg, #fff, #fff7f7);
}

.pf-head {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
    margin-bottom: 1.4rem;
}

.pf-head-icon {
    display: flex;
    width: 2.8rem;
    height: 2.8rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0.9rem;
    background: #ffedd5;
    color: #FF7010;
}

.pf-card.is-danger .pf-head-icon {
    background: #fee2e2;
    color: #dc2626;
}

.pf-head h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
}

.pf-head p {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    line-height: 1.5;
    color: #6b7280;
}

.pf-fields {
    display: flex;
    max-width: 30rem;
    flex-direction: column;
    gap: 1.1rem;
}

.pf-input-wrap {
    position: relative;
}

.pf-input-wrap .usr-input[readonly] {
    padding-right: 2.5rem;
    border-color: #e5e7eb;
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

.pf-input-icon {
    position: absolute;
    right: 0.8rem;
    top: 50%;
    display: flex;
    color: #9ca3af;
    transform: translateY(-50%);
}

.pf-eye {
    position: absolute;
    right: 0.7rem;
    top: 50%;
    display: flex;
    padding: 0;
    border: 0;
    background: none;
    color: #9ca3af;
    cursor: pointer;
    transform: translateY(-50%);
    transition: color 0.2s ease;
}

.pf-eye:hover {
    color: #FF7010;
}

.pf-has-eye {
    padding-right: 2.6rem;
}

.pf-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 0.4rem;
}

.pf-saved {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #16a34a;
}

.pf-saved-enter-active,
.pf-saved-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.pf-saved-enter-from {
    opacity: 0;
    transform: translateX(-8px);
}

.pf-saved-leave-to {
    opacity: 0;
}

.pf-notice {
    padding: 0.7rem 0.9rem;
    border: 1px solid #fde68a;
    border-radius: 0.8rem;
    background: #fffbeb;
    font-size: 0.85rem;
    color: #92400e;
}

.pf-notice button {
    padding: 0;
    border: 0;
    background: none;
    font-size: inherit;
    font-weight: 600;
    color: #c2410c;
    text-decoration: underline;
    cursor: pointer;
}

.pf-notice .is-ok {
    margin-top: 0.4rem;
    font-weight: 600;
    color: #15803d;
}

.pf-meter {
    display: flex;
    gap: 0.3rem;
    margin-top: 0.5rem;
}

.pf-meter i {
    height: 0.3rem;
    flex: 1;
    border-radius: 9999px;
    background: #ffe8d4;
    transition: background 0.3s ease;
}

.pf-meter-label {
    margin-top: 0.3rem;
    font-size: 0.75rem;
    font-weight: 600;
}

@media (prefers-reduced-motion: reduce) {
    .pf-rise,
    .pf-ring {
        animation: none !important;
    }
}
</style>