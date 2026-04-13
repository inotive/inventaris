<template>

    <Head title="Tambah Checklist" />
    <Toast />

    <div class="flex overflow-hidden flex-col gap-3 px-3 py-0.5 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <!-- Informasi Checklist -->
        <div class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200">
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    Informasi Checklist
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label for="name" class="block mb-1 text-sm text-gray-600">Nama Checklist</label>
                        <input id="name"
                            class="px-3 py-2 w-full text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': validationErrors.name || form.errors.name }" type="text"
                            v-model="form.name" placeholder="Masukkan nama checklist" required />
                        <p v-if="validationErrors.name || form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ validationErrors.name || form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label for="sop_code" class="block mb-1 text-sm text-gray-600">Nomor SOP</label>
                        <input id="sop_code"
                            class="px-3 py-2 w-full text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': validationErrors.sop_code || form.errors.sop_code }" type="text"
                            v-model="form.sop_code" placeholder="Masukkan kode SOP" required />
                        <p v-if="validationErrors.sop_code || form.errors.sop_code" class="mt-1 text-xs text-red-500">
                            {{ validationErrors.sop_code || form.errors.sop_code }}
                        </p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600">Kategori Checklist</label>
                        <Select v-model="form.category_id" label="Pilih Kategori" :items="categories" :taggable="true"
                            :class="{ 'border-red-500': validationErrors.category_id || form.errors.category_id }" />
                        <p v-if="validationErrors.category_id || form.errors.category_id"
                            class="mt-1 text-xs text-red-500">
                            {{ validationErrors.category_id || form.errors.category_id }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mt-2 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="description" class="block mb-1 text-sm text-gray-600">Deskripsi</label>
                        <textarea id="description" v-model="form.description" rows="3"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400"
                            placeholder="Masukkan deskripsi checklist"></textarea>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600">Departemen</label>
                        <SelectMultiple v-model="form.department_ids" label="Pilih Departemen"
                            :items="filteredDepartments"
                            :class="{ 'border-red-500': validationErrors.department_ids || form.errors.department_ids }">
                            <template #item="{ item }">
                                <span class="text-gray-900 dark:text-gray-100">{{ item.name }}</span>
                                <span v-if="item.branch?.name || item.employees_count !== undefined"
                                    class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span v-if="item.branch?.name">({{ item.branch.name }}</span>
                                    <span v-if="item.branch?.name && item.employees_count !== undefined"> - </span>
                                    <span v-if="item.employees_count !== undefined">{{ item.employees_count || 0 }}
                                        karyawan</span>
                                    <span v-if="item.branch?.name">)</span>
                                    <span v-if="!item.branch?.name && item.employees_count !== undefined">({{
                                        item.employees_count || 0 }} karyawan)</span>
                                </span>
                            </template>
                        </SelectMultiple>
                        <p v-if="validationErrors.department_ids || form.errors.department_ids"
                            class="mt-1 text-xs text-red-500">
                            {{ validationErrors.department_ids || form.errors.department_ids }}
                        </p>
                    </div>
                    <div>
                        <label for="type" class="block mb-1 text-sm text-gray-600">Jenis</label>
                        <select id="type" v-model="form.type"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400"
                            :class="{ 'border-red-500': form.errors.type }" required>
                            <option value="single">Perorang</option>
                            <option value="multiple">Banyak Orang</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-red-500">
                            {{ form.errors.type }}
                        </p>
                    </div>
                    <div>
                        <label for="status" class="block mb-1 text-sm text-gray-600">Status</label>
                        <select id="status" v-model="form.status"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400"
                            :class="{ 'border-red-500': form.errors.status }" required>
                            <option value="Draft">Draft</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">
                            {{ form.errors.status }}
                        </p>
                    </div>
                </div>

                <!-- Pengaturan Reminder -->
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Pengaturan Reminder Otomatis</h3>
                    
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="reminder_enabled" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input id="reminder_enabled" type="checkbox" v-model="form.reminder_enabled"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" />
                                Aktifkan Reminder Otomatis
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Kirim notifikasi reminder secara otomatis ke karyawan</p>
                        </div>
                    </div>

                    <div v-if="form.reminder_enabled" class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
                        <div>
                            <label for="reminder_time" class="block mb-1 text-sm text-gray-600">Jam Pengiriman Reminder (WITA)</label>
                            <input id="reminder_time"
                                class="px-3 py-2 w-full text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.reminder_time }" type="time" v-model="form.reminder_time"
                                required />
                            <p v-if="form.errors.reminder_time" class="mt-1 text-xs text-red-500">
                                {{ form.errors.reminder_time }}
                            </p>
                        </div>

                        <div>
                            <label for="reminder_frequency" class="block mb-1 text-sm text-gray-600">Frekuensi Pengiriman</label>
                            <select id="reminder_frequency" v-model="form.reminder_frequency"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400"
                                :class="{ 'border-red-500': form.errors.reminder_frequency }" required>
                                <option value="daily">Setiap Hari</option>
                                <option value="weekly">Setiap Minggu</option>
                                <option value="monthly">Setiap Bulan</option>
                            </select>
                            <p v-if="form.errors.reminder_frequency" class="mt-1 text-xs text-red-500">
                                {{ form.errors.reminder_frequency }}
                            </p>
                        </div>

                        <div v-if="form.reminder_frequency === 'weekly'" class="md:col-span-2">
                            <label class="block mb-2 text-sm text-gray-600">Hari Pengiriman Reminder</label>
                            <div class="flex flex-wrap gap-3">
                                <label v-for="day in weekDays" :key="day.value" 
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" :value="day.value" v-model="form.reminder_days"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700">{{ day.label }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.reminder_days" class="mt-1 text-xs text-red-500">
                                {{ form.errors.reminder_days }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pertanyaan -->
        <div class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200">
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    Daftar Pertanyaan
                </h2>

                <div class="space-y-4">
                    <div v-for="(q, i) in questions" :key="q.uid"
                        class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                        <div class="space-y-5">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-1">
                                    <div class="flex gap-3 items-center md:flex-col md:items-start">
                                        <div class="flex gap-2 items-center">
                                            <div
                                                class="flex justify-center items-center w-8 h-8 text-sm font-medium text-gray-700 bg-gray-100 rounded-full border">
                                                {{ i + 1 }}
                                            </div>
                                            <label class="text-sm font-medium text-gray-700 md:hidden">Pertanyaan {{ i +
                                                1
                                                }}</label>
                                        </div>
                                        <label class="hidden text-sm font-medium text-gray-700 md:block">Pertanyaan {{ i
                                            + 1
                                            }}</label>
                                        <button type="button" @click="removeQuestion(i)"
                                            class="px-2 py-1 text-xs text-red-600 rounded border border-red-300 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <div class="col-span-12 space-y-4 md:col-span-11">
                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                                        <div class="lg:col-span-8">
                                            <div class="relative z-0 w-full group">
                                                <input v-model="q.title" type="text"
                                                    class="block px-0 py-2.5 w-full text-sm placeholder-transparent text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 focus:border-blue-600 focus:outline-none"
                                                    placeholder="Masukkan pertanyaan" />
                                                <label
                                                    class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500">Pertanyaan</label>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-4">
                                            <label class="inline-flex gap-2 items-center text-gray-800 cursor-pointer">
                                                <input type="checkbox" v-model="q.required" class="sr-only peer" />
                                                <div
                                                    class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-sky-600">
                                                </div>
                                                Wajib?
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                                        <div class="lg:col-span-6">
                                            <div class="relative z-0 w-full group">
                                                <input v-model="q.description" type="text"
                                                    class="block px-0 py-2.5 w-full text-sm placeholder-transparent text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 focus:border-blue-600 focus:outline-none"
                                                    placeholder="Masukkan petunjuk pertanyaan" />
                                                <label
                                                    class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500">Petunjuk
                                                    Pertanyaan</label>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-3">
                                            <Select v-model="q.category" :items="questionCategories"
                                                label="Pilih Kategori" class="w-full" :taggable="true" />
                                        </div>
                                        <div class="lg:col-span-3">
                                            <select v-model="q.answer_type" @change="onAnswerTypeChange(q)"
                                                class="p-2 w-full text-gray-700 bg-white rounded-lg border border-gray-300">
                                                <option disabled value="">
                                                    Pilih Tipe
                                                </option>
                                                <option value="text">
                                                    Text
                                                </option>
                                                <option value="textarea">
                                                    Textarea
                                                </option>
                                                <option value="select">
                                                    Select
                                                </option>
                                                <option value="radio">
                                                    Radio
                                                </option>
                                                <option value="checkbox">
                                                    Checkbox
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div v-if="
                                        [
                                            'select',
                                            'radio',
                                            'checkbox',
                                        ].includes(q.answer_type)
                                    " class="mt-4">
                                        <div class="mb-2">
                                            <h4 class="text-sm font-medium text-gray-700">
                                                Opsi Jawaban
                                            </h4>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm rounded border border-gray-200">
                                                <thead>
                                                    <tr class="bg-gray-50">
                                                        <th class="p-3 text-left border-b border-gray-200">
                                                            Label
                                                        </th>
                                                        <th class="p-3 text-left border-b border-gray-200">
                                                            Value
                                                        </th>
                                                        <th class="p-3 w-20 text-center border-b border-gray-200">
                                                            Aksi
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(
opt, idx
                                                        ) in q.options" :key="opt.uid || idx" class="hover:bg-gray-50">
                                                        <td class="p-3 border-b border-gray-100">
                                                            <input v-model="opt.label
                                                                " type="text"
                                                                class="p-2 w-full text-sm rounded border border-gray-300"
                                                                placeholder="Label opsi" />
                                                        </td>
                                                        <td class="p-3 border-b border-gray-100">
                                                            <input v-model="opt.value
                                                                " type="text"
                                                                class="p-2 w-full text-sm rounded border border-gray-300"
                                                                placeholder="Value opsi" />
                                                        </td>
                                                        <td class="p-3 text-center border-b border-gray-100">
                                                            <button type="button" @click="
                                                                removeOption(
                                                                    q,
                                                                    idx
                                                                )
                                                                " class="text-red-600 hover:text-red-800">
                                                                Hapus
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <button type="button" @click="addOption(q)"
                                                class="px-4 py-2 mt-3 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                                + Tambah Opsi
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 justify-end mt-4">
                    <button @click="addQuestion" class="px-4 py-2 text-white bg-green-500 rounded">
                        <i class="mr-2 lni lni-plus"></i>Tambah Pertanyaan
                    </button>

                    <button @click="goBack"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Batal
                    </button>
                    <button @click="submit" :disabled="form.processing"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Select from "@/Components/form/SelectKategoriCheklist.vue";
import SelectMultiple from "@/Components/form/SelectMultiple.vue";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import { Head } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { v4 as uuidv4 } from "uuid";

defineOptions({ layout: AppLayout });

const props = defineProps({
    categories: Array,
    departments: Array,
    branches: Array,
    questionCategories: Array,
});

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Checklists", href: route("checklists.index") },
    { label: "Tambah Checklist" },
];

const toast = useToast();

const weekDays = [
    { label: 'Senin', value: 1 },
    { label: 'Selasa', value: 2 },
    { label: 'Rabu', value: 3 },
    { label: 'Kamis', value: 4 },
    { label: 'Jumat', value: 5 },
    { label: 'Sabtu', value: 6 },
    { label: 'Minggu', value: 0 },
];

const form = useForm({
    name: "",
    sop_code: "",
    category_id: null,
    department_ids: [],
    branch_id: null,
    status: "Draft",
    description: "",
    type: "single",
    durasi: 1,
    count: 1,
    reminder_time: "15:20",
    reminder_frequency: "daily",
    reminder_days: [1, 2, 3, 4, 5], // Default: Senin-Jumat
    reminder_enabled: true,
    questions: [],
});

// State untuk menyimpan error validasi frontend
const validationErrors = ref({});

// Departemen yang ditampilkan, dependen pada cabang
// Initialize with all departments from props
const filteredDepartments = ref(
    (props.departments || []).map(dept => ({
        id: dept.id || dept,
        name: dept.name || dept,
        branch: dept.branch ? {
            id: dept.branch.id,
            name: dept.branch.name,
        } : null,
        employees_count: dept.employees_count || 0,
    }))
);

// Saat cabang berubah, reset departemen dan ambil daftar departemen dari API
watch(
    () => form.branch_id,
    async (newBranchId) => {
        form.department_ids = [];
        filteredDepartments.value = [];

        if (!newBranchId) {
            // If no branch selected, show all departments
            filteredDepartments.value = (props.departments || []).map(dept => ({
                id: dept.id || dept,
                name: dept.name || dept,
                branch: dept.branch ? {
                    id: dept.branch.id,
                    name: dept.branch.name,
                } : null,
                employees_count: dept.employees_count || 0,
            }));
            return;
        }

        try {
            const url = route("attendance-recap.departments", { branch_id: newBranchId });
            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                console.error("Failed to load departments for branch", newBranchId);
                return;
            }

            const data = await response.json();
            filteredDepartments.value = Array.isArray(data.departments)
                ? data.departments.map(dept => ({
                    id: dept.id,
                    name: dept.name,
                    branch: dept.branch ? {
                        id: dept.branch.id,
                        name: dept.branch.name,
                    } : (newBranchId ? {
                        id: newBranchId,
                        name: props.branches?.find(b => b.id === newBranchId)?.name || '',
                    } : null),
                    employees_count: dept.employees_count || 0,
                }))
                : [];
        } catch (e) {
            console.error("Error fetching departments for branch", newBranchId, e);
        }
    },
    { immediate: true }
);

const questions = ref([
    {
        uid: uuidv4(),
        title: "",
        description: "",
        answer_type: "text",
        required: false,
        category: null,
        options: [],
    },
]);

function addQuestion() {
    questions.value.push({
        uid: uuidv4(),
        title: "",
        description: "",
        answer_type: "text",
        required: false,
        category: null,
        options: [],
    });
}

function onAnswerTypeChange(question) {
    if (!["select", "radio", "checkbox"].includes(question.answer_type)) {
        question.options = [];
    }
}

function removeQuestion(index) {
    questions.value.splice(index, 1);
}

function addOption(question) {
    question.options.push({ uid: uuidv4(), label: "", value: "" });
}

function removeOption(question, index) {
    question.options.splice(index, 1);
}

function goBack() {
    router.visit(route("checklists.index"));
}

function submit() {
    // Reset validation errors
    validationErrors.value = {};

    // Validasi frontend sebelum submit
    let hasError = false;

    if (!form.name) {
        validationErrors.value.name = "Nama Checklist wajib diisi.";
        hasError = true;
    }

    if (!form.sop_code) {
        validationErrors.value.sop_code = "Nomor SOP wajib diisi.";
        hasError = true;
    }

    if (!form.category_id) {
        validationErrors.value.category_id = "Kategori Checklist wajib dipilih.";
        hasError = true;
    }

    if (!form.department_ids || form.department_ids.length === 0) {
        validationErrors.value.department_ids = "Minimal satu departemen wajib dipilih.";
        hasError = true;
    }

    // Jika ada error, tampilkan toast dan hentikan submit
    if (hasError) {
        toast.add({
            severity: "error",
            summary: "Validasi Error",
            detail: "Mohon lengkapi semua field yang wajib diisi.",
            life: 3000,
        });
        return;
    }

    // Prepare questions data
    form.questions = questions.value.map((q) => ({
        title: q.title,
        description: q.description,
        answer_type: q.answer_type,
        required: q.required,
        category: q.category,
        options: q.options.map((o) => ({
            label: o.label,
            value: o.value,
        })),
    }));

    // Ensure department_ids is an array of IDs
    if (Array.isArray(form.department_ids)) {
        form.department_ids = form.department_ids.map(id =>
            typeof id === 'object' ? id.id : id
        );
    }

    // Submit to backend
    form.post(route("checklists.store"), {
        onSuccess: () => {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: "Checklist berhasil disimpan.",
                life: 3000,
            });
            router.visit(route("checklists.index"));
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);

            // Tampilkan error pertama yang ditemukan
            const firstError = Object.values(errors)[0];
            toast.add({
                severity: "error",
                summary: "Error",
                detail: firstError || "Terjadi kesalahan saat menyimpan checklist.",
                life: 5000,
            });
        },
    });
}
</script>
