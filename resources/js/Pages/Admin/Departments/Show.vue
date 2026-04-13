<template>
  <Head :title="`Detail Departemen #${department?.id}`" />
  <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
    <div class="flex items-center justify-between h-10">
      <Breadcrumb :items="breadcrumbs" />
      <Link :href="route('departments.index')" class="px-3 py-2 text-sm text-gray-700 rounded border">Kembali</Link>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Info Departemen {{ department?.name }} - {{ department?.branch }}</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
        <Info label="Nama" :value="department?.name" />
        <Info label="Kode" :value="department?.code" />
        <Info label="Status" :value="department?.status ? 'Aktif' : 'Non Aktif'" />
        <Info label="Cabang" :value="department?.branch" />
        <Info label="Jumlah Pegawai" :value="department?.employees_count" />
        <div class="md:col-span-2">
          <label class="block mb-1 text-sm text-gray-600">Deskripsi</label>
          <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ department?.description || '-' }}</div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Karyawan</h3>
      </div>
      <div class="p-4 overflow-auto" data-simplebar>
        <table class="min-w-full text-sm">
          <thead>
            <tr>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="font-medium text-center text-gray-600">No</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Nama</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Gender</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Cabang</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Shift</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Email</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Status</div></th>
            </tr>
          </thead>
          <tbody>
            <template v-if="employees?.data?.length">
              <tr v-for="(e, idx) in employees.data" :key="e.id" class="border-b border-gray-200">
                <td class="p-3 text-center">{{ (employees.current_page-1)*employees.per_page + idx + 1 }}</td>
                <td class="p-3">{{ e.name }}</td>
                <td class="p-3">{{ e.gender || '-' }}</td>
                <td class="p-3">{{ e.branch || '-' }}</td>
                <td class="p-3">{{ e.shift || '-' }}</td>
                <td class="p-3">{{ e.email || '-' }}</td>
                <td class="p-3">{{ e.status || '-' }}</td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-6 text-center text-gray-500">Tidak ada karyawan</td>
            </tr>
          </tbody>
        </table>
        <Pagination v-if="employees?.data?.length" :pagination="employees" />
      </div>
    </div>
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/common/Breadcrumb.vue';
import Pagination from '@/Components/common/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  department: Object,
  employees: Object,
});

const breadcrumbs = [
  { label: 'Konfigurasi' },
  { label: 'Departemen', href: route('departments.index') },
  { label: props.department?.name || `#${props.department?.id}` },
];

const Info = {
  props: { label: String, value: [String, Number, null, Boolean] },
  template: `
    <div>
      <label class='block mb-1 text-sm text-gray-600'>{{ label }}</label>
      <div class='p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800'>{{ value ?? '-' }}</div>
    </div>
  `,
};

defineOptions({ layout: AppLayout });
</script>
