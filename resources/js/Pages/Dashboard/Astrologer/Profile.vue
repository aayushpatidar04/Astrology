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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-2 gap-5">
                <div>
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                            class="max-w-xl" />
                    </div>
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 mt-5">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>
                </div>
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateAstrologerInformationForm :status="status" class="max-w-xl" />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AstrologerLayout>
</template>
