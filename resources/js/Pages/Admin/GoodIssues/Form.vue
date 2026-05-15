<template>

    <form class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Tanggal Permintaan</label
                >
                <input
                    v-model="form.date"
                    type="date"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                />
                <p v-if="form.errors.date" class="mt-1 text-sm text-red-500">
                    {{ form.errors.date[0] }}
                </p>
            </div>
            <div class="space-y-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Departemen</label
                >
                <Select
                    v-model="form.department_id"
                    label="Pilih departemen"
                    :items="
                        props.user?.department
                            ? [props.user.department]
                            : departments
                    "
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
            <div class="space-y-2">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Referensi MR</label
                >
                <div class="relative">
                    <Select
                        v-model="form.request_id"
                        label="Pilih nomor request"
                        label-key="request_no"
                        search-key="request_no"
                        :items="materialRequestsWithNone"
                        :disabled="loadingMRs"
                    >
                        <template #item="{ item }">
                            <span>
                                {{ item.request_no }}
                            </span>
                        </template>
                    </Select>
                    <div v-if="loadingMRs" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                    </div>
                </div>
                <p
                    v-if="form.errors.employee_id"
                    class="mt-1 text-xs text-rose-600"
                >
                    {{ form.errors.employee_id[0] }}
                </p>
            </div>
            <div class="col-span-3">
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >Keperluan</label
                >
                <textarea
                    v-model="form.requirement"
                    rows="2"
                    placeholder="Masukkan keperluan permintaan barang"
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
import { watch, computed, ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    form: { type: Object, required: true },
    departments: { type: Array, required: true },
    materialRequests: { type: Array, required: true },
    isSuperadmin: { type: Boolean, required: true },
    isEdit: { type: Boolean, default: false },
});

// Store fetched MRs based on department
const fetchedMaterialRequests = ref([]);
const loadingMRs = ref(false);

// Create computed property to add "Tanpa Referensi" option at the top
const materialRequestsWithNone = computed(() => {
    const noneOption = {
        id: null,
        request_no: "Tanpa Referensi"
    };

    // Use fetched MRs if available, otherwise use fallback data
    const mrData = fetchedMaterialRequests.value.length > 0
        ? fetchedMaterialRequests.value
        : [];

    return [noneOption, ...mrData];
});

const emit = defineEmits(['items-loaded', 'reset-items']);

// Function to fetch MRs by department
const fetchMaterialRequestsByDepartment = async (departmentId) => {
    if (!departmentId) {
        fetchedMaterialRequests.value = [];
        return;
    }

    loadingMRs.value = true;
    try {
        const response = await axios.get(route('material-requests.requests'), {
            params: { department_id: departmentId }
        });
        fetchedMaterialRequests.value = response.data.materialRequests || [];
    } catch (error) {
        console.error('Error loading MRs by department:', error);
        fetchedMaterialRequests.value = [];
    } finally {
        loadingMRs.value = false;
    }
};

// Watch for department changes to fetch MRs and clear selection
watch(
    () => props.form.department_id,
    (newDepartmentId) => {
        // Clear the request_id when department changes
        props.form.request_id = null;

        // Fetch MRs for the new department
        fetchMaterialRequestsByDepartment(newDepartmentId);
    }
);

// Watch for request_id changes to load items
watch(
    () => props.form.request_id,
    async (newRequestId) => {
        if (!newRequestId || newRequestId === null) {
            // Only reset items if not in edit mode
            if (!props.isEdit) {
                emit('items-loaded', []);
                emit('reset-items');
            }
            return;
        }

        // In edit mode, don't auto-load items from MR changes
        // Only load if user explicitly changes the MR reference
        try {
            const response = await axios.get(route('material-requests.items.by-request'), {
                params: { request_id: newRequestId }
            });
            emit('items-loaded', response.data.items);
        } catch (error) {
            console.error('Error loading MR items:', error);
            emit('items-loaded', []);
        }
    },
    { immediate: !props.isEdit } // Only run immediately if NOT in edit mode
);

// Fetch MRs on mount if department is already selected
onMounted(() => {
    if (props.form.department_id) {
        fetchMaterialRequestsByDepartment(props.form.department_id);
    }
});
</script>
