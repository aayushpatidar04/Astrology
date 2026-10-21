<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <header class="ast-form-head">
            <h2>Delete Account</h2>
            <p>
                Once your account is deleted, all of its resources and data will be permanently deleted.
                Before deleting your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <button type="button" class="adm-btn adm-btn-danger" @click="confirmUserDeletion">
            <Icon icon="mdi:delete-outline" width="18" height="18" /> Delete Account
        </button>

        <Modal :show="confirmingUserDeletion" max-width="md" @close="closeModal">
            <div class="adm-confirm">
                <div class="adm-confirm-icon">
                    <Icon icon="mdi:alert-outline" />
                </div>
                <h3>Are you sure you want to delete your account?</h3>
                <p>
                    Once your account is deleted, all of its resources and data will be permanently deleted.
                    Please enter your password to confirm.
                </p>

                <div class="adm-confirm-field">
                    <label for="password" class="adm-label">Password</label>
                    <input id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="adm-input" placeholder="Password" @keyup.enter="deleteUser" />
                    <Transition name="adm-err">
                        <p v-if="form.errors.password" class="adm-error">{{ form.errors.password }}</p>
                    </Transition>
                </div>

                <div class="adm-confirm-actions">
                    <button type="button" class="adm-btn adm-btn-ghost" @click="closeModal">Cancel</button>
                    <button type="button" class="adm-btn adm-btn-danger" :disabled="form.processing"
                        @click="deleteUser">
                        <Icon v-if="form.processing" icon="mdi:loading" class="adm-spin" />
                        Delete Account
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>