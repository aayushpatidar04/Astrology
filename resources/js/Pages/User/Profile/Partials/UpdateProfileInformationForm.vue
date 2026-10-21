<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="pf-head">
            <span class="pf-head-icon"><Icon icon="mdi:account-edit-outline" width="24" height="24" /></span>
            <div>
                <h2>Profile Information</h2>
                <p>Update your account's profile information and email address.</p>
            </div>
        </header>

        <form @submit.prevent="form.patch(route('user.profile.update'))" class="pf-fields">
            <div>
                <label for="name" class="usr-label">Name</label>
                <input id="name" type="text" class="usr-input" :class="{ 'is-error': form.errors.name }"
                    v-model="form.name" required autofocus autocomplete="name" />
                <p v-if="form.errors.name" class="usr-error">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="email" class="usr-label">Email</label>
                <div class="pf-input-wrap">
                    <input id="email" type="email" class="usr-input" :class="{ 'is-error': form.errors.email }"
                        v-model="form.email" required readonly autocomplete="username" />
                    <span class="pf-input-icon" title="Email can't be changed">
                        <Icon icon="mdi:lock-outline" width="18" height="18" />
                    </span>
                </div>
                <p v-if="form.errors.email" class="usr-error">{{ form.errors.email }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="pf-notice">
                Your email address is unverified.
                <Link :href="route('verification.send')" method="post" as="button">
                    Click here to re-send the verification email.
                </Link>

                <div v-show="status === 'verification-link-sent'" class="is-ok">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="pf-actions">
                <button type="submit" class="usr-btn" :disabled="form.processing">
                    <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="usr-spin" />
                    Save
                </button>

                <Transition name="pf-saved">
                    <p v-if="form.recentlySuccessful" class="pf-saved">
                        <Icon icon="mdi:check-circle" width="18" height="18" /> Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>