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
                        label="Pilih Penerima"
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

            <!-- Lampiran -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Lampiran <span class="text-gray-400 text-xs">(PDF, Gambar, Word - max 10MB)</span>
                </label>
                <div class="space-y-2">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Tambah Lampiran</span>
                        <input
                            type="file"
                            class="hidden"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            multiple
                            @change="handleAttachmentChange"
                        />
                    </label>
                    
                    <div v-if="attachmentFiles.length > 0" class="flex flex-col gap-1.5 mt-2">
                        <div v-for="(file, idx) in attachmentFiles" :key="idx" class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2 bg-gray-50 dark:bg-gray-800 p-1.5 px-3 rounded-md border border-gray-200 dark:border-gray-700 w-fit">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ file.name }}
                            <button type="button" @click="removeAttachment(idx)" class="text-red-400 hover:text-red-600 ml-2 font-bold">×</button>
                        </div>
                    </div>
                    <span v-else class="text-sm text-gray-400 block">Belum ada lampiran terpilih.</span>
                </div>
                <p v-if="form.errors.attachment" class="text-sm text-red-500">{{ form.errors.attachment }}</p>
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
    isEdit: { type: Boolean, default: false },
});

const emit = defineEmits(['items-loaded', 'attachments-changed']);

const loading = ref(false);
const selectedTransferEmployee = ref(null);
const attachmentFiles = ref([]);

// Set initial employee value
if (props.isEdit && props.form.employee_id) {
    selectedTransferEmployee.value = props.form.employee_id;
} else if (props.user?.employee?.id) {
    selectedTransferEmployee.value = props.user.employee.id;
    props.form.employee_id = props.user.employee.id;
}

// Watch selectedTransferEmployee changes to update form.employee_id
watch(selectedTransferEmployee, (newValue) => {
    if (newValue) {
        props.form.employee_id = newValue;
    } else {
        props.form.employee_id = null;
    }
});

// Set source value for pemindahan
props.form.source = 'Pemindahan';

function handleAttachmentChange(event) {
    const files = Array.from(event.target.files);
    attachmentFiles.value.push(...files);
    emit('attachments-changed', attachmentFiles.value);
}

function removeAttachment(index) {
    attachmentFiles.value.splice(index, 1);
    emit('attachments-changed', attachmentFiles.value);
}

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
            selectedTransferEmployee.value = null;
            props.form.employee_id = null;
        } finally {
            loading.value = false;
        }
    },
    { immediate: false }
);
</script>
