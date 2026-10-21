<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthField from '@/Components/AuthField.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';

const page = usePage();
const form = useForm({
    account_type: 'user',
    name: '',
    email: '',
    phone: '',
    otp: '',
    dob: '',
    birth_time: '',
    gender: '',
    location: '',
    preferred_language: 'Hindi',
    user_profile_image: null,
    bio: '',
    expertise: '',
    experience_years: '',
    astrologer_profile_image: null,
    documents: [],
    password: '',
    password_confirmation: '',
});

const otpForm = useForm({
    phone: '',
});

const otpSent = ref(false);
const astrologerImageName = ref('');
const userImageName = ref('');
const validationErrors = computed(() => [
    ...new Set(
        [...Object.values(page.props.errors ?? {}), ...Object.values(form.errors), ...Object.values(otpForm.errors)]
            .filter((error) => typeof error === 'string' && error.trim())
    ),
]);
const flashError = computed(() => page.props.flash?.error);
const fieldErrors = computed(() => ({
    ...page.props.errors,
    ...otpForm.errors,
    ...form.errors,
}));

const setAccountType = (accountType) => {
    form.account_type = accountType;
    form.clearErrors();
};

const setProfileImage = (event, field, imageName) => {
    const file = event.target.files?.[0] ?? null;
    form[field] = file;
    imageName.value = file?.name ?? '';
};

const addDocument = () => {
    form.documents.push({ name: '', file: null });
};

const removeDocument = (index) => {
    form.documents.splice(index, 1);
};

const setDocumentFile = (event, index) => {
    form.documents[index].file = event.target.files?.[0] ?? null;
};

const sendOtp = () => {
    otpForm.phone = form.phone;
    otpForm.post(route('register.otp.send'), {
        preserveScroll: true,
        onSuccess: () => {
            otpSent.value = true;
            form.otp = '';
        },
    });
};

const submit = () => {
    form.post(route('register'), { forceFormData: true });
};
</script>

