<template>
  <Head :title="`Detail Cabang #${branch?.id}`" />
  <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
    <div class="flex items-center justify-between h-10">
      <Breadcrumb :items="breadcrumbs" />
      <Link :href="route('branches.index')" class="px-3 py-2 text-sm text-gray-700 rounded border">Kembali</Link>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Info Cabang {{ branch?.name }}</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
        <Info label="Nama Cabang" :value="branch?.name || '-'" />
        <Info label="Nomor Telepon" :value="branch?.phone || branch?.contact || '-'" />
        <Info label="Email" :value="branch?.email || '-'" />
        <Info label="Region" :value="branch?.region || '-'" />
        <Info label="Jumlah Pegawai" :value="branch?.employees_count ?? '-'" />
        <div class="md:col-span-2">
          <label class="block mb-1 text-sm text-gray-600">Alamat</label>
          <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.address || '-' }}</div>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <label class="block mb-1 text-sm text-gray-600">Region</label>
            <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.region || '-' }}</div>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block mb-1 text-sm text-gray-600">Email</label>
            <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.email || '-' }}</div>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block mb-1 text-sm text-gray-600">Nomor Telepon</label>
            <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.phone || branch?.contact || '-' }}</div>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block mb-1 text-sm text-gray-600">Jumlah Pegawai</label>
            <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.employees_count ?? '-' }}</div>
          </div>
        </div>
        <div class="md:col-span-2">
          <label class="block mb-1 text-sm text-gray-600">Deskripsi</label>
          <div class="p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800">{{ branch?.description || '-' }}</div>
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
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Departemen</div></th>
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
                <td class="p-3">{{ e.department || '-' }}</td>
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
        <Pagination
          v-if="employees?.data?.length"
          :pagination="employees"
        />
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
  branch: Object,
  employees: Object,
});

const breadcrumbs = [
  { label: 'Konfigurasi' },
  { label: 'Kantor Cabang', href: route('branches.index') },
  { label: props.branch?.name || `#${props.branch?.id}` },
];

const Info = {
  props: { label: String, value: [String, Number, null] },
  template: `
    <div>
      <label class='block mb-1 text-sm text-gray-600'>{{ label }}</label>
      <div class='p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800'>{{ value ?? '-' }}</div>
    </div>
  `,
};

defineOptions({ layout: AppLayout });
</script>
