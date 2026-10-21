<script setup>
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref, computed } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};

// ---------- presentation helpers (no behaviour change) ----------
const show = ref({ current: false, next: false, confirm: false })

const strength = computed(() => {
    const p = form.password || ''
    if (!p) return 0
    let s = 0
    if (p.length >= 8) s++
    if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++
    if (/\d/.test(p)) s++
    if (/[^A-Za-z0-9]/.test(p) || p.length >= 14) s++
    return s
})
const strengthMeta = computed(() => [
    { label: '', color: '#ffe8d4' },
    { label: 'Weak', color: '#ef4444' },
    { label: 'Fair', color: '#f59e0b' },
    { label: 'Good', color: '#22c55e' },
    { label: 'Strong', color: '#16a34a' },
][strength.value])

const mismatch = computed(() => form.password_confirmation && form.password !== form.password_confirmation)
</script>

<template>
    <section>
        <header class="pf-head">
            <span class="pf-head-icon"><Icon icon="mdi:shield-lock-outline" width="24" height="24" /></span>
            <div>
                <h2>Update Password</h2>
                <p>Ensure your account is using a long, random password to stay secure.</p>
            </div>
        </header>

        <form @submit.prevent="updatePassword" class="pf-fields">
            <div>
                <label for="current_password" class="usr-label">Current Password</label>
                <div class="pf-input-wrap">
                    <input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                        :type="show.current ? 'text' : 'password'" class="usr-input pf-has-eye"
                        :class="{ 'is-error': form.errors.current_password }" autocomplete="current-password" />
                    <button type="button" class="pf-eye" @click="show.current = !show.current"
                        :aria-label="show.current ? 'Hide password' : 'Show password'">
                        <Icon :icon="show.current ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" height="20" />
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="usr-error">{{ form.errors.current_password }}</p>
            </div>

            <div>
                <label for="password" class="usr-label">New Password</label>
                <div class="pf-input-wrap">
                    <input id="password" ref="passwordInput" v-model="form.password"
                        :type="show.next ? 'text' : 'password'" class="usr-input pf-has-eye"
                        :class="{ 'is-error': form.errors.password }" autocomplete="new-password" />
                    <button type="button" class="pf-eye" @click="show.next = !show.next"
                        :aria-label="show.next ? 'Hide password' : 'Show password'">
                        <Icon :icon="show.next ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" height="20" />
                    </button>
                </div>
                <div v-if="form.password" class="pf-meter">
                    <i v-for="n in 4" :key="n" :style="{ background: n <= strength ? strengthMeta.color : '' }"></i>
                </div>
                <p v-if="form.password" class="pf-meter-label" :style="{ color: strengthMeta.color }">{{ strengthMeta.label }}</p>
                <p v-if="form.errors.password" class="usr-error">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="usr-label">Confirm Password</label>
                <div class="pf-input-wrap">
                    <input id="password_confirmation" v-model="form.password_confirmation"
                        :type="show.confirm ? 'text' : 'password'" class="usr-input pf-has-eye"
                        :class="{ 'is-error': form.errors.password_confirmation || mismatch }"
                        autocomplete="new-password" />
                    <button type="button" class="pf-eye" @click="show.confirm = !show.confirm"
                        :aria-label="show.confirm ? 'Hide password' : 'Show password'">
                        <Icon :icon="show.confirm ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" height="20" />
                    </button>
                </div>
                <p v-if="mismatch" class="usr-error">Passwords do not match yet.</p>
                <p v-if="form.errors.password_confirmation" class="usr-error">{{ form.errors.password_confirmation }}</p>
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