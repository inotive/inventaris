<template>
    <Toast />
    <div class="px-6 py-6">
        <div class="flex justify-between items-center mb-4">
            <Link
                :href="route('shift-works.index')"
                as="button"
                class="inline-flex items-center px-3 py-2 text-white bg-blue-500 rounded-md"
            >
                <i class="mr-2 lni lni-chevron-left" /> Kembali
            </Link>
        </div>
    </div>

    <div class="space-y-6">
        <form @submit.prevent="openConfirm">
            <CustomCard title="Tambah Shift Kerja">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Employee -->
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Karyawan</label
                            >
                            <Select
                                v-model="form.user_id"
                                :options="employeeOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Pilih Karyawan"
                                class="w-full !border !border-gray-300 rounded-lg"
                                :class="{
                                    'p-invalid border-red-500':
                                        form.errors.user_id,
                                }"
                                @change="form.errors.user_id = null"
                            />
                            <div
                                v-if="form.errors.user_id"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.user_id[0] }}
                            </div>
                        </div>

                        <!-- Shift -->
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Shift</label
                            >
                            <Select
                                v-model="form.shift_id"
                                :options="shiftOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Pilih Shift"
                                class="w-full !border !border-gray-300 rounded-lg"
                                :class="{
                                    'p-invalid border-red-500':
                                        form.errors.shift_id,
                                }"
                                @change="form.errors.shift_id = null"
                            />
                            <div
                                v-if="form.errors.shift_id"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.shift_id[0] }}
                            </div>
                        </div>
                    </div>

                    <!-- Work Date -->
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Tanggal Kerja</label
                        >
                        <DatePicker
                            v-model="form.work_date"
                            showIcon
                            fluid
                            iconDisplay="input"
                            class="w-full !bg-white"
                            inputClass="w-full !bg-white !border !border-gray-300 rounded-lg"
                            placeholder="Pilih tanggal kerja"
                            :class="{
                                'p-invalid border-red-500':
                                    form.errors.work_date,
                            }"
                            @change="form.errors.work_date = null"
                        />
                        <div
                            v-if="form.errors.work_date"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.work_date[0] }}
                        </div>
                    </div>
                </div>
            </CustomCard>

            <!-- Actions -->
            <div class="flex gap-2 justify-start mt-4">
                <Button
                    title="Simpan"
                    class="px-3 py-2 text-white bg-blue-500"
                    type="button"
                    @click="openConfirm"
                >
                    Simpan
                </Button>
            </div>

            <!-- Confirm Dialog -->
            <Dialog
                v-model:visible="confirmVisible"
                modal
                dismissableMask
                :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
                :style="{ width: '480px' }"
                :pt="dialogPt"
            >
                <template #header>
                    <h3 class="text-xl font-semibold text-gray-900">
                        Konfirmasi Data
                    </h3>
                </template>

                <div class="flex flex-col items-center text-center">
                    <h4 class="mb-2 text-2xl font-semibold text-gray-900">
                        Simpan Shift Kerja?
                    </h4>
                    <p class="max-w-md text-gray-600">
                        Anda akan menambahkan shift kerja untuk
                        <span class="font-semibold text-blue-600">
                            {{ selectedEmployeeName || "—" }}
                        </span>
                        pada tanggal
                        <span class="font-semibold text-blue-600">
                            {{ form.work_date || "—" }} </span
                        >. Lanjutkan?
                    </p>
                </div>

                <template #footer>
                    <div class="flex gap-3 w-full">
                        <Button
                            label="Batalkan"
                            severity="secondary"
                            class="flex-1 h-11 text-white bg-gray-300 rounded-lg"
                            @click="confirmVisible = false"
                        />
                        <Button
                            label="Ya, Simpan"
                            class="flex-1 h-11 text-white bg-blue-500 rounded-lg"
                            @click="confirmSave"
                        />
                    </div>
                </template>
            </Dialog>
        </form>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { Select, DatePicker, Dialog } from "primevue";
import Button from "primevue/button";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import CustomCard from "@/Components/common/CustomCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
    employees: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
});

const employeeOptions = props.employees.map((emp) => ({
    label: emp.name,
    value: emp.id,
}));
const shiftOptions = props.shifts.map((s) => ({
    label: s.name,
    value: s.id,
}));

const form = useForm({
    user_id: null,
    shift_id: null,
    work_date: null,
});

const selectedEmployeeName = computed(() => {
    const emp = employeeOptions.find((e) => e.value === form.user_id);
    return emp ? emp.label : "";
});

const confirmVisible = ref(false);
const openConfirm = () => (confirmVisible.value = true);

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};

const toast = useToast();

const confirmSave = () => {
    confirmVisible.value = false;
    form.post(route("shift-works.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Gagal menyimpan shift kerja.",
                life: 3000,
            });
        },
    });
};
</script>
