<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthField from '@/Components/AuthField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    phone: '',
    otp: '',
    remember: false,
});

const otpSent = ref(false);

const sendOtp = () => {
    form.post(route('login.otp.send'), {
        preserveScroll: true,
        onSuccess: () => {
            otpSent.value = true;
            form.clearErrors('otp');
        },
    });
};

const submit = () => {
    form.post(route('login'));
};
</script>

<template>
    <AuthLayout title="Welcome back" subtitle="Log in with your WhatsApp number to continue">
        <Head title="Log in" />

        <div v-if="status" class="auth-alert">
            <Icon icon="mdi:check-circle-outline" width="18" height="18" />
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="otpSent ? submit() : sendOtp()" class="auth-form">
            <AuthField id="phone" label="WhatsApp phone number" type="tel" icon="mdi:whatsapp" prefix="+91"
                placeholder="10-digit number" v-model="form.phone" :error="form.errors.phone" required autofocus
                inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="tel-national"
                @input="otpSent = false" />

            <Transition name="field">
                <div v-if="otpSent" class="auth-form auth-form-tight">
                    <AuthField id="otp" label="One-time password" icon="mdi:shield-key-outline" code
                        placeholder="6-digit code" v-model="form.otp" :error="form.errors.otp" required
                        inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" />

                    <div class="auth-row">
                        <label class="auth-check">
                            <input type="checkbox" v-model="form.remember" />
                            <span>Remember me</span>
                        </label>

                        <button type="button" class="auth-link" :disabled="form.processing" @click="sendOtp">
                            Resend OTP
                        </button>
                    </div>
                </div>
            </Transition>

            <button type="submit" class="auth-btn"
                :disabled="form.processing || (otpSent && form.otp.length !== 6)">
                <Icon v-if="form.processing" icon="mdi:loading" width="20" height="20" class="auth-spin" />
                {{ otpSent ? 'Log in' : 'Send OTP' }}
            </button>

            <p class="auth-center">
                New here?
                <Link :href="route('register')" class="auth-link">Create an account</Link>
            </p>
        </form>
    </AuthLayout>
</template>

<style scoped>
.field-enter-active,
.field-leave-active {
    transition: opacity 0.35s ease, transform 0.35s ease;
}

.field-enter-from,
.field-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>