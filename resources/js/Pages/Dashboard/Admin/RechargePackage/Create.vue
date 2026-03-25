<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';



const showAddPackage = ref(false)
const alertRef = inject('alertRef')

const form = useForm({
    amount: "",
    bonus_amount: "",
    label: "",
    recommended: "",
    type: "",
})

const openAddPackage = () => {
    showAddPackage.value = true
}

// Submit handler
function submitForm() {
    form.post(route("admin.recharge-packages.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showAddPackage.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Package added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button @click="openAddPackage"
        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 font-bold flex items-center">
        <Icon icon="mdi-plus" width="20" height="20" class="mr-1" /> Add Package
    </button>

    <!-- Add Blog Modal -->
    <Modal :show="showAddPackage" @close="showAddPackage = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Add Package</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div class="mb-4">
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" v-model="form.amount" type="number" class="w-full" required />
                    <InputError :message="form.errors.amount" />
                </div>
    
                <!-- Bonus Amount -->
                <div class="mb-4">
                    <InputLabel for="bonus_amount" value="Bonus Amount" />
                    <TextInput id="bonus_amount" v-model="form.bonus_amount" type="number" class="w-full" />
                    <InputError :message="form.errors.bonus_amount" />
                </div>
    
                <!-- Label -->
                <div class="mb-4">
                    <InputLabel for="label" value="Label" />
                    <TextInput id="label" v-model="form.label" type="text" class="w-full" />
                    <InputError :message="form.errors.label" />
                </div>
    
                <!-- Type (select) -->
                <div class="mb-4">
                    <InputLabel for="type" value="Type" />
                    <select v-model="form.type" class="w-full border rounded px-3 py-2">
                        <option value="" selected disabled>Select Type</option>
                        <option value="first_time">First Time</option>
                        <option value="regular">Regular</option>
                        <option value="special">Special</option>
                    </select>
                    <InputError :message="form.errors.type" />
                </div>

                <!-- Recommended (checkbox) -->
                <div class="mb-4 flex items-center gap-4">
                    <InputLabel for="recommended" value="Recommended" />
                    <input v-model="form.recommended" type="checkbox" class="mr-2 rounded border-gray-300" />
                    <InputError :message="form.errors.recommended" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <button type="button" @click="showAddPackage = false"
                    class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" @click="submitForm"
                    class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
            </div>
        </div>
    </Modal>

</template>