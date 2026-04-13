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
                    :items="branches"
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
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Keperluan</label
                >
                <textarea
                    v-model="form.purpose"
                    rows="2"
                    placeholder="Masukkan keperluan pemindahan barang"
                    class="mt-1 w-full rounded-md text-sm border-gray-300 placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                ></textarea>
                <p
                    v-if="form.errors.purpose"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.purpose[0] }}
                </p>
            </div>
        </div>
    </form>
</template>

<script setup>
import Input from "@/Components/form/Input.vue";
import Select from "@/Components/form/SelectPemakaian.vue";

const props = defineProps({
    form: { type: Object, required: true },
    branches: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: String, required: true },
    roles: { type: String, required: true },
});

console.log(props.form)
</script>
