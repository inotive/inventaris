<template>
    <form class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-1">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Tanggal Pemindahan
                    <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.transferred_at"
                    type="date"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                />
                <p
                    v-if="form.errors.transferred_at"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.transferred_at[0] }}
                </p>
            </div>
            <div class="space-y-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Dikirim oleh
                    <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.employee_id"
                    label="Pilih karyawan"
                    :items="employees"
                    :disabled="roles !== 'Superadmin'"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.name }}
                        </span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.employee_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.employee_id[0] }}
                </p>
            </div>
            <div class="space-y-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Dari Cabang<span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.from_branch_id"
                    label="Pilih cabang"
                    :items="branches"
                    :disabled="roles !== 'Superadmin'"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.name }}
                        </span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.from_branch_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.from_branch_id[0] }}
                </p>
            </div>
            <div class="space-y-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Ke Cabang
                    <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.to_branch_id"
                    label="Pilih cabang"
                    :items="toBranches"
                    :disabled="isFromGudangPusat"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.name }}
                        </span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.to_branch_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.to_branch_id[0] }}
                </p>
            </div>
            <div class="col-span-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keperluan</label>
                <textarea
                    v-model="form.purpose"
                    rows="2"
                    placeholder="Masukkan keperluan pemindahan barang"
                    class="mt-1 w-full rounded-md text-sm border-gray-300 placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                ></textarea>
                <p v-if="form.errors.purpose" class="mt-1 text-xs text-rose-600">{{ form.errors.purpose[0] }}</p>
            </div>

            <!-- Lampiran Pengiriman -->
            <div class="col-span-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Lampiran Pengiriman
                    <span class="text-gray-400 text-xs">(PDF, Gambar, Word - max 10MB)</span>
                </label>
                <div class="space-y-2 mt-1">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Tambah Lampiran</span>
                        <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple @change="handleAttachmentChange" />
                    </label>
                    
                    <div v-if="attachmentFiles.length > 0" class="flex flex-col gap-1.5 mt-2">
                        <div v-for="(file, idx) in attachmentFiles" :key="idx" class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2 bg-gray-50 dark:bg-gray-800 p-1.5 px-3 rounded-md border border-gray-200 dark:border-gray-700 w-fit">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ file.name }}
                            <button type="button" @click="removeAttachment(idx)" class="text-red-400 hover:text-red-600 ml-2 font-bold">&times;</button>
                        </div>
                    </div>
                    <span v-else class="text-sm text-gray-400 block">Belum ada lampiran terpilih.</span>
                </div>
                <p v-if="form.errors.attachment" class="mt-1 text-xs text-rose-600">{{ form.errors.attachment }}</p>
            </div>
        </div>
    </form>
</template>

<script setup>
import Input from "@/Components/form/Input.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import { computed, ref, watch } from "vue";

const props = defineProps({
    form: { type: Object, required: true },
    branches: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: String, required: true },
    roles: { type: String, required: true },
});

const emit = defineEmits(['attachments-changed']);
const attachmentFiles = ref([]);

function handleAttachmentChange(event) {
    const files = Array.from(event.target.files);
    attachmentFiles.value.push(...files);
    emit('attachments-changed', attachmentFiles.value);
}

function removeAttachment(index) {
    attachmentFiles.value.splice(index, 1);
    emit('attachments-changed', attachmentFiles.value);
}

// Find the branch object of From Branch
const fromBranchObj = computed(() => {
    return props.branches.find(b => b.id == props.form.from_branch_id);
});

// Check if From Branch is Gudang Pusat
const isFromGudangPusat = computed(() => {
    return fromBranchObj.value?.name?.toLowerCase() === 'gudang pusat';
});

// Find Gudang Utama Cabang Sangatta ID
const gudangSangattaId = computed(() => {
    const sangatta = props.branches.find(b => b.name?.toLowerCase() === 'gudang utama cabang sangatta');
    return sangatta?.id ?? null;
});

// Filter to-branches to exclude same branch, and enforce Sangatta only if source is Gudang Pusat
const toBranches = computed(() => {
    if (isFromGudangPusat.value) {
        return props.branches.filter(b => b.name?.toLowerCase() === 'gudang utama cabang sangatta');
    }
    return props.branches.filter(b => b.id != props.form.from_branch_id);
});

// Lock and watch to automatically populate when from_branch_id becomes Gudang Pusat
watch(
    () => props.form.from_branch_id,
    (newVal) => {
        if (isFromGudangPusat.value && gudangSangattaId.value) {
            props.form.to_branch_id = gudangSangattaId.value;
        }
    },
    { immediate: true }
);

console.log(props.form)
</script>
