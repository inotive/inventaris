<template>
    <div class="space-y-4">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor PO</label>
                <input
                    v-model="form.order_no"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white font-mono"
                    placeholder="Auto-generate jika dikosongkan"
                />
                <div class="text-xs text-gray-400 mt-1">Kosongkan untuk generate otomatis (PO-SPB-001-06-2026)</div>
                <div v-if="form.errors.order_no" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.order_no[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal PO</label>
                <DatePicker
                    v-model="form.ordered_at"
                    dateFormat="dd MM yy"
                    showIcon
                    fluid
                    iconDisplay="input"
                    class="w-full !bg-white"
                    inputClass="w-full !bg-white px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="Pilih tanggal"
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
                        {{ pr.request_no }}{{ pr.requirement ? ' - ' + pr.requirement : '' }}
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diskon</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                    <input
                        v-model.number="form.discount"
                        type="number"
                        min="0"
                        step="1"
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="0"
                    />
                </div>
                <div v-if="form.errors.discount" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.discount[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Biaya Lain-Lain</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                    <input
                        v-model.number="form.other_cost"
                        type="number"
                        min="0"
                        step="1"
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="0"
                    />
                </div>
                <div v-if="form.errors.other_cost" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.other_cost[0] }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PPN (%)</label>
                <div class="relative">
                    <input
                        v-model.number="form.tax_percent"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white font-mono"
                        placeholder="11"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">%</span>
                </div>
                <div v-if="form.errors.tax_percent" class="text-xs text-rose-600 mt-1">
                    {{ form.errors.tax_percent[0] }}
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
import { DatePicker } from "primevue";

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
