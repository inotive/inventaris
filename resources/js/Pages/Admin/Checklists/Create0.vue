<template>
    <Head title="Tambah Checklist" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 py-0.5 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <!-- Informasi Checklist -->
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    Informasi Checklist
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            for="name"
                            class="block mb-1 text-sm text-gray-600"
                            >Nama Checklist</label
                        >
                        <input
                            id="name"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="Masukkan nama checklist"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.name[0] }}
                        </div>
                    </div>
                    <div>
                        <label
                            for="sop_code"
                            class="block mb-1 text-sm text-gray-600"
                            >Nomor SOP</label
                        >
                        <input
                            id="sop_code"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            type="text"
                            v-model="form.sop_code"
                            required
                            placeholder="Masukkan kode SOP"
                        />
                        <div
                            v-if="form.errors.sop_code"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.sop_code[0] }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Kategori Checklist</label
                        >
                        <!-- Dropdown menu -->
                        <Select
                            v-model="form.category_id"
                            label="Pilih Kategori"
                            :items="categories"
                            :taggable="true"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Cabang</label
                        >
                        <Select
                            v-model="form.branch_id"
                            label="Pilih Cabang"
                            :items="branches"
                        />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Departemen</label
                        >
                        <Select
                            v-model="form.department_id"
                            label="Pilih Departemen"
                            :items="departments"
                        />
                    </div>
                </div>

                <!-- Deskripsi & Jenis -->
                <div class="grid grid-cols-1 gap-4 mt-2 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label
                            for="description"
                            class="block mb-1 text-sm text-gray-600"
                            >Deskripsi</label
                        >
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            placeholder="Masukkan deskripsi checklist"
                        ></textarea>
                    </div>
                    <div>
                        <label
                            for="type"
                            class="block mb-1 text-sm text-gray-600"
                            >Jenis</label
                        >
                        <select
                            id="type"
                            v-model="form.type"
                            class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        >
                            <option value="single">Perorang</option>
                            <option value="multiple">Banyak Orang</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    Daftar Pertanyaan
                </h2>
                <div class="space-y-4">
                    <div
                        v-for="(q, i) in questions"
                        :key="q.uid"
                        class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm"
                    >
                        <div class="space-y-5">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-1">
                                    <div
                                        class="flex gap-3 items-center md:flex-col md:items-start"
                                    >
                                        <div class="flex gap-2 items-center">
                                            <div
                                                class="flex justify-center items-center w-8 h-8 text-sm font-medium text-gray-700 bg-gray-100 rounded-full border"
                                            >
                                                {{ i + 1 }}
                                            </div>
                                            <label
                                                class="text-sm font-medium text-gray-700 md:hidden"
                                            >
                                                Pertanyaan {{ i + 1 }}
                                            </label>
                                        </div>
                                        <label
                                            class="hidden text-sm font-medium text-gray-700 md:block"
                                        >
                                            Pertanyaan {{ i + 1 }}
                                        </label>
                                        <button
                                            type="button"
                                            @click="removeQuestion(i)"
                                            class="px-2 py-1 text-xs text-red-600 rounded border border-red-300 hover:bg-red-50"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="col-span-12 space-y-4 md:col-span-11"
                                >
                                    <div
                                        class="grid grid-cols-1 gap-4 lg:grid-cols-12"
                                    >
                                        <div class="lg:col-span-8">
                                            <div
                                                class="relative z-0 w-full group"
                                            >
                                                <input
                                                    v-model="q.title"
                                                    type="text"
                                                    class="block px-0 py-2.5 w-full text-sm placeholder-transparent placeholder-gray-300 text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none peer focus:border-blue-600 focus:placeholder-gray-400 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
                                                    placeholder="Masukkan pertanyaan"
                                                />
                                                <label
                                                    class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-600 rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-500"
                                                >
                                                    Pertanyaan
                                                </label>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-4">
                                            <label
                                                class="inline-flex gap-2 items-center text-gray-800 cursor-pointer"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="q.required"
                                                    class="sr-only peer"
                                                />
                                                <div
                                                    class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-sky-300 dark:peer-focus:ring-sky-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-sky-600 dark:peer-checked:bg-sky-600"
                                                ></div>
                                                Wajib?
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 gap-4 lg:grid-cols-12"
                                    >
                                        <div class="lg:col-span-6">
                                            <div
                                                class="relative z-0 w-full group"
                                            >
                                                <input
                                                    v-model="q.description"
                                                    type="text"
                                                    class="block px-0 py-2.5 w-full text-sm placeholder-transparent placeholder-gray-300 text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none peer focus:border-blue-600 focus:placeholder-gray-400 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
                                                    placeholder="Masukkan petunjuk pertanyaan"
                                                />
                                                <label
                                                    class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-600 rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-500"
                                                >
                                                    Petunjuk Pertanyaan
                                                </label>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-3">
                                            <Select
                                                v-model="q.category"
                                                :items="questionCategories"
                                                label="Pilih Kategori"
                                                class="w-full"
                                                :taggable="true"
                                            />
                                        </div>
                                        <div class="lg:col-span-3">
                                            <select
                                                v-model="q.answer_type"
                                                class="p-2 w-full text-gray-700 bg-white rounded-lg border border-gray-300"
                                            >
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

                                    <div
                                        v-if="
                                            [
                                                'select',
                                                'radio',
                                                'checkbox',
                                            ].includes(q.answer_type)
                                        "
                                        class="mt-4"
                                    >
                                        <div class="mb-2">
                                            <h4
                                                class="text-sm font-medium text-gray-700"
                                            >
                                                Opsi Jawaban
                                            </h4>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table
                                                class="w-full text-sm rounded border border-gray-200"
                                            >
                                                <thead>
                                                    <tr class="bg-gray-50">
                                                        <th
                                                            class="p-3 text-left border-b border-gray-200"
                                                        >
                                                            Label
                                                        </th>
                                                        <th
                                                            class="p-3 text-left border-b border-gray-200"
                                                        >
                                                            Value
                                                        </th>
                                                        <th
                                                            class="p-3 w-20 text-center border-b border-gray-200"
                                                        >
                                                            Aksi
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="(
                                                            opt, idx
                                                        ) in q.options"
                                                        :key="opt.uid"
                                                        class="hover:bg-gray-50"
                                                    >
                                                        <td
                                                            class="p-3 border-b border-gray-100"
                                                        >
                                                            <input
                                                                v-model="
                                                                    opt.label
                                                                "
                                                                type="text"
                                                                class="p-2 w-full text-sm rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                placeholder="Label opsi"
                                                            />
                                                        </td>
                                                        <td
                                                            class="p-3 border-b border-gray-100"
                                                        >
                                                            <input
                                                                v-model="
                                                                    opt.value
                                                                "
                                                                type="text"
                                                                class="p-2 w-full text-sm rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                placeholder="Value opsi"
                                                            />
                                                        </td>
                                                        <td
                                                            class="p-3 text-center border-b border-gray-100"
                                                        >
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    removeOption(
                                                                        q,
                                                                        idx
                                                                    )
                                                                "
                                                                class="px-2 py-1 text-xs text-red-600 rounded border border-red-300 hover:bg-red-50"
                                                            >
                                                                Hapus
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button
                                            type="button"
                                            @click="addOption(q)"
                                            class="px-4 py-2 mt-3 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            + Tambah Opsi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button
                            type="button"
                            @click="addQuestion"
                            class="px-4 py-2 text-white bg-green-500 rounded"
                        >
                            + Tambah Pertanyaan
                        </button>
                        <button
                            type="submit"
                            @click="submit"
                            class="px-4 py-2 text-white bg-blue-600 rounded"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import { computed, ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { v4 as uuidv4 } from "uuid";

import Dropdown from "primevue/dropdown";
import Input from "@/Components/form/Input.vue";
import Select from "@/Components/form/Select.vue";

defineOptions({ layout: AppLayout });

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Checklists", href: route("checklists.index") },
    { label: "Tambah Checklist" },
];

