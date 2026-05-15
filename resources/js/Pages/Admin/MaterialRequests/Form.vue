<template>
    <form @submit.prevent="$emit('submit')" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Request</label>
                <input v-model="form.request_no" type="text" disabled
                    class="h-10 p-0 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                <p v-if="form.errors.request_no" class="mt-1 text-sm text-red-500">
                    {{ form.errors.request_no[0] }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Permintaan</label>
                <input v-model="form.requested_at" type="date"
                    class="h-10 ps-0 pe-5 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                <p v-if="form.errors.requested_at" class="mt-1 text-sm text-red-500">
                    {{ form.errors.requested_at[0] }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                <Select v-model="form.department_id" :items="departments" :disabled="!isSuperadmin" class="mt-2">
                    <template #item="{ item }">
                        <span>
                            {{ item.name }}{{ item.branch ? " - " + item.branch.name : "" }}
                        </span>
                    </template>
                </Select>
                <p v-if="!isSuperadmin && form.department_id" class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Otomatis diisi berdasarkan data karyawan Anda
                </p>
                <p v-if="form.errors.department_id" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.department_id[0] }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permintaan oleh</label>
                <Select v-model="form.employee_id" :items="localEmployees"
                    :disabled="!isSuperadmin ||isLoadingEmployees" class="mt-2" />
                <div v-if="!isLoadingEmployees && localEmployees.length === 0 && form.department_id"
                    class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Tidak ada karyawan tersedia untuk departemen ini
                </div>
                <div v-if="isLoadingEmployees" class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    <svg class="inline w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Memuat data karyawan...
                </div>
                <p v-if="!isSuperadmin && form.employee_id && !isLoadingEmployees"
                    class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Otomatis diisi berdasarkan data karyawan Anda
                </p>
                <p v-if="employeeError" class="mt-1 text-xs text-rose-600">
                    {{ employeeError }}
                </p>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.employee_id[0] }}
                </p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keperluan</label>
                <textarea v-model="form.requirement" rows="2" placeholder="Masukkan keperluan permintaan barang"
                    class="mt-1 w-full rounded-md text-sm border-gray-300 placeholder:text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"></textarea>
                <p v-if="form.errors.requirement" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.requirement[0] }}
                </p>
            </div>
        </div>
        <!-- <div class="flex items-center gap-2 justify-end pt-2">
            <button
                type="button"
                @click="$emit('cancel')"
                class="inline-flex items-center rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
            >
                Batal
            </button>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded bg-primary-600 px-3 py-2 text-sm font-medium text-gray-900 hover:bg-primary-700 disabled:opacity-60"
            >
                Simpan
            </button>
        </div> -->
    </form>
</template>

<script setup>
import Input from "@/Components/form/Input.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import { watch, onMounted, ref } from "vue";
import axios from "axios";

const props = defineProps({
    form: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, required: true },
    isEdit: { type: Boolean, required: true },
});

const emit = defineEmits(['submit', 'cancel', 'department-changed']);

console.log("form department_id:", props.form.department_id);


// Check if user is Superadmin
const isSuperadmin = props.user?.roles?.[0]?.name === 'Superadmin';

// Local state for employees
const localEmployees = ref([...props.employees]);
const isLoadingEmployees = ref(false);
const employeeError = ref('');

// Function to fetch employees by department
const fetchEmployeesByDepartment = async (departmentId) => {
    console.log(departmentId)
    if (!departmentId) {
        localEmployees.value = ''
        employeeError.value = '';
        return;
    }

    try {
        isLoadingEmployees.value = true;
        employeeError.value = '';
        const response = await axios.get(route('departments.employees', departmentId), {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        // Handle the web route response format
        if (response.data && Array.isArray(response.data)) {
            localEmployees.value = response.data.map(employee => ({
                id: employee.id,
                name: employee.name,
                email: employee.user?.email || '',
            }));
        } else {
            localEmployees.value = [];
            employeeError.value = 'Tidak ada karyawan ditemukan untuk departemen ini';
        }

        // Clear employee selection when department changes
        props.form.employee_id = null;
    } catch (error) {
        console.error('Error fetching employees:', error);
        localEmployees.value = [];
        employeeError.value = 'Gagal memuat data karyawan. Silakan coba lagi.';
    } finally {
        isLoadingEmployees.value = false;
    }
};

// Auto-populate department and employee for non-Superadmin users
onMounted(() => {
    console.log('Form mounted - User data:', props.user);
    console.log('User employee:', props.user?.employee);
    console.log('Is Superadmin:', isSuperadmin);

    if (!isSuperadmin && props.user?.employee) {
        console.log('Auto-populating fields for non-Superadmin user');
        // Set department from user's employee data
        if (props.user.employee.department_id) {
            props.form.department_id = props.isEdit ? props.form.department_id : props.user.employee.department_id;
            console.log('Set department_id to:', props.form.department_id);

            // Fetch employees for this department
            // fetchEmployeesByDepartment(props.user.employee.department_id);
        }

        // Set employee from user's employee data
        if (props.user.employee.id) {
            props.form.employee_id = props.isEdit ? props.form.employee_id : props.user.employee.id;
            console.log('Set employee_id to:', props.form.employee_id);
        }
    }
});

// Watch for changes in user data and update form accordingly
watch(() => props.user, (newUser) => {
    console.log('User data changed:', newUser);
    console.log('isSuperadmin:', isSuperadmin);
    if (!isSuperadmin && newUser?.employee) {
        if (newUser.employee.department_id && !props.form.department_id) {
            props.form.department_id = newUser.employee.department_id;
            console.log('Updated department_id to:', props.form.department_id);

            // Fetch employees for this department
            fetchEmployeesByDepartment(newUser.employee.department_id);
        }

        if (newUser.employee.id && !props.form.employee_id) {
            props.form.employee_id = newUser.employee.id;
            console.log('Updated employee_id to:', props.form.employee_id);
        }
    }
}, { deep: true });

// Watch for department changes and fetch employees
watch(() => props.form.department_id, (newDepartmentId, oldDepartmentId) => {
    if (newDepartmentId !== oldDepartmentId) {
        console.log('Department changed to:', newDepartmentId);
        fetchEmployeesByDepartment(newDepartmentId);

        // Emit department change event to parent component
        emit('department-changed', newDepartmentId);
    }
});
</script>
