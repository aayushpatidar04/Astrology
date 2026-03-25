<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { Icon } from '@iconify/vue';



const showEditPackage = ref(false)
const alertRef = inject('alertRef')

const props = defineProps({
    recharge: Object
})



const form = useForm({
    amount: "",
    bonus_amount: "",
    label: "",
    recommended: "",
    type: "",
})

const openEditPackage = (recharge) => {
    form.amount = recharge.amount,
        form.bonus_amount = recharge.bonus_amount,
        form.label = recharge.label,
        form.type = recharge.type,
        form.recommended = !!recharge.recommended,
        showEditPackage.value = true
}

// Submit handler
function submitForm() {
    form.post(route("admin.recharge-packages.edit", { id: props.recharge.id }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEditPackage.value = false
            form.clearErrors()
            form.reset()
            alertRef.value.showAlert('Package updated successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
    })
}

</script>

<template>
    <button @click="openEditPackage(props.recharge)" class="text-yellow-500 text-2xl">
        <Icon icon="mdi:pencil"></Icon>
    </button>

    <!-- Add Blog Modal -->
    <Modal :show="showEditPackage" @close="showEditPackage = false">
        <div class="p-6">
            <h3 class="text-xl font-semibold mb-4">Edit Package</h3>

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
                <button type="button" @click="showEditPackage = false"
                    class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="button" @click="submitForm"
                    class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
            </div>
        </div>
    </Modal>

</template>