<template>
    <AuthLayout title="Create your account" subtitle="Join MyAstroSathi and get guidance from expert astrologers">
        <Head title="Register" />

        <form @submit.prevent="submit" class="auth-form">
            <div v-if="flashError || validationErrors.length" class="auth-error-summary" role="alert">
                <Icon icon="mdi:alert-circle-outline" width="20" height="20" />
                <div>
                    <p v-if="flashError">{{ flashError }}</p>
                    <template v-if="validationErrors.length">
                        <p>Please fix the following errors:</p>
                        <ul>
                            <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
                        </ul>
                    </template>
                </div>
            </div>

            <div class="auth-role-tabs" role="tablist" aria-label="Choose account type">
                <button
                    type="button"
                    role="tab"
                    :aria-selected="form.account_type === 'user'"
                    :class="{ 'is-active': form.account_type === 'user' }"
                    @click="setAccountType('user')"
                >
                    Register as User
                </button>
                <button
                    type="button"
                    role="tab"
                    :aria-selected="form.account_type === 'astrologer'"
                    :class="{ 'is-active': form.account_type === 'astrologer' }"
                    @click="setAccountType('astrologer')"
                >
                    Register as Astrologer
                </button>
            </div>

            <AuthField id="name" label="Name" icon="mdi:account-outline" placeholder="Your full name"
                v-model="form.name" :error="fieldErrors.name" required autofocus autocomplete="name" />

            <AuthField id="email" label="Email" type="email" icon="mdi:email-outline" placeholder="you@example.com"
                v-model="form.email" :error="fieldErrors.email" required autocomplete="username" />

            <div class="auth-form auth-form-tight" style="gap: 0">
                <AuthField id="phone" label="WhatsApp phone number" type="tel" icon="mdi:whatsapp" prefix="+91"
                    placeholder="10-digit number" v-model="form.phone"
                    :error="fieldErrors.phone" required inputmode="numeric"
                    pattern="[0-9]{10}" maxlength="10" autocomplete="tel-national" @input="otpSent = false" />

                <button type="button" class="auth-btn auth-btn-outline"
                    :disabled="otpForm.processing || form.phone.length !== 10" @click="sendOtp">
                    <Icon v-if="otpForm.processing" icon="mdi:loading" width="18" height="18" class="auth-spin" />
                    {{ otpSent ? 'Resend OTP' : 'Send OTP' }}
                </button>
            </div>

            <Transition name="field">
                <AuthField v-if="otpSent" id="otp" label="One-time password" icon="mdi:shield-key-outline" code
                    placeholder="6-digit code" v-model="form.otp" :error="fieldErrors.otp" required
                    inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" />
            </Transition>

            <template v-if="form.account_type === 'user'">
                <div class="auth-profile-grid">
                    <AuthField id="dob" label="Date of birth" type="date" icon="mdi:cake-variant-outline"
                        v-model="form.dob" :error="fieldErrors.dob" />

                    <AuthField id="birth_time" label="Birth time" type="time" icon="mdi:clock-outline"
                        v-model="form.birth_time" :error="fieldErrors.birth_time" />
                </div>

                <label class="auth-select-label" for="gender">Gender</label>
                <select id="gender" v-model="form.gender" class="auth-select"
                    :aria-invalid="!!fieldErrors.gender" :aria-describedby="fieldErrors.gender ? 'gender-error' : undefined">
                    <option value="">Prefer not to say</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <p v-if="fieldErrors.gender" id="gender-error" class="auth-field-error">{{ fieldErrors.gender }}</p>

                <AuthField id="location" label="Location" icon="mdi:map-marker-outline"
                    placeholder="City or town" v-model="form.location" :error="fieldErrors.location" />

                <AuthField id="preferred_language" label="Preferred language" icon="mdi:translate"
                    placeholder="Hindi" v-model="form.preferred_language"
                    :error="fieldErrors.preferred_language" />

                <label class="auth-upload-label" for="user_profile_image">Profile photo</label>
                <input id="user_profile_image" type="file" accept="image/jpeg,image/png,image/webp"
                    class="auth-file-input" @change="setProfileImage($event, 'user_profile_image', userImageName)" />
                <p v-if="userImageName" class="auth-hint">{{ userImageName }}</p>
                <p v-if="fieldErrors.user_profile_image" class="auth-field-error">{{ fieldErrors.user_profile_image }}</p>
            </template>

            <template v-else>
                <div>
                    <label class="auth-select-label" for="bio">Professional bio</label>
                    <textarea id="bio" v-model="form.bio" class="auth-textarea" rows="4"
                        placeholder="Tell clients about your background and approach" required />
                    <p v-if="fieldErrors.bio" class="auth-field-error">{{ fieldErrors.bio }}</p>
                </div>

                <AuthField id="expertise" label="Areas of expertise" icon="mdi:star-outline"
                    placeholder="Vedic astrology, Tarot (separate with commas)" v-model="form.expertise"
                    :error="fieldErrors.expertise" required />

                <AuthField id="experience_years" label="Years of experience" type="number"
                    icon="mdi:briefcase-outline" placeholder="Years of experience"
                    v-model="form.experience_years" :error="fieldErrors.experience_years"
                    required min="0" max="80" />

                <label class="auth-upload-label" for="astrologer_profile_image">Profile image</label>
                <input id="astrologer_profile_image" type="file" accept="image/jpeg,image/png,image/webp"
                    class="auth-file-input"
                    @change="setProfileImage($event, 'astrologer_profile_image', astrologerImageName)" required />
                <p v-if="astrologerImageName" class="auth-hint">{{ astrologerImageName }}</p>
                <p v-if="fieldErrors.astrologer_profile_image" class="auth-field-error">
                    {{ fieldErrors.astrologer_profile_image }}
                </p>

                <div class="auth-documents">
                    <div class="auth-documents-heading">
                        <div>
                            <h3>Supporting documents <span>(optional)</span></h3>
                            <p>Add certificates or credentials for Admin review.</p>
                        </div>
                        <button type="button" class="auth-btn auth-btn-outline" @click="addDocument">
                            Add document
                        </button>
                    </div>
                    <div v-for="(document, index) in form.documents" :key="index" class="auth-document-row">
                        <AuthField :id="`document-name-${index}`" label="Document name"
                            icon="mdi:file-document-outline" placeholder="Certificate name"
                            v-model="document.name" :error="fieldErrors[`documents.${index}.name`]" required />
                        <div>
                            <label class="auth-upload-label" :for="`document-file-${index}`">File (PDF or image)</label>
                            <input :id="`document-file-${index}`" type="file" accept="application/pdf,image/jpeg,image/png"
                                class="auth-file-input" required @change="setDocumentFile($event, index)" />
                            <p v-if="document.file" class="auth-hint">{{ document.file.name }}</p>
                            <p v-if="fieldErrors[`documents.${index}.file`]" class="auth-field-error">
                                {{ fieldErrors[`documents.${index}.file`] }}
                            </p>
                        </div>
                        <button type="button" class="auth-remove-document" :aria-label="`Remove document ${index + 1}`"
                            @click="removeDocument(index)">Remove</button>
                    </div>
                </div>
            </template>

            <AuthField id="password" label="Password" type="password" icon="mdi:lock-outline"
                placeholder="Create a password" v-model="form.password" :error="fieldErrors.password" required
                autocomplete="new-password" />

            <AuthField id="password_confirmation" label="Confirm Password" type="password"
                icon="mdi:lock-check-outline" placeholder="Re-enter your password" v-model="form.password_confirmation"
                :error="fieldErrors.password_confirmation" required autocomplete="new-password" />

            <div>
                <button type="submit" class="auth-btn" :disabled="form.processing || !otpSent">
                    <Icon v-if="form.processing" icon="mdi:loading" width="20" height="20" class="auth-spin" />
                    Register
                </button>
                <p v-if="!otpSent" class="auth-hint">
                    Verify your WhatsApp number with an OTP to enable registration.
                </p>
            </div>

            <p class="auth-center">
                Already registered?
                <Link :href="route('login')" class="auth-link">Log in</Link>
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

