<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    employees: Array,
    shifts: Array,
});

const form = useForm({
    user_id: "",
    shift_id: "",
    work_date: "",
});

const submit = () => {
    if (form.work_date) {
        const date = new Date(form.work_date);
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        form.work_date = `${yyyy}-${mm}-${dd}`;
    }

    form.post(route("shift-works.store"));
};
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="border-b border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <h1 class="text-2xl font-semibold text-gray-800">
                    Tambah Shift Kerja
                </h1>
                <!-- breadcrumb opsional -->
                <!-- <p class="text-sm text-gray-500">Dashboard / Shift / Tambah</p> -->
            </div>
        </div>

        <!-- Page Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Employee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Karyawan
                        </label>
                        <select
                            v-model="form.user_id"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Karyawan --</option>
                            <option
                                v-for="emp in employees"
                                :key="emp.id"
                                :value="emp.id"
                            >
                                {{ emp.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.user_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.user_id }}
                        </p>
                    </div>

                    <!-- Shift -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Shift
                        </label>
                        <select
                            v-model="form.shift_id"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Shift --</option>
                            <option
                                v-for="s in shifts"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.shift_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.shift_id }}
                        </p>
                    </div>

                    <!-- Work Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Tanggal Kerja
                        </label>
                        <input
                            type="date"
                            v-model="form.work_date"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <p
                            v-if="form.errors.work_date"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.work_date }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                            @click="$inertia.visit(route('shift-works.index'))"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
