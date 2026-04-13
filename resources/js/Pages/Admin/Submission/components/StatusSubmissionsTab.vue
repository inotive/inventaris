<template>
  <div>
    <div v-if="loading" class="flex justify-center p-8">
      <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div v-else>
      <div v-if="submissions.data && submissions.data.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                No
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Tanggal Pengajuan
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Karyawan
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Cabang
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Jenis
              </th>
              <th class="relative px-6 py-3">
                <span class="sr-only">Aksi</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in submissions.data" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ (submissions.current_page - 1) * submissions.per_page + index + 1 }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                {{ formatDate(item.submission_date) || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 font-medium text-gray-600 bg-gray-200 rounded-full">
                    {{ item.employee?.name?.charAt(0) || '?' }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ item.employee?.name || '-' }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ item.employee?.nip || '' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ item.branch?.name || '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ getTypeLabel(item.type) }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                <button
                  @click="$emit('show-detail', item)"
                  class="mr-4 text-blue-600 hover:text-blue-900"
                  title="Detail"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="p-8 text-center text-gray-500">
        Tidak ada data pengajuan dengan status ini
      </div>
      
      <Pagination 
        v-if="submissions.data && submissions.data.length"
        :pagination="submissions"
        @page-change="(page) => $emit('page-change', page)"
        class="border-t"
      />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Pagination from '@/Components/common/Pagination.vue';

const typeMap = {
  sick: 'Sakit',
  annual_leave: 'Cuti',
  overtime: 'Lembur',
  permission: 'Izin',
  others: 'Lain-lain'
};

const props = defineProps({
  submissions: {
    type: Object,
    required: true
  },
  status: {
    type: Number,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['show-detail', 'page-change']);
const loading = ref(false);

const getTypeLabel = (type) => {
  return typeMap[type] || type || '-';
};

const fetchSubmissions = (page = 1) => {
  loading.value = true;
  router.get(route('admin.submissions.index'), {
    ...props.filters,
    status: props.status,
    page
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    }
  });
};

watch(
  () => props.filters,
  () => {
    fetchSubmissions(1);
  },
  { deep: true }
);

function formatDate(dateString) {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
}
</script>
