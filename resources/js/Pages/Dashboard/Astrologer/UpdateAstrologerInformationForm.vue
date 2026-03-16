<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { inject, watch } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const alertRef = inject('alertRef')
const astrologer = usePage().props.auth.user.astrologer;

const form = useForm({
    bio: astrologer.bio,
    expertise: astrologer.expertise,
    experience_years: astrologer.experience_years,
    documents: Array.isArray(astrologer.documents) ? astrologer.documents : [],
    profile_image: astrologer.profile_image,
});


const previewImage = ref(
    astrologer.profile_image
        ? `/${astrologer.profile_image}`
        : null
)

function handleProfileImage(event) {
    const file = event.target.files[0]
    form.profile_image = file
    if (file) {
        previewImage.value = URL.createObjectURL(file)
    }
}

function addDocument() {
    form.documents.push({ name: '', file: null })
}

function removeDocument(index) {
    form.documents.splice(index, 1)
}

function handleDocumentFile(event, index) {
    form.documents[index].file = event.target.files[0]
}

// Watch for recentlySuccessful changes
watch(() => form.recentlySuccessful, (newVal) => {
  if (newVal) {
    alertRef.value?.showAlert('Profile Info updated successfully!', 'success')
  }
})
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information.
            </p>
        </header>

        <form @submit.prevent="form.post(route('astrologer.astrologer.update'))" class="mt-6 space-y-6"
            enctype="multipart/form-data">

            <!-- Profile Image -->
            <div>
                <InputLabel for="profile_image" value="Profile Image" />
                <div class="flex gap-5 items-center">
                    <div v-if="previewImage" class="mt-2">
                        <img :src="previewImage" alt="Preview" class="w-32 h-auto rounded object-cover" />
                    </div>
                    <input id="profile_image" type="file" accept="image/*" @change="handleProfileImage" class="block w-1/2 text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100" />
                </div>
                <InputError class="mt-2" :message="form.errors.profile_image" />
            </div>

            <!-- Expertise -->
            <div>
                <InputLabel for="expertise" value="Expertise" />
                <TextInput id="expertise" type="text" class="mt-1 block w-full" v-model="form.expertise" required />
                <InputError class="mt-2" :message="form.errors.expertise" />
            </div>

            <!-- Experience -->
            <div>
                <InputLabel for="experience_years" value="Years of Experience" />
                <TextInput id="experience_years" type="number" inputmode="numeric" class="mt-1 block w-full"
                    v-model="form.experience_years" required />
                <InputError class="mt-2" :message="form.errors.experience_years" />
            </div>

            <!-- Bio -->
            <div>
                <InputLabel for="bio" value="Bio" />
                <textarea id="bio"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.bio" required placeholder="Brief about yourself..."></textarea>
                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <!-- Documents -->
            <div>
                <InputLabel value="Documents" />
                <div v-for="(doc, index) in form.documents" :key="index" class="mt-2">
                    <div class="flex items-center gap-2 ">
                        <TextInput type="text" placeholder="Document Name" v-model="doc.name" class="w-1/3" />
                        <div>
                            <input type="file" @change="handleDocumentFile($event, index)"
                                class="w-2/3 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <div v-if="doc.document_path" class="mt-1 text-center">
                                <a :href="`/${doc.document_path}`" target="_blank" class="text-blue-600 underline">
                                    View {{ doc.name }}
                                </a>
                            </div>
                        </div>
                        <button type="button" @click="removeDocument(index)" class="text-red-600 font-bold">−</button>
                    </div>
                    <InputError :message="form.errors[`documents.${index}.file`]" />
                    <InputError :message="form.errors[`documents.${index}.name`]" />

                </div>
                <button type="button" @click="addDocument" class="mt-2 text-green-600 font-bold">＋ Add Document</button>
            </div>

            <!-- Save -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
                </Transition>
            </div>
        </form>

    </section>
</template>
