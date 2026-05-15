<template>
    <div class="p-6 space-y-6">

        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal Penerimaan -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Penerimaan <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.received_at"
                        type="date"
                        required
                        class="w-full h-10 px-3 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    />
                    <p v-if="form.errors.received_at" class="text-sm text-red-500">
                        {{ form.errors.received_at[0] }}
                    </p>
                </div>

                <!-- Referensi Purchase Order -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Referensi Purchase Order <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <Select
                            v-model="form.order_id"
                            :items="orders"
                            :search-key="'order_no'"
                            :label-key="'order_no'"
                            placeholder="Pilih Purchase Order"
                            class="w-full"
                            :disabled="loading"
                        >
                            <template #item="{ item }">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ item.order_no }}</span>
                                    <span class="text-sm text-gray-500">{{ item.vendor || 'N/A' }}</span>
                                </div>
                            </template>
                        </Select>
                        <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        </div>
                    </div>
                    <p v-if="form.errors.order_id" class="text-sm text-red-500">
                        {{ form.errors.order_id[0] }}
                    </p>
                </div>

                <!-- Sumber Penerimaan - Hidden -->
                <input
                    v-model="form.source"
                    type="hidden"
                    value="Pembelian"
                />

                <!-- Penerima -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Diterima oleh <span class="text-red-500">*</span>
                    </label>
                    <Select
                        v-model="selectedOrderEmployee"
                        :items="employees"
                        :search-key="'name'"
                        :label-key="'name'"
                        :disabled="!isSuperadmin"
                        placeholder="Pilih pemesanan oleh"
                        class="w-full"
                    >
                        <template #item="{ item }">
                            <span>{{ item.name }}</span>
                        </template>
                    </Select>
                    <p v-if="form.errors.employee_id" class="text-sm text-red-500">
                        {{ form.errors.employee_id[0] }}
                    </p>
                </div>
            </div>

            <!-- Catatan -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Catatan
                </label>
                <textarea
                    v-model="form.note"
                    rows="3"
                    placeholder="Masukkan catatan penerimaan barang (opsional)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                ></textarea>
                <p v-if="form.errors.note" class="text-sm text-red-500">
                    {{ form.errors.note[0] }}
                </p>
            </div>
        </form>
    </div>
</template>

<script setup>
import Select from "@/Components/form/SelectPemakaian.vue";
import { ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
    form: { type: Object, required: true },
    orders: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, default: null },
    isSuperadmin: { type: Boolean, required: true },
    isEdit: { type: Boolean, required: true },
});
const emit = defineEmits(['items-loaded']);
const loading = ref(false);
const selectedOrderEmployee = ref(null);

// Set initial employee value
if (props.isEdit && props.form.employee_id) {
    selectedOrderEmployee.value = props.form.employee_id;
} else if (props.user?.employee?.id) {
    selectedOrderEmployee.value = props.user.employee.id;
    props.form.employee_id = props.user.employee.id;
}

// Watch selectedOrderEmployee changes to update form.employee_id
watch(selectedOrderEmployee, (newValue) => {
    if (newValue) {
        // newValue is the employee ID from the select component
        props.form.employee_id = newValue;
    } else {
        props.form.employee_id = null;
    }
});

// Set source value for pembelian
props.form.source = 'Pembelian';

// Watch for order_id changes to load items and employee
watch(
    () => props.form.order_id,
    async (newOrderId) => {
        if (!newOrderId) {
            emit('items-loaded', []);
            selectedOrderEmployee.value = null;
            props.form.employee_id = null;
            return;
        }

        loading.value = true;
        try {
            // Load items
            const response = await axios.get(route('good-receipts.loadItems', newOrderId), {
                params: { type: 'pembelian' }
            });
            emit('items-loaded', response.data);

            // Find selected order and get employee info
            const selectedOrder = props.orders.find(order => order.id === newOrderId);
            if (selectedOrder) {
                // Set employee info based on available data
                if (selectedOrder.applicant) {
                    selectedOrderEmployee.value = selectedOrder.applicant.id;
                } else if (selectedOrder.ordered_by) {
                    // If employee data is not loaded, find it from employees list
                    const employee = props.employees.find(emp => emp.id === selectedOrder.ordered_by);
                    if (employee) {
                        selectedOrderEmployee.value = employee.id;
                    }
                } else if (props.user?.employee?.id) {
                    // Fallback to current user if no employee info available
                    selectedOrderEmployee.value = props.user.employee.id;
                }
            }
        } catch (error) {
            console.error('Error loading items:', error);
            emit('items-loaded', []);
            selectedOrderEmployee.value = null;
            props.form.employee_id = null;
        } finally {
            loading.value = false;
        }
    },
    { immediate: false }
);
</script>
