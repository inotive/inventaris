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

                <!-- Referensi Transfer -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Referensi Transfer <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <Select
                            v-model="form.transfer_id"
                            :items="transfers"
                            :search-key="'transfer_no'"
                            :label-key="'transfer_no'"
                            placeholder="Pilih Transfer"
                            class="w-full"
                            :disabled="loading"
                        >
                            <template #item="{ item }">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ item.transfer_no }}</span>
                                    <span class="text-sm text-gray-500">{{ item.from_branch || 'N/A' }} → {{ item.to_branch || 'N/A' }}</span>
                                </div>
                            </template>
                        </Select>
                        <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        </div>
                    </div>
                    <p v-if="form.errors.transfer_id" class="text-sm text-red-500">
                        {{ form.errors.transfer_id[0] }}
                    </p>
                </div>

                <!-- Sumber Penerimaan - Hidden -->
                <input
                    v-model="form.source"
                    type="hidden"
                    value="Pemindahan"
                />

                <!-- Penerima -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Diterima oleh <span class="text-red-500">*</span>
                    </label>
                    <Select
                        v-model="selectedTransferEmployee"
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
    transfers: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, default: null },
    isSuperadmin: { type: Boolean, required: true },
});

const emit = defineEmits(['items-loaded']);

const loading = ref(false);
const selectedTransferEmployee = props.user?.employee?.name;

// Set source value for pemindahan
props.form.source = 'Pemindahan';

// Watch for transfer_id changes to load items and employee
watch(
    () => props.form.transfer_id,
    async (newTransferId) => {
        if (!newTransferId) {
            emit('items-loaded', []);
            props.form.employee_id = null;
            return;
        }

        loading.value = true;
        try {
            // Load items
            const response = await axios.get(route('good-receipts.loadItems', newTransferId), {
                params: { type: 'pemindahan' }
            });
            emit('items-loaded', response.data);

        } catch (error) {
            console.error('Error loading items:', error);
            emit('items-loaded', []);
            selectedTransferEmployee.value = '';
            props.form.employee_id = null;
        } finally {
            loading.value = false;
        }
    },
    { immediate: false }
);
</script>
