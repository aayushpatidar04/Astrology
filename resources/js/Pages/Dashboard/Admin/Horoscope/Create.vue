<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminField from '@/Components/AdminField.vue'
import Modal from '@/Components/Modal.vue';
import { ref, inject } from 'vue'
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { Icon } from '@iconify/vue';

const showHoroscopeModal = ref(false);

const alertRef = inject('alertRef')

const form = useForm({
    sign: '',
    type: 'daily',
    date: '',
    week_key: '',
    month_key: '',
    year_key: '',
    colors: '',
    numbers: '',
    alphabets: '',
    love: '',
    career: '',
    emotions: '',
    health: '',
    travel: '',
    description: '',
    cosmic_tip: '',
    tip_for_singles: '',
    tip_for_couples: '',
})

const closeModal = () => {
    showHoroscopeModal.value = false;

    form.clearErrors();
    form.reset();
};

const openAddHoroscope = () => {
    showHoroscopeModal.value = true
}

function submit() {
    form.post(route('admin.horoscopes.store'), {
        onSuccess: () => {
            showHoroscopeModal.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Horoscope added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}


const selectedColors = ref([])
const currentColor = ref('#F97317') // default picker value

function addColor() {
    if (currentColor.value && !selectedColors.value.includes(currentColor.value)) {
        selectedColors.value.push(currentColor.value)
        updateFormColors()
    }
}

function removeColor(color) {
    selectedColors.value = selectedColors.value.filter(c => c !== color)
    updateFormColors()
}

function updateFormColors() {
    form.colors = selectedColors.value.join(', ')
}

</script>

<template>
    <button @click="openAddHoroscope" class="adm-btn">
        <Icon icon="mdi-plus" width="20" height="20" /> Add Horoscope
    </button>

    <Modal :show="showHoroscopeModal" @close="closeModal">
        <form @submit.prevent="submit">
            <div class="adm-modal-head">
                <h3>Add Horoscope</h3>
                <button type="button" class="adm-icon-btn" aria-label="Close" @click="closeModal">
                    <Icon icon="mdi:close" />
                </button>
            </div>

            <div class="adm-modal-body">
                <div class="adm-grid-2">
                    <!-- Sign -->
                    <AdminField label="Zodiac Sign" for-id="sign" :error="form.errors.sign">
                        <select v-model="form.sign" id="sign" class="adm-input" required>
                            <option value="">Select Sign</option>
                            <option
                                v-for="sign in ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces']"
                                :key="sign" :value="sign">
                                {{ sign }}
                            </option>
                        </select>
                    </AdminField>

                    <!-- Type -->
                    <AdminField label="Type" for-id="type" :error="form.errors.type">
                        <select v-model="form.type" id="type" class="adm-input" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </AdminField>

                    <!-- Time Keys -->
                    <AdminField v-if="form.type === 'daily'" label="Date" for-id="date" :error="form.errors.date">
                        <input id="date" type="date" v-model="form.date" class="adm-input" />
                    </AdminField>

                    <AdminField v-if="form.type === 'weekly'" label="Week Key (YYYY-WW)" for-id="week_key"
                        :error="form.errors.week_key">
                        <input id="week_key" type="text" v-model="form.week_key" placeholder="2026-11"
                            class="adm-input" />
                    </AdminField>

                    <AdminField v-if="form.type === 'monthly'" label="Month Key (YYYY-MM)" for-id="month_key"
                        :error="form.errors.month_key">
                        <input id="month_key" type="month" v-model="form.month_key" class="adm-input" />
                    </AdminField>

                    <AdminField v-if="form.type === 'yearly'" label="Year Key (YYYY)" for-id="year_key"
                        :error="form.errors.year_key">
                        <input id="year_key" type="text" v-model="form.year_key" placeholder="2026"
                            class="adm-input" />
                    </AdminField>

                    <AdminField label="Colors" :error="form.errors.colors" hint="Pick a color and confirm it; click a swatch to remove it.">
                        <!-- Color picker with confirm button -->
                        <div class="adm-swatches">
                            <input type="color" v-model="currentColor" style="width: 2.6rem; height: 2.4rem; padding: 0; border: 0; background: none; cursor: pointer" />
                            <button type="button" @click="addColor" class="adm-icon-btn adm-icon-add"
                                title="Add color">
                                <Icon icon="mdi:check" />
                            </button>
                            <!-- Show selected colors -->
                            <span v-for="color in selectedColors" :key="color" class="adm-swatch"
                                :style="{ backgroundColor: color }" :title="`Remove ${color}`"
                                @click="removeColor(color)">
                            </span>
                        </div>

                        <!-- Hidden text field with comma-separated codes -->
                        <input id="colors" type="hidden" v-model="form.colors" readonly />
                    </AdminField>

                    <AdminField label="Numbers" for-id="numbers" :error="form.errors.numbers">
                        <input id="numbers" type="text" v-model="form.numbers" placeholder="e.g. 3, 7, 11"
                            class="adm-input" required />
                    </AdminField>

                    <AdminField label="Alphabets" for-id="alphabets" :error="form.errors.alphabets">
                        <input id="alphabets" type="text" v-model="form.alphabets" placeholder="e.g. A, K, M"
                            class="adm-input" required />
                    </AdminField>
                </div>

                <AdminField label="Love" for-id="love" :error="form.errors.love">
                    <input id="love" type="text" v-model="form.love" class="adm-input" required />
                </AdminField>

                <AdminField label="Health" for-id="health" :error="form.errors.health">
                    <input id="health" type="text" v-model="form.health" class="adm-input" required />
                </AdminField>

                <AdminField label="Career" for-id="career" :error="form.errors.career">
                    <input id="career" type="text" v-model="form.career" class="adm-input" required />
                </AdminField>

                <AdminField label="Emotions" for-id="emotions" :error="form.errors.emotions">
                    <input id="emotions" type="text" v-model="form.emotions" class="adm-input" required />
                </AdminField>

                <AdminField label="Travel" for-id="travel" :error="form.errors.travel">
                    <input id="travel" type="text" v-model="form.travel" class="adm-input" required />
                </AdminField>

                <!-- Description -->
                <AdminField label="Description" :error="form.errors.description">
                    <QuillEditor v-model:content="form.description" contentType="html" theme="snow"
                        placeholder="Write your content here..." style="height:300px" />
                </AdminField>

                <!-- Other Tips -->
                <AdminField label="Cosmic Tip" for-id="cosmic_tip" :error="form.errors.cosmic_tip">
                    <input id="cosmic_tip" type="text" v-model="form.cosmic_tip" class="adm-input" required />
                </AdminField>

                <AdminField label="Tip for Singles" for-id="tip_for_singles" :error="form.errors.tip_for_singles">
                    <input id="tip_for_singles" type="text" v-model="form.tip_for_singles" class="adm-input"
                        required />
                </AdminField>

                <AdminField label="Tip for Couples" for-id="tip_for_couples" :error="form.errors.tip_for_couples">
                    <input id="tip_for_couples" type="text" v-model="form.tip_for_couples" class="adm-input"
                        required />
                </AdminField>
            </div>

            <!-- Submit -->
            <div class="adm-modal-foot">
                <button type="button" @click="closeModal" class="adm-btn adm-btn-ghost">Cancel</button>
                <button type="submit" class="adm-btn" :disabled="form.processing">
                    <Icon v-if="form.processing" icon="mdi:loading" width="18" height="18" class="adm-spin" />
                    Save Horoscope
                </button>
            </div>
        </form>
    </Modal>
</template>