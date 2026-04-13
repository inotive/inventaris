<template>
  <div class="px-3 h-full flex flex-col gap-3 overflow-hidden">
    <Head title="Tata Tertib" />
    <div class="flex items-center justify-between h-10">
      <Breadcrumb :items="[{label:'Tata Tertib'}]" />
      <div class="flex gap-2">
        <button v-if="can('compliance.create')" class="px-3 py-2 text-sm text-white bg-blue-600 rounded" @click="openCreate">Tambah Tata Tertib</button>
      </div>
    </div>

    <div class="flex flex-col bg-white border border-gray-200 rounded-lg overflow-hidden">
      <div class="flex items-center justify-between px-6 py-3 border-b">
        <div class="text-xl font-semibold text-gray-800">Daftar Tata Tertib</div>
        <div class="relative">
          <input v-model="qLocal" type="text" placeholder="Cari..." class="h-10 w-64 rounded-lg border border-gray-200 bg-transparent py-2.5 pl-3 pr-8 text-sm text-gray-800 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
          <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
        </div>
      </div>

      <div class="overflow-auto" data-simplebar>
        <table class="min-w-full text-sm">
          <thead>
            <tr>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">No</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Kategori</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="text-left font-medium text-gray-600">Judul</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="text-left font-medium text-gray-600">Deskripsi</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Dibuat Oleh</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Dibuat</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Aksi</div></th>
            </tr>
          </thead>
          <tbody>
            <template v-if="rows.data && rows.data.length">
              <tr v-for="(row, idx) in rows.data" :key="row.id" class="border-b border-gray-200">
                <td class="p-3 text-center">{{ (rows.current_page - 1) * rows.per_page + idx + 1 }}</td>
                <td class="p-3 text-center">
                    <span class="inline-block px-2 py-0.5 text-xs text-blue-700 bg-blue-100 rounded-full border border-blue-300">
                        {{ row.section ? row.section.charAt(0).toUpperCase() + row.section.slice(1) : '-' }}
                    </span>
                </td>
                <td class="p-3">{{ row.title }}</td>
                <td class="p-3">
                  <div class="max-w-xs truncate" :title="row.description">{{ row.description || '-' }}</div>
                </td>
                <td class="p-3 text-center">{{ row.user?.name || '-' }}</td>
                <td class="p-3 text-center">{{ formatDate(row.created_at) }}</td>
                <td class="p-3">
                  <div class="flex gap-2 justify-center">
                    <button v-if="can('compliance.edit')" class="p-1 text-yellow-500 hover:text-yellow-700" @click="openEdit(row)">Edit</button>
                    <button v-if="can('compliance.delete')" class="p-1 text-red-500 hover:text-red-700" @click="openConfirmModal(row)">Hapus</button>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-6 text-center text-gray-500">Tidak ada data</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="rows.data && rows.data.length" :pagination="rows" class="border-t" @page-changed="changePage" @per-page-changed="perPageChanged" />
    </div>

    <!-- Modal -->
    <Dialog v-model:visible="showModal" modal dismissableMask :style="{ width: '520px', padding:'6px' }">
      <template #header>
        <div class="flex items-center justify-between w-full">
          <h3 class="text-xl font-semibold text-gray-900">{{ form.id ? 'Edit' : 'Tambah' }} Tata Tertib</h3>
        </div>
      </template>
      <div class="flex flex-col gap-3">
        <div>
          <label class="block mb-1 text-sm text-gray-600">Kategori</label>
          <select v-model="form.section" class="w-full h-10 px-3 rounded border border-gray-300">
            <option value="">Pilih Kategori</option>
            <option value="hak">Hak & Kewajiban</option>
            <option value="larangan">Larangan</option>
          </select>
          <p v-if="form.errors.section" class="mt-1 text-xs text-red-600">{{ form.errors.section }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Judul</label>
          <input v-model="form.title" type="text" class="w-full h-10 px-3 rounded border border-gray-300" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Deskripsi (opsional)</label>
          <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 rounded border border-gray-300"></textarea>
          <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
        </div>
        <div class="flex justify-end gap-2">
          <button class="px-3 h-10 rounded border" type="button" @click="showModal=false">Batal</button>
          <button :disabled="form.processing" class="px-3 h-10 text-white bg-blue-600 rounded disabled:opacity-60" type="button" @click="submit">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>
      </div>
    </Dialog>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="isConfirmModalOpen"
      :question="`Yakin ingin menghapus`"
      :selected="`${selectedItem?.title || ''}`"
      title="Hapus Tata Tertib"
      confirmText="Ya, Hapus!"
      maxWidth="md"
      @close="closeConfirmModal"
      @confirm="destroyData"
    />
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import Pagination from '@/Components/common/Pagination.vue'
import Dialog from 'primevue/dialog'
import ConfirmModal from '@/Components/common/ConfirmModal.vue'
import { useAuth } from '@/Composables/useAuth'

const { can } = useAuth()

defineOptions({ layout: AppLayout })

const props = defineProps({ compliances: Object, q: String })
const rows = computed(() => props.compliances || { data: [] })
const qLocal = ref(props.q || '')

let t = null
watch(qLocal, () => { clearTimeout(t); t = setTimeout(() => fetchList(), 350) })

function fetchList(){ router.get(route('compliance.index'), { q: qLocal.value }, { preserveScroll: true, preserveState: true, replace: true }) }

const showModal = ref(false)
const form = useForm({ id: null, section: '', title: '', description: '', created_by: null })

// Confirm delete modal state
const selectedItem = ref(null)
const isConfirmModalOpen = ref(false)

function openCreate(){
  form.defaults({ id: null, section: '', title: '', description: '', created_by: null })
  form.reset(); form.clearErrors(); showModal.value = true
}
function openEdit(row){
  form.defaults({ id: row.id, section: row.section || '', title: row.title, description: row.description || '', created_by: row.created_by || null })
  form.reset(); form.clearErrors(); Object.assign(form, { id: row.id, section: row.section || '', title: row.title, description: row.description || '', created_by: row.created_by || null }); showModal.value = true
}

function submit(){
  const options = { preserveScroll: true, onSuccess: () => { showModal.value = false } }
  if(form.id){
    form.put(route('compliance.update', form.id), {
      preserveScroll: true,
      onSuccess: () => { showModal.value = false },
      onError: (errors) => {
        console.log(errors);
      }
    });
  } else {
    form.post(route('compliance.store'), options)
  }
}

function openConfirmModal(item){
  if(!item) return;
  selectedItem.value = item;
  isConfirmModalOpen.value = true;
}

function closeConfirmModal(){
  selectedItem.value = null;
  isConfirmModalOpen.value = false;
}

function destroyData(){
  if(!selectedItem.value?.id) return;

  router.delete(route('compliance.destroy', selectedItem.value.id), {
    onSuccess: () => {
      closeConfirmModal();
    },
    onError: (errors) => {
      alert(errors?.message || 'Gagal menghapus data.');
    },
    preserveScroll: true,
  })
}

function formatDate(d){ if(!d) return '-'; return new Date(d).toLocaleString('id-ID') }

function changePage(page) {
    router.get(route('compliance.index'), { q: qLocal.value, page: page }, { preserveScroll: true, preserveState: true, replace: true });
}

function perPageChanged(perPage) {
    router.get(route('compliance.index'), { q: qLocal.value, per_page: perPage }, { preserveScroll: true, preserveState: true, replace: true });
}
</script>
