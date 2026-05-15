<template>
    <form @submit.prevent="$emit('submit')" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Request</label>
                <input v-model="form.request_no" type="text" class="mt-1 w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                <p v-if="form.errors.request_no" class="mt-1 text-xs text-rose-600">{{ form.errors.request_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Permintaan</label>
                <input v-model="form.request_date" type="date" class="mt-1 w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                <p v-if="form.errors.request_date" class="mt-1 text-xs text-rose-600">{{ form.errors.request_date }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                <select v-model="form.department_id" class="mt-1 w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                    <option :value="null">Pilih Departemen</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
                <p v-if="form.errors.department_id" class="mt-1 text-xs text-rose-600">{{ form.errors.department_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Diminta oleh</label>
                <select v-model="form.request_by" class="mt-1 w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                    <option :value="null">Pilih Karyawan</option>
                    <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
                <p v-if="form.errors.request_by" class="mt-1 text-xs text-rose-600">{{ form.errors.request_by }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select v-model="form.status" class="mt-1 w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-xs text-rose-600">{{ form.errors.status }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"></textarea>
                <p v-if="form.errors.notes" class="mt-1 text-xs text-rose-600">{{ form.errors.notes }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 justify-end pt-2">
            <button type="button" @click="$emit('cancel')" class="inline-flex items-center rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Batal</button>
            <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-60">Simpan</button>
        </div>
    </form>
</template>

<script setup>
const props = defineProps({
    form: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
})
</script>
