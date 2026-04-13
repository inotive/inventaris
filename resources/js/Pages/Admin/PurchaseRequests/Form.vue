<template>
    <form class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Tanggal Pengajuan</label
                >
                <input
                    v-model="form.requested_at"
                    type="date"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                />
                <p
                    v-if="form.errors.requested_at"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ form.errors.requested_at[0] }}
                </p>
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Pengajuan oleh</label
                >
                <Select
                    v-model="form.employee_id"
                    :items="employees"
                    :search-key="'name'"
                    :label-key="'name'"
                    label="Pilih karyawan"
                    class="mt-2"
                    :disabled="!isSuperadmin"
                >
                    <template #item="{ item }">
                        <span>{{ item.name }}</span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.employee_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.employee_id[0] }}
                </p>
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Referensi MR</label>
                <Select
                    v-model="form.request_id"
                    :items="[{ id: null, request_no: 'Tanpa Referensi' }, ...materialRequests]"
                    :search-key="'request_no'"
                    :label-key="'request_no'"
                    label="Pilih material request"
                    class="mt-2"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.request_no }}
                        </span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.request_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.request_id[0] }}
                </p>
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Departemen</label
                >
                <Select
                    v-model="form.department_id"
                    :items="departments"
                    :search-key="'name'"
                    :label-key="'name'"
                    label="Pilih departemen"
                    class="mt-2"
                    :disabled="!isSuperadmin"
                >
                    <template #item="{ item }">
                        <span>
                            {{ item.name
                            }}{{ item.branch ? " - " + item.branch.name : "" }}
                        </span>
                    </template>
                </Select>
                <p
                    v-if="form.errors.department_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.department_id[0] }}
                </p>
            </div>
            <div class="col-span-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Keperluan</label
                >
                <textarea
                    v-model="form.requirement"
                    rows="2"
                    placeholder="Masukkan keperluan pengajuan barang"
                    class="mt-1 w-full rounded-md text-sm border-gray-300 placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                ></textarea>
                <p
                    v-if="form.errors.requirement"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.requirement[0] }}
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
    materialRequests: { type: Array, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, default: null },
    isSuperadmin: { type: Boolean, required: true },
});

console.log('form:', props.form);
</script>
