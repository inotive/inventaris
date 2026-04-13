<template>
    <form class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-0.5">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Tanggal Pemesanan</label
                >
                <input
                    v-model="form.ordered_at"
                    type="date"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 placeholder:text-gray-400 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                />
                <p v-if="form.errors.ordered_at" class="text-sm text-red-500">
                    {{ form.errors.ordered_at[0] }}
                </p>
            </div>
            <div class="space-y-0.5">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Referensi PR</label
                >
                <Select
                    v-model="form.request_id"
                    :items="purchaseRequests"
                    :search-key="'request_no'"
                    :label-key="'request_no'"
                    label="Pilih purchase request"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.request_no }}
                        </span>
                    </template>
                </Select>
                <p v-if="form.errors.request_id" class="text-xs text-rose-600">
                    {{ form.errors.request_id[0] }}
                </p>
            </div>
            <div class="space-y-0.5">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Nama Vendor</label
                >
                <input
                    v-model="form.vendor"
                    type="text"
                    placeholder="Masukkan nama vendor"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 placeholder:text-gray-400 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                />
                <p v-if="form.errors.vendor" class="text-xs text-rose-600">
                    {{ form.errors.vendor[0] }}
                </p>
            </div>
            <div class="space-y-0.5">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Pemesanan oleh</label
                >
                <Select
                    v-model="form.employee_id"
                    :items="
                        props.user?.employee ? [props.user.employee] : employees
                    "
                    :disabled="!!props.user?.employee"
                    label="Pilih karyawan"
                />
                <p v-if="form.errors.employee_id" class="text-xs text-rose-600">
                    {{ form.errors.employee_id[0] }}
                </p>
            </div>
            <div class="col-span-2 space-y-0.5">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Catatan</label
                >
                <textarea
                    v-model="form.note"
                    rows="2"
                    placeholder="Masukkan catatan pemesanan barang"
                    class="w-full rounded-md text-sm border-gray-300 placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                ></textarea>
                <p v-if="form.errors.note" class="text-xs text-rose-600">
                    {{ form.errors.note[0] }}
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
    purchaseRequests: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, required: true },
});
</script>
