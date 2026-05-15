<template>
    <div class="space-y-4">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal PO</label>
                <input
                    v-model="form.ordered_at"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    required
                />
                <div v-if="form.errors.ordered_at" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.ordered_at[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vendor</label>
                <input
                    v-model="form.vendor"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="Masukkan nama vendor"
                    required
                />
                <div v-if="form.errors.vendor" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.vendor[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referensi PR (Opsional)</label>
                <select
                    v-model="form.request_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Pilih Purchase Request</option>
                    <option v-for="pr in purchaseRequests" :key="pr.id" :value="pr.id">
                        {{ pr.request_no }} - {{ pr.requirement }}
                    </option>
                </select>
                <div class="text-xs text-gray-500 mt-1">
                    Memilih PR akan mengisi daftar item secara otomatis
                </div>
                <div v-if="form.errors.request_id" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.request_id[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dibuat oleh</label>
                <Select
                    v-model="form.employee_id"
                    :items="employees"
                    label="Pilih Karyawan"
                    label-key="name"
                    search-key="name"
                    class="w-full"
                    :disabled="!isSuperadmin"
                />
                <div v-if="form.errors.created_by" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.created_by[0] }}
                </div>
            </div>
            <div v-if="isSuperadmin">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cabang <span class="text-red-500">*</span></label>
                <Select
                    v-model="form.branch_id"
                    :items="branches"
                    label="Pilih Cabang"
                    label-key="name"
                    search-key="name"
                    class="w-full"
                    :disabled="!isSuperadmin"
                />
                <div v-if="form.errors.branch_id" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.branch_id[0] }}
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
            <textarea
                v-model="form.note"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Catatan untuk vendor"
            ></textarea>
            <div v-if="form.errors.note" class="text-xs text-rose-600 mt-1">
                {{ form.errors.note[0] }}
            </div>
        </div>
    </div>
</template>

<script setup>
import Select from "@/Components/form/SelectPemakaian.vue";

defineProps({
    form: { type: Object, required: true },
    purchaseRequests: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    user: { type: Object, required: true },
    isSuperadmin: { type: Boolean, required: true },
});

defineEmits(['cancel']);
</script>
