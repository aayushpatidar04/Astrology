<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
</script>

<template>
    <section>
        <header class="ast-form-head">
            <h2>Update Password</h2>
            <p>Ensure your account is using a long, random password to stay secure.</p>
        </header>

        <form @submit.prevent="updatePassword">
            <div class="adm-field">
                <label for="current_password" class="adm-label">Current Password</label>
                <input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                    type="password" class="adm-input" autocomplete="current-password" />
                <Transition name="adm-err">
                    <p v-if="form.errors.current_password" class="adm-error">{{ form.errors.current_password }}</p>
                </Transition>
            </div>

            <div class="adm-field">
                <label for="password" class="adm-label">New Password</label>
                <input id="password" ref="passwordInput" v-model="form.password" type="password"
                    class="adm-input" autocomplete="new-password" />
                <Transition name="adm-err">
                    <p v-if="form.errors.password" class="adm-error">{{ form.errors.password }}</p>
                </Transition>
            </div>

            <div class="adm-field">
                <label for="password_confirmation" class="adm-label">Confirm Password</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                    class="adm-input" autocomplete="new-password" />
                <Transition name="adm-err">
                    <p v-if="form.errors.password_confirmation" class="adm-error">
                        {{ form.errors.password_confirmation }}
                    </p>
                </Transition>
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