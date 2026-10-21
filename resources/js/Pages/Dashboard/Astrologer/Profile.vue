<script setup>
import AstrologerLayout from '@/Layouts/AstrologerLayout.vue';
import DeleteUserForm from './DeleteUserForm.vue';
import UpdatePasswordForm from './UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import UpdateAstrologerInformationForm from './UpdateAstrologerInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: String,
    }
});
</script>

<template>

    <Head title="Profile" />

    <AstrologerLayout>
        <template #header>
            <h2 class="adm-title">Profile</h2>
        </template>

        <div class="ast-grid">
            <div class="ast-stack">
                <div class="adm-card ast-pad">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>
                <div class="adm-card ast-pad">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
                <div class="adm-card ast-pad">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>

            <div class="adm-card ast-pad">
                <UpdateAstrologerInformationForm :status="status" class="max-w-xl" />
            </div>
        </div>
    </AstrologerLayout>
</template>