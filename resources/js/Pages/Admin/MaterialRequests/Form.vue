<template>
    <form @submit.prevent="$emit('submit')" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Request</label>
                <input v-model="form.request_no" type="text"
                    class="h-10 p-0 w-full border-0 border-b-2 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 font-mono"
                    placeholder="Auto-generate jika dikosongkan" />
                <div class="text-xs text-gray-400 mt-1">Kosongkan untuk generate otomatis (MR-SPB-001-06-2026)</div>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Untuk Cabang</label>
                <Select v-model="form.department_id" :items="branchesAsDepartments" class="mt-2" />
                <p v-if="form.errors.department_id" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.department_id[0] }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permintaan oleh</label>
                <Select v-model="form.employee_id" :items="employees"
                    :disabled="!isSuperadmin" class="mt-2" />
                <p v-if="!isSuperadmin && form.employee_id"
                    class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Otomatis diisi berdasarkan data pengguna
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
import { watch, onMounted, computed } from "vue";

const props = defineProps({
    form: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, required: true },
    isEdit: { type: Boolean, required: true },
});

const emit = defineEmits(['submit', 'cancel', 'department-changed']);

console.log("form department_id:", props.form.department_id);

const branchesAsDepartments = computed(() => {
    const list = props.departments || [];
    return list.map(item => {
        return {
            id: item.id,
            name: item.branch ? item.branch.name : item.name,
        };
    });
});


// Check if user is Superadmin
const isSuperadmin = props.user?.roles?.[0]?.name === 'Superadmin';

// Auto-populate department and employee for non-Superadmin users
onMounted(() => {
    console.log('Form mounted - User data:', props.user);
    console.log('User employee:', props.user?.employee);
    console.log('Is Superadmin:', isSuperadmin);

    if (!isSuperadmin && props.user?.employee) {
        console.log('Auto-populating fields for non-Superadmin user');
        // Set department from user's employee data
        if (props.user.employee.department_id && !props.form.department_id) {
            props.form.department_id = props.isEdit ? props.form.department_id : props.user.employee.department_id;
            console.log('Set department_id to:', props.form.department_id);
        }

        // Set employee from user's employee data
        if (props.user.employee.id && !props.form.employee_id) {
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
        }

        if (newUser.employee.id && !props.form.employee_id) {
            props.form.employee_id = newUser.employee.id;
            console.log('Updated employee_id to:', props.form.employee_id);
        }
    }
}, { deep: true });

// Watch for department changes
watch(() => props.form.department_id, (newDepartmentId, oldDepartmentId) => {
    if (newDepartmentId !== oldDepartmentId) {
        console.log('Department changed to:', newDepartmentId);
        
        // Emit department change event to parent component
        emit('department-changed', newDepartmentId);
    }
});
</script>
