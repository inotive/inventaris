<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Buat Pengajuan Baru</h1>
            <p class="mt-1 text-sm text-gray-600">
                Isi formulir di bawah ini untuk membuat pengajuan baru.
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <!-- Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Jenis Pengajuan <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <button
                                v-for="(label, type) in submissionTypes"
                                :key="type"
                                type="button"
                                @click="form.type = type"
                                class="relative rounded-lg border p-4 text-center focus:outline-none"
                                :class="{
                                    'border-blue-500 ring-2 ring-blue-500': form.type === type,
                                    'border-gray-300 hover:border-gray-400': form.type !== type,
                                }"
                            >
                                <span class="block text-sm font-medium text-gray-900">
                                    {{ label }}
                                </span>
                            </button>
                        </div>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Dynamic form fields based on selected type -->
                    <div v-if="form.type">
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Detail {{ submissionTypes[form.type] }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Mohon isi detail pengajuan {{ submissionTypes[form.type].toLowerCase() }} Anda.
                            </p>
                        </div>

                        <!-- Date Range -->
                        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="start_date"
                                    v-model="form.start_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">
                                    Tanggal Selesai <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="end_date"
                                    v-model="form.end_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                                <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.end_date }}
                                </p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mt-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700">
                                Alasan <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <textarea
                                    id="reason"
                                    v-model="form.reason"
                                    rows="4"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Tuliskan alasan pengajuan Anda..."
                                ></textarea>
                            </div>
                            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                {{ form.errors.reason }}
                            </p>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">
                                Dokumen Pendukung
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 48 48"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label
                                            for="file-upload"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                                        >
                                            <span>Unggah file</span>
                                            <input
                                                id="file-upload"
                                                name="file-upload"
                                                type="file"
                                                class="sr-only"
                                                @change="handleFileUpload"
                                            />
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, PDF hingga 10MB
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.documents" class="mt-1 text-sm text-red-600">
                                {{ form.errors.documents }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <Link
                        :href="route('submission.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        <span v-if="form.processing">
                            <svg
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Menyimpan...
                        </span>
                        <span v-else>Simpan Pengajuan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: 'AppLayout',
});

const props = defineProps({
    submissionTypes: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    type: '',
    start_date: '',
    end_date: '',
    reason: '',
    documents: [],
});

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    form.documents = files;
};

const submit = () => {
    // Format the data for submission
    const formData = new FormData();
    
    formData.append('type', form.type);
    formData.append('start_date', form.start_date);
    formData.append('end_date', form.end_date);
    formData.append('reason', form.reason);
    
    // Append each file
    if (form.documents.length > 0) {
        form.documents.forEach((file, index) => {
            formData.append(`documents[${index}]`, file);
        });
    }
    
    form.post(route('submission.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Handle success (e.g., show success message, redirect, etc.)
            form.reset();
        },
    });
};
</script>
