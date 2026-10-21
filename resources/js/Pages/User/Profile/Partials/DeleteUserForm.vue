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

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('user.profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
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
        <header class="pf-head">
            <span class="pf-head-icon"><Icon icon="mdi:alert-outline" width="24" height="24" /></span>
            <div>
                <h2>Delete Account</h2>
                <p>
                    Once your account is deleted, all of its resources and data will be permanently deleted. Before
                    deleting your account, please download any data or information that you wish to retain.
                </p>
            </div>
        </header>

        <button type="button" class="usr-btn usr-btn-danger" @click="confirmUserDeletion">
            <Icon icon="mdi:trash-can-outline" width="18" height="18" /> Delete Account
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div style="padding: 1.75rem">
                <div class="usr-modal-icon is-red" style="margin-left: 0">
                    <Icon icon="mdi:trash-can-outline" />
                </div>

                <h2 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #1f2937">
                    Are you sure you want to delete your account?
                </h2>

                <p style="margin: 0.5rem 0 0; font-size: 0.9rem; line-height: 1.5; color: #6b7280">
                    Once your account is deleted, all of its resources and data will be permanently deleted. Please
                    enter your password to confirm you would like to permanently delete your account.
                </p>

                <div style="margin-top: 1.25rem">
                    <label for="password" class="usr-label" style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0)">Password</label>
                    <input id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="usr-input" :class="{ 'is-error': form.errors.password }" placeholder="Password"
                        @keyup.enter="deleteUser" />
                    <p v-if="form.errors.password" class="usr-error">{{ form.errors.password }}</p>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.7rem; margin-top: 1.5rem">
                    <button type="button" class="usr-btn usr-btn-ghost" @click="closeModal">Cancel</button>

                    <button type="button" class="usr-btn usr-btn-danger" :disabled="form.processing"
                        @click="deleteUser">
                        <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="usr-spin" />
                        Delete Account
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>