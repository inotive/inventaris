<template>
    <Head title="Edit Akses Pengguna" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div
            class="flex flex-col overflow-hidden rounded border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex items-center justify-between gap-2 px-8 py-5 font-semibold text-gray-700 border-b md:text-xl dark:text-gray-300"
            >
                Kelola Hak Akses
                <span class="text-sky-500">{{ role.name }}</span>
            </div>

            <div class="overflow-auto" data-simplebar>
                <div class="grid grid-cols-1 gap-6 p-8 md:grid-cols-4">
                    <!-- Group -->
                    <div
                        v-for="(
                            groupPermissions, groupName
                        ) in props.permissions"
                        :key="groupName"
                        class="p-4 space-y-2 border rounded-lg"
                    >
                        <h5
                            class="flex items-center justify-between text-lg font-bold dark:text-gray-100"
                        >
                            {{ groupName }}
                            <label v-if="getFilteredPermissions(groupPermissions).length > 1" class="cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="isGroupChecked(groupPermissions)"
                                    :disabled="!can('roles.edit')"
                                    @change="
                                        toggleGroup(
                                            groupPermissions,
                                            $event.target.checked
                                        )
                                    "
                                    class="sr-only peer"
                                />
                                <div
                                    class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-sky-300 dark:peer-focus:ring-sky-500 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-sky-500 dark:peer-checked:bg-sky-600"
                                ></div>
                            </label>
                        </h5>

                        <div class="grid gap-1">
                            <!-- Permission -->
                            <div
                                v-for="permission in getFilteredPermissions(groupPermissions)"
                                :key="permission.id"
                                class="flex flex-col gap-2 p-2"
                            >
                                <div class="flex items-center gap-3">
                                    <label class="cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="
                                                checkedPermissions[permission.id]
                                            "
                                            :checked="hasPermission(permission.id)"
                                            :disabled="!can('roles.edit')"
                                            @change="
                                                isViewPermission(permission) && hasViewAndGlobal(groupPermissions)
                                                    ? handleViewToggle(permission, groupPermissions, $event.target.checked)
                                                    : togglePermission(
                                                        permission.id,
                                                        $event.target.checked
                                                    )
                                            "
                                            class="sr-only peer"
                                        />
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-sky-300 dark:peer-focus:ring-sky-500 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-sky-500 dark:peer-checked:bg-sky-600"
                                        ></div>
                                    </label>
                                    <span class="dark:text-gray-200">
                                        {{ permission.display_name }}
                                    </span>
                                </div>

                                <!-- Dropdown if view is enabled and group has view and global -->
                                <div v-if="isViewPermission(permission) && hasViewAndGlobal(groupPermissions) && checkedPermissions[permission.id]" class="mt-1 pl-[56px]">
                                    <select
                                        :value="checkedPermissions[getAssociatedGlobalPermission(permission, groupPermissions)?.id] ? 'all' : 'self'"
                                        @change="handleScopeChange(permission, groupPermissions, $event.target.value)"
                                        :disabled="!can('roles.edit')"
                                        class="w-full text-xs rounded border border-gray-200 dark:border-gray-600 bg-transparent dark:bg-gray-800 text-gray-800 dark:text-white/90 focus:ring-1 focus:ring-sky-500 focus:border-sky-500"
                                    >
                                        <option value="self">Cabang Sendiri</option>
                                        <option value="all">Semua Cabang</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import { ref, onMounted, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

defineOptions({
    layout: AppLayout,
});

const { can } = useAuth();

const props = defineProps({
    role: Object,
    permissions: Object,
});

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Jabatan", href: route("roles.index") },
    { label: props.role.name },
];

// Daftar permission role saat ini
const rolePermissions = ref(props.role.permissions.map((p) => p.id));

// Cek apakah permission aktif
const hasPermission = (id) => rolePermissions.value.includes(id);

const toggleForm = useForm({
    // permission_id: null,
    permission_ids: [],
    checked: false,
});

