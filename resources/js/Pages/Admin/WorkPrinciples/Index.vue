<template>
    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <Head title="Prinsip & Etos Kerja" />
        <div class="flex items-center justify-between h-10">
            <Breadcrumb
                :items="[
                    { label: 'Konfigurasi' },
                    { label: 'Prinsip & Etos Kerja' },
                ]"
            />
            <div class="flex gap-2">
                <button
                    v-if="can('work_principles.create')"
                    class="px-3 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                    @click="openCreate"
                >
                    Tambah
                </button>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg"
        >
            <div class="flex items-center justify-between px-6 py-3 border-b">
                <div class="text-xl font-semibold text-gray-800">
                    Daftar Prinsip & Etos Kerja
                </div>
                <div class="flex items-center gap-2">
                    <select
                        v-model="filters.category"
                        class="h-10 px-3 text-sm border border-gray-200 rounded-lg"
                    >
                        <option :value="null">Semua Kategori</option>
                        <option value="prinsip">Prinsip</option>
                        <option value="etos kerja">Etos Kerja</option>
                    </select>
                    <div class="relative">
                        <input
                            v-model="filters.q"
                            type="text"
                            placeholder="Cari..."
                            class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                        />
                        <span
                            class="absolute text-gray-400 -translate-y-1/2 right-2 top-1/2"
                            >🔍</span
                        >
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    No
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Kategori
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Judul
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Deskripsi
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Gambar
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Dibuat
                                </div>
                            </th>
                            <th
                                v-if="can('work_principles.edit') || can('work_principles.delete')"
                                class="p-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Aksi
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="rows.data && rows.data.length">
                            <tr
                                v-for="(row, idx) in rows.data"
                                :key="row.id"
                                class="border-b border-gray-200"
                            >
                                <td class="p-3 text-center">
                                    {{
                                        (rows.current_page - 1) * rows.per_page +
                                        idx +
                                        1
                                    }}
                                </td>
                                <td class="p-3">
                                    <span
                                        class="inline-block px-2 py-0.5 text-xs text-blue-700 bg-blue-100 rounded-full border border-blue-300"
                                    >
                                        {{
                                            row.category
                                                ? row.category
                                                      .charAt(0)
                                                      .toUpperCase() +
                                                  row.category.slice(1)
                                                : "-"
                                        }}
                                    </span>
                                </td>
                                <td class="p-3">{{ row.title }}</td>
                                <td class="p-3">{{ row.description || "-" }}</td>
                                <td class="p-3">
                                    <img
                                        v-if="row.img_url"
                                        :src="row.img_url"
                                        alt="gambar"
                                        class="h-10 rounded"
                                    />
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="p-3 text-center">
                                    {{
                                        new Date(row.created_at).toLocaleString(
                                            "id-ID"
                                        )
                                    }}
                                </td>
                                <td
                                    v-if="can('work_principles.edit') || can('work_principles.delete')"
                                    class="p-3"
                                >
                                    <div class="flex justify-center gap-2">
                                        <button
                                            v-if="can('work_principles.edit')"
                                            class="p-1 text-yellow-500 hover:text-yellow-700"
                                            @click="openEdit(row)"
                                            title="Edit"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="can('work_principles.delete')"
                                            class="p-1 text-red-500 hover:text-red-700"
                                            @click="openConfirmModal(row)"
                                            title="Hapus"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td
                                :colspan="can('work_principles.edit') || can('work_principles.delete') ? 7 : 6"
                                class="py-6 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="rows.data && rows.data.length"
                :pagination="rows"
                @page-changed="changePage"
                @per-page-changed="perPageChanged"
                class="border-t"
            />
        </div>

        <!-- Modal -->
        <Dialog
            v-model:visible="showModal"
            modal
            dismissableMask
            :style="{ width: '600px', padding: '6px' }"
        >
            <template #header>
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{ form.id ? "Edit" : "Tambah" }} Item
                    </h3>
                </div>
            </template>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block mb-1 text-sm text-gray-600"
                        >Kategori</label
                    >
                    <select
                        v-model="form.category"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    >
                        <option value="prinsip">Prinsip</option>
                        <option value="etos kerja">Etos Kerja</option>
                    </select>
                    <p
                        v-if="form.errors.category"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.category }}
                    </p>
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 text-sm text-gray-600"
                        >Judul</label
                    >
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    />
                    <p
                        v-if="form.errors.title"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 text-sm text-gray-600"
                        >Deskripsi</label
                    >
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded"
                    ></textarea>
                    <p
                        v-if="form.errors.description"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 text-sm text-gray-600"
                        >Gambar (opsional)</label
                    >
                    <div class="flex items-center gap-3">
                        <input
                            type="file"
                            accept="image/*"
                            @change="onFileChange"
                            class="w-full h-10 px-3 border border-gray-300 rounded"
                        />
                        <button
                            type="button"
                            class="h-10 px-2 text-xs border rounded"
                            @click="clearImage"
                            v-if="previewUrl || form.img_url"
                        >
                            Hapus
                        </button>
                    </div>
                    <p
                        v-if="form.errors.img_file"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.img_file }}
                    </p>
                    <div v-if="previewUrl || form.img_url" class="mt-2">
                        <img
                            :src="previewUrl || form.img_url"
                            alt="preview"
                            class="h-24 border rounded"
                        />
                    </div>
                </div>
                <div class="flex justify-end col-span-2 gap-2">
                    <button
                        class="h-10 px-3 border rounded"
                        type="button"
                        @click="showModal = false"
                    >
                        Batal
                    </button>
                    <button
                        :disabled="form.processing"
                        class="h-10 px-3 text-white bg-blue-600 rounded disabled:opacity-60"
                        type="button"
                        @click="submit"
                    >
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan</span>
                    </button>
                </div>
            </div>
        </Dialog>

        <ConfirmModal
            :show="isConfirmModalOpen"
            :question="`Yakin ingin menghapus`"
            :selected="`${selectedItem?.title || ''}`"
            title="Hapus Prinsip & Etos Kerja"
            confirmText="Ya, Hapus!"
            maxWidth="md"
            @close="closeConfirmModal"
            @confirm="destroyData"
        />
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Dialog from "primevue/dialog";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

