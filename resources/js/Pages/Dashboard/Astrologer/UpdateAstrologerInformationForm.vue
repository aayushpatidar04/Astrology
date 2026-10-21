<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { ref, inject, watch } from 'vue';

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
        <header class="ast-form-head">
            <h2>Profile Information</h2>
            <p>Update your astrologer profile shown to users.</p>
        </header>

        <form @submit.prevent="form.post(route('astrologer.astrologer.update'))" enctype="multipart/form-data">

            <!-- Profile Image -->
            <div class="adm-field">
                <label for="profile_image" class="adm-label">Profile Image</label>
                <div class="ast-photo-row">
                    <img v-if="previewImage" :src="previewImage" alt="Preview" class="ast-photo" />
                    <span v-else class="ast-photo ast-photo-empty">
                        <Icon icon="mdi:account-star-outline" />
                    </span>
                    <input id="profile_image" type="file" accept="image/*" class="adm-file" style="flex: 1 1 12rem; width: auto"
                        @change="handleProfileImage" />
                </div>
                <Transition name="adm-err">
                    <p v-if="form.errors.profile_image" class="adm-error">{{ form.errors.profile_image }}</p>
                </Transition>
            </div>

            <div class="adm-grid-2">
                <!-- Expertise -->
                <div class="adm-field">
                    <label for="expertise" class="adm-label">Expertise</label>
                    <input id="expertise" type="text" class="adm-input" v-model="form.expertise" required />
                    <Transition name="adm-err">
                        <p v-if="form.errors.expertise" class="adm-error">{{ form.errors.expertise }}</p>
                    </Transition>
                </div>

                <!-- Experience -->
                <div class="adm-field">
                    <label for="experience_years" class="adm-label">Years of Experience</label>
                    <input id="experience_years" type="number" inputmode="numeric" class="adm-input"
                        v-model="form.experience_years" required />
                    <Transition name="adm-err">
                        <p v-if="form.errors.experience_years" class="adm-error">{{ form.errors.experience_years }}</p>
                    </Transition>
                </div>
            </div>

            <!-- Bio -->
            <div class="adm-field">
                <label for="bio" class="adm-label">Bio</label>
                <textarea id="bio" class="adm-input" v-model="form.bio" required
                    placeholder="Brief about yourself..."></textarea>
                <Transition name="adm-err">
                    <p v-if="form.errors.bio" class="adm-error">{{ form.errors.bio }}</p>
                </Transition>
            </div>

            <!-- Documents -->
            <div class="adm-field">
                <p class="adm-section-label">Documents</p>

                <div class="ast-docs">
                    <div v-for="(doc, index) in form.documents" :key="index">
                        <div class="ast-doc">
                            <input type="text" placeholder="Document name" v-model="doc.name" class="adm-input" />
                            <div class="ast-doc-file">
                                <input type="file" class="adm-file" @change="handleDocumentFile($event, index)" />
                                <a v-if="doc.document_path" :href="`/${doc.document_path}`" target="_blank"
                                    class="adm-link" style="margin-top: 0.3rem">
                                    <Icon icon="mdi:open-in-new" width="14" height="14" /> View {{ doc.name }}
                                </a>
                            </div>
                            <button type="button" class="adm-icon-btn adm-icon-del" aria-label="Remove document"
                                @click="removeDocument(index)">
                                <Icon icon="mdi:close" />
                            </button>
                        </div>
                        <p v-if="form.errors[`documents.${index}.file`]" class="adm-error">
                            {{ form.errors[`documents.${index}.file`] }}
                        </p>
                        <p v-if="form.errors[`documents.${index}.name`]" class="adm-error">
                            {{ form.errors[`documents.${index}.name`] }}
                        </p>
                    </div>
                </div>

                <button type="button" class="adm-btn adm-btn-ghost adm-btn-sm" style="margin-top: 0.75rem"
                    @click="addDocument">
                    <Icon icon="mdi:plus" width="16" height="16" /> Add document
                </button>
            </div>

            <!-- Save -->
            <div class="ast-form-foot">
                <button type="submit" class="adm-btn" :disabled="form.processing">Save</button>
                <Transition name="adm-err">
                    <p v-if="form.recentlySuccessful" class="ast-saved">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>