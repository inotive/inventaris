<template>
  <Head :title="`Edit Departemen #${department?.id}`" />
  <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
    <div class="flex items-center justify-between h-10">
      <Breadcrumb :items="breadcrumbs" />
      <Link :href="route('departments.index')" class="px-3 py-2 text-sm text-gray-700 rounded border">Kembali</Link>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Edit Departemen</h2>
      </div>
      <form @submit.prevent="save" class="p-6 space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block mb-1 text-sm text-gray-600">Nama <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" class="w-full px-3 py-2 text-sm border rounded" placeholder="Masukkan nama departemen" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name[0] }}</p>
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-600">Kode</label>
            <input v-model="form.code" type="text" class="w-full px-3 py-2 text-sm border rounded" placeholder="Masukkan kode (opsional)" />
            <p v-if="form.errors.code" class="mt-1 text-xs text-red-500">{{ form.errors.code[0] }}</p>
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-600">Cabang</label>
            <select v-model="form.branch_id" class="w-full px-3 py-2 text-sm border rounded">
              <option :value="null">-- Pilih Cabang --</option>
              <option v-for="b in branchOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
            </select>
            <p v-if="form.errors.branch_id" class="mt-1 text-xs text-red-500">{{ form.errors.branch_id[0] }}</p>
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-600">Status</label>
            <select v-model="form.status" class="w-full px-3 py-2 text-sm border rounded">
              <option :value="1">Aktif</option>
              <option :value="0">Non Aktif</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status[0] }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 text-sm text-gray-600">Deskripsi</label>
            <textarea v-model="form.description" rows="4" class="w-full px-3 py-2 text-sm border rounded" placeholder="Deskripsi (opsional)"></textarea>
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description[0] }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-3 py-2 text-white bg-blue-500 rounded">Simpan</button>
          <Link :href="route('departments.index')" class="px-3 py-2 text-gray-700 rounded border">Batal</Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/common/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  department: Object,
  branches: { type: Array, default: () => [] },
});

const branchOptions = (props.branches || []).map(b => ({ label: b.name, value: b.id }));

const breadcrumbs = [
  { label: 'Konfigurasi' },
  { label: 'Departemen', href: route('departments.index') },
  { label: props.department?.name || `#${props.department?.id}` },
];

const form = useForm({
  name: props.department?.name || '',
  code: props.department?.code || '',
  branch_id: props.department?.branch_id ?? null,
  status: props.department?.status ? 1 : 0,
  description: props.department?.description || '',
});

function save() {
  form.put(route('departments.update', props.department.id));
}

defineOptions({ layout: AppLayout });
</script>