.auth-role-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    padding: 0.3rem;
    border: 1px solid #fed7aa;
    border-radius: 0.75rem;
    background: rgba(255, 247, 237, 0.75);
}

.auth-role-tabs button {
    padding: 0.7rem 0.5rem;
    border: 0;
    border-radius: 0.55rem;
    background: transparent;
    color: #7c2d12;
    font-weight: 600;
    cursor: pointer;
}

.auth-role-tabs button.is-active {
    background: #ff7010;
    color: white;
    box-shadow: 0 2px 6px rgba(124, 45, 18, 0.15);
}

.auth-profile-grid,
.auth-document-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.auth-select-label,
.auth-upload-label {
    display: block;
    margin-bottom: 0.375rem;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
}

.auth-select,
.auth-textarea,
.auth-file-input {
    box-sizing: border-box;
    width: 100%;
    padding: 0.65rem 0.8rem;
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.95);
    color: #1f2937;
}

.auth-textarea {
    resize: vertical;
}

.auth-field-error {
    margin: 0.375rem 0 0;
    color: #dc2626;
    font-size: 0.875rem;
}

.auth-error-summary {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.875rem 1rem;
    border: 1px solid #fca5a5;
    border-radius: 0.625rem;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 0.875rem;
}

.auth-error-summary p {
    margin: 0;
    font-weight: 600;
}

.auth-error-summary ul {
    margin: 0.375rem 0 0;
    padding-left: 1.25rem;
}

.auth-documents {
    display: grid;
    gap: 1rem;
}

.auth-documents-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.auth-documents-heading h3 {
    color: #374151;
    font-weight: 600;
}

.auth-documents-heading h3 span,
.auth-documents-heading p {
    color: #6b7280;
    font-size: 0.8rem;
}

.auth-document-row {
    align-items: end;
    padding: 1rem;
    border: 1px solid #fed7aa;
    border-radius: 0.75rem;
}

.auth-remove-document {
    padding: 0.6rem;
    color: #b91c1c;
    font-size: 0.875rem;
}

@media (max-width: 560px) {
    .auth-role-tabs button {
        font-size: 0.85rem;
    }

    .auth-profile-grid,
    .auth-document-row {
        grid-template-columns: 1fr;
    }
}
</style>