const props = defineProps({
    categories: Object,
    departments: Object,
    branches: Object,
    questionCategories: Object,
});

const categoryOptions = computed(() =>
    (props.categories ?? []).map((c) => ({ label: c.name, value: c.id }))
);
const departmentOptions = computed(() =>
    (props.departments ?? []).map((d) => ({ label: d.name, value: d.id }))
);
const branchOptions = computed(() =>
    (props.branches ?? []).map((b) => ({ label: b.name, value: b.id }))
);
const questionCategoryOptions = computed(() =>
    (props.questionCategories ?? []).map((qc) => ({
        label: qc.name,
        value: qc.id,
    }))
);

// Make questionCategories reactive so we can add new categories dynamically
const questionCategories = ref([...(props.questionCategories ?? [])]);

const questions = ref([
    {
        uid: uuidv4(),
        question: "",
        description: "",
        answer_type: "",
        required: false,
        options: [],
        category: null,
    },
]);

// Watch for new categories added via tagging
watch(
    questions,
    (newQuestions) => {
        newQuestions.forEach((q) => {
            if (q.category && typeof q.category === 'string') {
                // Check if category already exists
                const exists = questionCategories.value.some(
                    (cat) => cat.name === q.category || cat.id === q.category
                );

                if (!exists) {
                    // Add new category to the list
                    questionCategories.value.push({
                        id: q.category, // Use the string as ID for new categories
                        name: q.category,
                    });
                }
            }
        });
    },
    { deep: true }
);

const addQuestion = () => {
    questions.value.push({
        uid: uuidv4(),
        question: "",
        description: "",
        answer_type: "",
        required: false,
        options: [],
        category: null,
    });
};

const removeQuestion = (index) => {
    questions.value.splice(index, 1);
};

const addOption = (question) => {
    question.options.push({
        uid: uuidv4(),
        label: "",
        value: "",
    });
};

const removeOption = (question, index) => {
    question.options.splice(index, 1);
};

// Inertia form
const form = useForm({
    name: "",
    sop_code: "",
    category_id: null,
    department_id: null,
    status: "active",
    description: "",
    type: "single",
    questions: [
        {
            uid: uuidv4(),
            title: "",
            answer_type: "text", // text, textarea, radio, checkbox
            required: false,
            guidance: "",
            category_id: null,
            options: [],
        },
    ],
});

const submit = () => {
    form.questions = questions.value; // Sync sebelum kirim
    form.post(route("checklists.store"));
};
</script>
