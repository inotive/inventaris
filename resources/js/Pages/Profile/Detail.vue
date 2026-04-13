<script setup>
import { ref, onBeforeUnmount, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

// Terima user via props (opsional) agar kompatibel dengan pemanggilan dari Show.vue
const props = defineProps({
    user: { type: Object, default: null },
});

const page = usePage();
const user = computed(() => props.user ?? page.props.auth?.user ?? {});
const employee = computed(() => page.props.employee ?? null);

const isEditing = ref(false);

// ------- Profile form (name, email, photo) -------
const photoPreview = ref(user.value?.profile_photo_url || null);
let objectUrl = null;

const profileForm = useForm({
    name: user.value?.name || "",
    email: user.value?.email || "",
    photo: null, // File
});

function onPhotoChange(e) {
    const file = e.target.files?.[0];
    profileForm.photo = file || null;

    if (objectUrl) URL.revokeObjectURL(objectUrl);
    if (file) {
        objectUrl = URL.createObjectURL(file);
        photoPreview.value = objectUrl;
    } else {
        photoPreview.value = user?.profile_photo_url || null;
    }
}

onBeforeUnmount(() => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
});

// ------- Password form (old, new, confirm) -------
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const submitting = ref(false);

function startEdit() {
    profileForm.name = user.value?.name || "";
    profileForm.email = user.value?.email || "";
    profileForm.photo = null;
    photoPreview.value = user.value?.profile_photo_url || null;

    passwordForm.current_password = "";
    passwordForm.password = "";
    passwordForm.password_confirmation = "";

    isEditing.value = true;
}

function cancelEdit() {
    isEditing.value = false;
    profileForm.reset("photo");
    passwordForm.reset();
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    photoPreview.value = user.value?.profile_photo_url || null;
}