const togglePermission = (permissionId, checked) => {
    // cek hak sebelum melakukan perubahan
    if (!can('roles.edit')) {
        // Opsional: tampilkan notifikasi bahwa user tidak punya akses
        return;
    }

    toggleForm.permission_ids = [permissionId]; // kirim 1 id saja
    toggleForm.checked = checked;

    toggleForm.patch(route("roles.update", props.role.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (checked) {
                if (!rolePermissions.value.includes(permissionId)) {
                    rolePermissions.value.push(permissionId);
                }
            } else {
                rolePermissions.value = rolePermissions.value.filter(
                    (id) => id !== permissionId
                );
            }

            // update reactive v-model juga
            checkedPermissions[permissionId] = checked;
        },
    });
};

const toggleMultiplePermissions = (permissionIds, checked) => {
    if (!can('roles.edit')) return;

    toggleForm.permission_ids = permissionIds;
    toggleForm.checked = checked;

    toggleForm.patch(route("roles.update", props.role.id), {
        preserveScroll: true,
        onSuccess: () => {
            permissionIds.forEach((id) => {
                if (checked) {
                    if (!rolePermissions.value.includes(id)) {
                        rolePermissions.value.push(id);
                    }
                } else {
                    rolePermissions.value = rolePermissions.value.filter(
                        (item) => item !== id
                    );
                }
                checkedPermissions[id] = checked;
            });
        },
    });
};

const toggleGroup = (groupPermissions, checked) => {
    if (!can('roles.edit')) return;

    const ids = groupPermissions.map((p) => p.id);
    toggleForm.permission_ids = ids;
    toggleForm.checked = checked;

    toggleForm.patch(route("roles.update", props.role.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (checked) {
                ids.forEach((id) => {
                    if (!rolePermissions.value.includes(id)) {
                        rolePermissions.value.push(id);
                    }
                });
            } else {
                rolePermissions.value = rolePermissions.value.filter(
                    (id) => !ids.includes(id)
                );
            }
            // sync checkedPermissions
            ids.forEach((id) => (checkedPermissions[id] = checked));
        },
    });
};

const isGroupChecked = (groupPermissions) => {
    return groupPermissions.every((p) => hasPermission(p.id));
};

const checkedPermissions = reactive({});

const hasViewAndGlobal = (groupPermissions) => {
    const hasView = groupPermissions.some(p => p.name.endsWith('.view'));
    const hasGlobal = groupPermissions.some(p => p.name.endsWith('.view_global'));
    return hasView && hasGlobal;
};

const getFilteredPermissions = (groupPermissions) => {
    if (hasViewAndGlobal(groupPermissions)) {
        return groupPermissions.filter(p => !p.name.endsWith('.view_global'));
    }
    return groupPermissions;
};

const isViewPermission = (permission) => {
    return permission.name.endsWith('.view');
};

const getAssociatedGlobalPermission = (permission, groupPermissions) => {
    const baseName = permission.name.slice(0, -5); // remove '.view'
    return groupPermissions.find(p => p.name === baseName + '.view_global');
};

const handleViewToggle = (permission, groupPermissions, checked) => {
    const globalPerm = getAssociatedGlobalPermission(permission, groupPermissions);
    if (!checked && globalPerm && hasPermission(globalPerm.id)) {
        toggleMultiplePermissions([permission.id, globalPerm.id], false);
    } else {
        togglePermission(permission.id, checked);
    }
};

const handleScopeChange = (permission, groupPermissions, value) => {
    const globalPerm = getAssociatedGlobalPermission(permission, groupPermissions);
    if (globalPerm) {
        togglePermission(globalPerm.id, value === 'all');
    }
};

onMounted(() => {
    Object.values(props.permissions).forEach((group) => {
        group.forEach((permission) => {
            checkedPermissions[permission.id] = hasPermission(permission.id);
        });
    });
});
</script>