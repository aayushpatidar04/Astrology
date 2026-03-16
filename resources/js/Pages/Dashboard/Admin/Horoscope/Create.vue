<script setup>
import { useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
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
    <button @click="openAddHoroscope"
        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 font-bold flex items-center">
        <Icon icon="mdi-plus" width="20" height="20" class="mr-1" /> Add Horoscope
    </button>

    <Modal :show="showHoroscopeModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Add Horoscope</h2>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-2 gap-5">
                    <!-- Sign -->
                    <div>
                        <InputLabel for="sign" value="Zodiac Sign" />
                        <select v-model="form.sign" id="sign"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                            <option value="">Select Sign</option>
                            <option
                                v-for="sign in ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces']"
                                :key="sign" :value="sign">
                                {{ sign }}
                            </option>
                        </select>
                        <InputError :message="form.errors.sign" class="mt-2" />
                    </div>

                    <!-- Type -->
                    <div>
                        <InputLabel for="type" value="Type" />
                        <select v-model="form.type" id="type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <!-- Time Keys -->
                    <div v-if="form.type === 'daily'">
                        <InputLabel for="date" value="Date" />
                        <TextInput id="date" type="date" v-model="form.date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.date" class="mt-2" />
                    </div>

                    <div v-if="form.type === 'weekly'">
                        <InputLabel for="week_key" value="Week Key (YYYY-WW)" />
                        <TextInput id="week_key" type="text" v-model="form.week_key" placeholder="2026-11"
                            class="mt-1 block w-full" />
                        <InputError :message="form.errors.week_key" class="mt-2" />
                    </div>

                    <div v-if="form.type === 'monthly'">
                        <InputLabel for="month_key" value="Month Key (YYYY-MM)" />
                        <TextInput id="month_key" type="month" v-model="form.month_key" class="mt-1 block w-full" />
                        <InputError :message="form.errors.month_key" class="mt-2" />
                    </div>

                    <div v-if="form.type === 'yearly'">
                        <InputLabel for="year_key" value="Year Key (YYYY)" />
                        <TextInput id="year_key" type="text" v-model="form.year_key" placeholder="2026"
                            class="mt-1 block w-full" />
                        <InputError :message="form.errors.year_key" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="colors" value="Colors" />
                        
                        <!-- Color picker with confirm button -->
                        <div class="flex items-center gap-2 mt-1">
                            <input type="color" v-model="currentColor" />
                            <button type="button" @click="addColor" class="bg-green-500 text-white rounded-full">
                                <Icon icon="mdi:check-circle-outline" width="30" height="30" />
                            </button>
                            <!-- Show selected colors -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span v-for="color in selectedColors" :key="color"
                                    class="w-6 h-6 rounded-full border cursor-pointer" :style="{ backgroundColor: color }"
                                    @click="removeColor(color)">
                                </span>
                            </div>
                        </div>

                        <!-- Hidden text field with comma-separated codes -->
                        <TextInput id="colors" type="hidden" v-model="form.colors" class="mt-1 block w-full" readonly
                            required />
                        <InputError :message="form.errors.colors" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="numbers" value="Numbers" />
                        <TextInput id="numbers" type="text" v-model="form.numbers" placeholder="e.g. 3, 7, 11"
                            class="mt-1 block w-full" required />
                        <InputError :message="form.errors.numbers" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="alphabets" value="Alphabets" />
                        <TextInput id="alphabets" type="text" v-model="form.alphabets" placeholder="e.g. A, K, M"
                            class="mt-1 block w-full" required />
                        <InputError :message="form.errors.alphabets" class="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel for="love" value="Love" />
                    <TextInput id="love" type="text" v-model="form.love" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.love" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="health" value="Health" />
                    <TextInput id="health" type="text" v-model="form.health" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.health" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="career" value="Career" />
                    <TextInput id="career" type="text" v-model="form.career" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.career" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="emotions" value="Emotions" />
                    <TextInput id="emotions" type="text" v-model="form.emotions" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.emotions" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="travel" value="Travel" />
                    <TextInput id="travel" type="text" v-model="form.travel" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.travel" class="mt-2" />
                </div>

                <!-- Description (Summernote) -->
                <div>
                    <InputLabel for="description" value="Description" />
                    <QuillEditor v-model:content="form.description" contentType="html" theme="snow"
                        placeholder="Write your content here..." style="height:300px" />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>


                <!-- Other Tips -->
                <div>
                    <InputLabel for="cosmic_tip" value="Cosmic Tip" />
                    <TextInput id="cosmic_tip" type="text" v-model="form.cosmic_tip" class="mt-1 block w-full"
                        required />
                    <InputError :message="form.errors.cosmic_tip" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="tip_for_singles" value="Tip for Singles" />
                    <TextInput id="tip_for_singles" type="text" v-model="form.tip_for_singles" class="mt-1 block w-full"
                        required />
                    <InputError :message="form.errors.tip_for_singles" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="tip_for_couples" value="Tip for Couples" />
                    <TextInput id="tip_for_couples" type="text" v-model="form.tip_for_couples" class="mt-1 block w-full"
                        required />
                    <InputError :message="form.errors.tip_for_couples" class="mt-2" />
                </div>

                <!-- Submit -->
                <div class="text-end">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="form.processing">
                        Save Horoscope
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
