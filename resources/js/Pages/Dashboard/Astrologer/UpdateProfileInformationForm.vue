<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { inject, watch } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const alertRef = inject('alertRef')
const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone,
});

// Watch for recentlySuccessful changes
watch(() => form.recentlySuccessful, (newVal) => {
    if (newVal) {
        alertRef.value?.showAlert('Account Info updated successfully!', 'success')
    }
})
</script>

<template>
    <section>
        <header class="ast-form-head">
            <h2>Account Information</h2>
            <p>Update your account's information.</p>
        </header>

        <form @submit.prevent="form.patch(route('astrologer.profile.update'))">
            <div class="adm-field">
                <label for="name" class="adm-label">Name</label>
                <input id="name" type="text" class="adm-input" v-model="form.name" required autofocus
                    autocomplete="name" />
                <Transition name="adm-err">
                    <p v-if="form.errors.name" class="adm-error">{{ form.errors.name }}</p>
                </Transition>
            </div>

            <div class="adm-field">
                <label for="email" class="adm-label">Email</label>
                <input id="email" type="email" class="adm-input" v-model="form.email" required readonly
                    autocomplete="username" />
                <Transition name="adm-err">
                    <p v-if="form.errors.email" class="adm-error">{{ form.errors.email }}</p>
                </Transition>
            </div>

            <div class="adm-field">
                <label for="phone" class="adm-label">Phone</label>
                <input id="phone" type="text" inputmode="numeric" pattern="^[6-9][0-9]{9}$" minlength="10"
                    maxlength="10" class="adm-input" v-model="form.phone" required autocomplete="tel" />
                <Transition name="adm-err">
                    <p v-if="form.errors.phone" class="adm-error">{{ form.errors.phone }}</p>
                </Transition>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="adm-field">
                <p class="adm-sub">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button" class="adm-link-btn">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <p v-show="status === 'verification-link-sent'" class="ast-saved" style="margin-top: 0.5rem">
                    A new verification link has been sent to your email address.
                </p>
            </div>

            <div class="ast-form-foot">
                <button type="submit" class="adm-btn" :disabled="form.processing">Save</button>

                <Transition name="adm-err">
                    <p v-if="form.recentlySuccessful" class="ast-saved">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>