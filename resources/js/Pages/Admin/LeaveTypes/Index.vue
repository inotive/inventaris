<template>
  <div class="flex flex-col gap-3 px-3 h-full overflow-hidden">
    <Head title="Jenis Izin" />
    <div class="flex justify-between items-center h-10">
      <Breadcrumb :items="[{ label: 'Konfigurasi' }, { label: 'Jenis Izin' }]" />
      <button v-if="can('leave_types.create')" type="button" class="px-3 py-2 text-sm text-white bg-blue-600 rounded" @click="openCreate">
        Tambah Jenis Izin
      </button>
    </div>

    <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
      <div class="flex items-center justify-between px-6 py-3 border-b">
        <div class="text-xl font-semibold text-gray-800">Daftar Jenis Izin</div>
        <div class="relative">
          <input v-model="local.search" type="text" placeholder="Cari..." class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
          <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
        </div>
      </div>

      <div class="overflow-auto" data-simplebar>
        <table class="min-w-full text-sm">
          <thead>
            <tr>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="font-medium text-center text-gray-600">No</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Nama</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Kategori</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-center text-gray-600">Jatah/Tahun</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="font-medium text-left text-gray-600">Deskripsi</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="font-medium text-center text-gray-600">Aksi</div></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rows.data && rows.data.length" v-for="(r, i) in rows.data" :key="r.id" class="border-b border-gray-200">
              <td class="p-3 text-center">{{ (rows.current_page - 1) * rows.per_page + i + 1 }}</td>
              <td class="p-3">{{ r.name }}</td>
              <td class="p-3">{{ categoryLabel(r.category) }}</td>
              <td class="p-3 text-center">{{ r.leave_quota_per_year }}</td>
              <td class="p-3">{{ r.description || '-' }}</td>
              <td class="p-3">
                <div class="flex gap-2 justify-center">
                  <button v-if="can('leave_types.edit')" class="px-2 py-1 text-xs rounded border" @click="openEdit(r)">Edit</button>
                  <button v-if="can('leave_types.delete')" class="px-2 py-1 text-xs text-white bg-rose-600 rounded border border-rose-700" @click="destroyOne(r.id)">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-else>
              <td colspan="6" class="py-6 text-center text-gray-500">Tidak ada data</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="rows.data && rows.data.length" :pagination="rows" class="border-t" @page-changed="changePage" @per-page-changed="perPageChanged" />
    </div>

    <Dialog v-model:visible="showModal" modal dismissableMask :style="{ width: '520px', padding: '6px' }">
      <template #header>
        <div class="flex justify-between items-center w-full">
          <h3 class="text-xl font-semibold text-gray-900">{{ form.id ? 'Edit' : 'Tambah' }} Jenis Izin</h3>
        </div>
      </template>
      <div class="flex flex-col gap-3">
        <div>
          <label class="block mb-1 text-sm text-gray-600">Nama</label>
          <input v-model="form.name" type="text" class="px-3 w-full h-10 rounded border border-gray-300" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Kategori</label>
          <select v-model="form.category" class="px-3 w-full h-10 rounded border border-gray-300">
            <option value="annual_leave">Cuti Tahunan</option>
            <option value="sick_leave">Sakit</option>
            <option value="special_leave">Khusus</option>
          </select>
          <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Jatah Cuti/Tahun</label>
          <input v-model.number="form.leave_quota_per_year" type="number" min="0" step="1" class="px-3 w-full h-10 rounded border border-gray-300" />
          <p v-if="form.errors.leave_quota_per_year" class="mt-1 text-xs text-red-600">{{ form.errors.leave_quota_per_year }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Deskripsi</label>
          <textarea v-model="form.description" rows="3" class="px-3 w-full rounded border border-gray-300"></textarea>
          <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
        </div>
        <div class="flex gap-2 justify-end">
          <button class="px-3 h-10 rounded border" type="button" @click="showModal=false">Batal</button>
          <button class="px-3 h-10 text-white bg-blue-600 rounded" type="button" :disabled="form.processing" @click="submit">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import Pagination from '@/Components/common/Pagination.vue'
import Dialog from 'primevue/dialog'
import { useAuth } from '@/Composables/useAuth'

const { can } = useAuth()

defineOptions({ layout: AppLayout })

const props = defineProps({ leaveTypes: Object, filters: Object })
const rows = computed(() => props.leaveTypes || { data: [] })

const local = ref({ search: props.filters?.search || '' })
watch(() => local.value.search, () => {
  clearTimeout(t)
  t = setTimeout(fetchList, 350)
})
let t = null

function fetchList () {
  router.get(route('leave-types.index'), { search: local.value.search, category: props.filters?.category || '' }, { preserveScroll: true, preserveState: true, replace: true })
}

function categoryLabel (v) {
  return v === 'annual_leave' ? 'Cuti Tahunan' : v === 'sick_leave' ? 'Sakit' : v === 'special_leave' ? 'Khusus' : v
}

const showModal = ref(false)
const form = useForm({ id: null, name: '', category: 'annual_leave', leave_quota_per_year: 0, description: '' })

function openCreate () {
  form.defaults({ id: null, name: '', category: 'annual_leave', leave_quota_per_year: 0, description: '' })
  form.reset(); form.clearErrors();
  showModal.value = true
}
function openEdit (row) {
  form.defaults({ id: row.id, name: row.name, category: row.category, leave_quota_per_year: row.leave_quota_per_year, description: row.description || '' })
  form.reset(); form.clearErrors();
  Object.assign(form, { id: row.id, name: row.name, category: row.category, leave_quota_per_year: row.leave_quota_per_year, description: row.description || '' })
  showModal.value = true
}
function submit () {
  const opts = { preserveScroll: true, onSuccess: () => { showModal.value = false } }
  if (form.id) form.put(route('leave-types.update', form.id), opts)
  else form.post(route('leave-types.store'), opts)
}
function destroyOne (id) {
  if (!id) return
  if (!confirm('Yakin ingin menghapus Jenis Izin ini?')) return
  router.delete(route('leave-types.destroy', id), { preserveScroll: true })
}

function changePage(page) {
  router.get(route('leave-types.index'), { search: local.value.search, category: props.filters?.category || '', page: page }, { preserveScroll: true, preserveState: true, replace: true })
}

function perPageChanged(perPage) {
  router.get(route('leave-types.index'), { search: local.value.search, category: props.filters?.category || '', per_page: perPage }, { preserveScroll: true, preserveState: true, replace: true })
}
</script>
