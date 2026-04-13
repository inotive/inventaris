<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    shiftWork: Object,
    shifts: Array,
});


const form = useForm({
    shift_id: props.shiftWork.shift_id,
    work_date: props.shiftWork.work_date,
});

const submit = () => {
    form.put(route("shift-works.update", props.shiftWork.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit Shift Work" />
        <div class="p-6 max-w-lg">
            <h1 class="text-xl font-bold mb-4">Edit Shift Work</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block mb-1">Shift</label>
                    <select
                        v-model="form.shift_id"
                        class="w-full border rounded p-2"
                    >
                        <option v-for="s in shifts" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1">Tanggal</label>
                    <input
                        type="date"
                        v-model="form.work_date"
                        class="w-full border rounded p-2"
                    />
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Simpan
                    </button>
                    <Link
                        :href="route('shift-works.index')"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