async function submitAll() {
    submitting.value = true;
    let hasError = false;

    try {
        await profileForm
            .transform((data) => ({ ...data, _method: "PUT" }))
            .post(route("user-profile-information.update"), {
                preserveScroll: true,
                forceFormData: true,
                onError: () => {
                    hasError = true;
                },
            });

        if (
            passwordForm.current_password ||
            passwordForm.password ||
            passwordForm.password_confirmation
        ) {
            await passwordForm.put(route("user-password.update"), {
                preserveScroll: true,
                onError: () => {
                    hasError = true;
                },
            });
        }

        if (profileForm.hasErrors || passwordForm.hasErrors) {
            hasError = true;
        }

        if (!hasError) {
            profileForm.reset("photo");
            passwordForm.reset();
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            await router.reload({ only: ["auth"] });
            photoPreview.value = user.value?.profile_photo_url || null;
            isEditing.value = false;
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Profil berhasil diperbarui.",
                timer: 1500,
                showConfirmButton: false,
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Profil tidak dapat diperbarui. Periksa kembali data yang Anda masukkan.",
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: "error",
            title: "Kesalahan",
            text: "Terjadi kesalahan pada server. Silakan coba lagi nanti.",
        });
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <!-- Header + tombol -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ isEditing ? "Ubah Data Profile" : "Profil" }}
            </h3>
            <div class="flex items-center gap-2">
                <button
                    v-if="!isEditing"
                    type="button"
                    @click="startEdit"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                >
                    Ubah Profile
                </button>
            </div>
        </div>

        <!-- ================== MODE VIEW ================== -->
        <div
            v-if="!isEditing"
            class="grid grid-cols-1 lg:grid-cols-[320px,1fr]"
        >
            <!-- Kiri: avatar -->
            <div
                class="flex flex-col items-center justify-center gap-4 p-8 border-b lg:border-b-0 lg:border-r"
            >
                <div
                    class="flex items-center justify-center w-56 h-56 overflow-hidden rounded-full bg-blue-50 ring-1 ring-blue-100"
                >
                    <img
                        v-if="user?.profile_photo_url"
                        :src="`/storage/${user.profile_photo_path}`"
                        alt="Profile"
                        class="object-cover w-full h-full"
                    />
                    <span
                        v-else
                        class="text-6xl font-semibold text-blue-500 select-none"
                    >
                        {{
                            (user?.name || user?.username || "?")
                                .toString()
                                .charAt(0)
                                .toUpperCase()
                        }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">Foto profil saat ini</p>
            </div>

            <!-- Kanan: semua info (Name, Email + Informasi Karyawan dalam satu panel) -->
            <div class="p-8">
                <dl class="grid grid-cols-1 gap-y-5 gap-x-12 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Name</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">
                            {{ user?.name || "-" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Email Address</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">
                            {{ user?.email || "-" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Telepon</dt>
                        <dd class="mt-1 font-medium">
                            {{ employee?.contact || "-" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Alamat</dt>
                        <dd class="mt-1 font-medium">
                            {{ employee?.address || "-" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Cabang</dt>
                        <dd class="mt-1 font-medium">
                            {{ employee?.branch || "-" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Departemen</dt>
                        <dd class="mt-1 font-medium">
                            {{ employee?.department || "-" }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Shift</dt>
                        <dd class="mt-1 font-medium">
                            <template v-if="employee?.shift">
                                {{ employee.shift.name }}
                                <span class="text-sm text-gray-500">
                                    ({{ employee.shift.start_time }} -
                                    {{ employee.shift.end_time }})
                                </span>
                            </template>
                            <template v-else>-</template>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- ================== MODE EDIT (FORM) ================== -->
        <form
            v-else
            @submit.prevent="submitAll"
            class="grid grid-cols-1 lg:grid-cols-[320px,1fr]"
        >
            <!-- Avatar + preview -->
            <div
                class="flex flex-col items-center justify-center gap-4 p-8 border-b lg:border-b-0 lg:border-r"
            >
                <div
                    class="flex items-center justify-center w-56 h-56 overflow-hidden rounded-full bg-blue-50 ring-1 ring-blue-100"
                >
                    <img
                        v-if="photoPreview"
                        :src="photoPreview"
                        alt="Profile"
                        class="object-cover w-full h-full"
                    />
                    <span
                        v-else
                        class="text-6xl font-semibold text-blue-500 select-none"
                    >
                        {{
                            (user?.name || user?.username || "?")
                                .toString()
                                .charAt(0)
                                .toUpperCase()
                        }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">Pratinjau foto profil</p>
            </div>

            <!-- Form kanan (field TETAP) -->
            <div class="p-8">
                <div class="grid grid-cols-1 gap-4">
                    <label class="space-y-1">
                        <span class="text-sm text-gray-700">Name</span>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            autocomplete="name"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span
                            v-if="profileForm.errors.name"
                            class="text-sm text-red-600"
                        >
                            {{ profileForm.errors.name }}
                        </span>
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm text-gray-700">Email Address</span>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            autocomplete="email"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span
                            v-if="profileForm.errors.email"
                            class="text-sm text-red-600"
                        >
                            {{ profileForm.errors.email }}
                        </span>
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm text-gray-700">Old Password</span>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span
                            v-if="passwordForm.errors.current_password"
                            class="text-sm text-red-600"
                        >
                            {{ passwordForm.errors.current_password }}
                        </span>
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm text-gray-700">New Password</span>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span
                            v-if="passwordForm.errors.password"
                            class="text-sm text-red-600"
                        >
                            {{ passwordForm.errors.password }}
                        </span>
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm text-gray-700"
                            >Confirm Password</span
                        >
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span
                            v-if="passwordForm.errors.password_confirmation"
                            class="text-sm text-red-600"
                        >
                            {{ passwordForm.errors.password_confirmation }}
                        </span>
                    </label>

                    <div class="space-y-1">
                        <span class="text-sm text-gray-700"
                            >Change Profile Photo</span
                        >
                        <div class="flex items-center gap-3">
                            <label
                                class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md cursor-pointer hover:bg-indigo-700"
                            >
                                Choose File
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onPhotoChange"
                                />
                            </label>
                            <span class="text-sm text-gray-600 truncate">
                                {{
                                    profileForm.photo
                                        ? profileForm.photo.name
                                        : "No file chosen"
                                }}
                            </span>
                        </div>
                        <span
                            v-if="profileForm.errors.photo"
                            class="text-sm text-red-600"
                        >
                            {{ profileForm.errors.photo }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ submitting ? "Updating..." : "Update Profile" }}
                        </button>
                        <button
                            type="button"
                            @click="cancelEdit"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border rounded-md hover:bg-gray-50"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <!-- /MODE EDIT -->
    </div>
</template>