defineOptions({ layout: AppLayout });

const props = defineProps({ rows: Object, filters: Object });
// Make rows reactive to prop updates (pagination, search, filters, redirects)
const rows = computed(() => props.rows || { data: [] });
const filters = ref({
    q: props.filters?.q || "",
    category: props.filters?.category ?? null,
});

let t = null;
watch(
    filters,
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 350);
    },
    { deep: true }
);

function fetchList() {
    router.get(
        route("work-principles.index"),
        { ...filters.value },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

const showModal = ref(false);
const form = useForm({
    id: null,
    category: "prinsip",
    title: "",
    description: "",
    img_file: null,
    created_by: null,
});
const previewUrl = ref("");

function openCreate() {
    if (!can('work_principles.create')) {
        return;
    }

    form.defaults({
        id: null,
        category: "prinsip",
        title: "",
        description: "",
        created_by: null,
    });
    form.reset();
    form.clearErrors();
    form.img_file = null;
    previewUrl.value = "";
    showModal.value = true;
}

function openEdit(row) {
    if (!can('work_principles.edit')) {
        return;
    }

    form.defaults({
        id: row.id,
        category: row.category,
        title: row.title,
        description: row.description,
        created_by: row.created_by || null,
    });
    form.reset();
    form.clearErrors();
    Object.assign(form, {
        id: row.id,
        category: row.category,
        title: row.title,
        description: row.description,
        created_by: row.created_by || null,
    });
    form.img_file = null;
    previewUrl.value = row.img_url || "";
    showModal.value = true;
}

function submit() {
    const options = {
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false;
        },
    };

    if (form.id) {
        if (!can('work_principles.edit')) {
            return;
        }
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route("work-principles.update", form.id), {
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
            },
            onError: (errors) => {
                console.error('Error updating work principle:', errors);
                alert('Terjadi kesalahan saat memperbarui data.');
            },
        });
    } else {
        if (!can('work_principles.create')) {
            return;
        }
        form.post(route("work-principles.store"), options);
    }
}

// Destroy
const selectedItem = ref(null);
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can('work_principles.delete')) {
        return;
    }
    if (!selectedItem.value?.id) return;

    router.delete(route("work-principles.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

function onFileChange(e) {
    const f = e?.target?.files?.[0];
    if (!f) return;
    form.img_file = f;
    previewUrl.value = URL.createObjectURL(f);
}

function clearImage() {
    form.img_file = null;
    // keep img_url but allow manual clear
    previewUrl.value = "";
}

function changePage(page) {
    router.get(route("work-principles.index"), { ...filters.value, page: page }, { preserveScroll: true, preserveState: true, replace: true });
}

function perPageChanged(perPage) {
    router.get(route("work-principles.index"), { ...filters.value, per_page: perPage }, { preserveScroll: true, preserveState: true, replace: true });
}
</script>
