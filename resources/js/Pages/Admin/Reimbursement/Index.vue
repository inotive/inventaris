<template>
  <div class="px-3 h-full flex flex-col gap-3 overflow-hidden">
    <Head title="Reimbursement" />
    <div class="flex items-center justify-between h-10">
      <Breadcrumb :items="[{label:'Reimbursement'}]" />
      <div class="flex gap-2">
        <button class="px-3 py-2 text-sm text-white bg-blue-600 rounded" @click="openCreate">Ajukan Reimbursement</button>
      </div>
    </div>

    <div class="flex flex-col bg-white border border-gray-200 rounded-lg overflow-hidden">
      <div class="flex items-center justify-between px-6 py-3 border-b">
        <div class="text-xl font-semibold text-gray-800">Daftar Reimbursement</div>
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
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="text-left font-medium text-gray-600">Judul</div></th>
              <th class="p-3 bg-gray-100 border border-gray-200"><div class="text-left font-medium text-gray-600">Gambar</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Dibuat Oleh</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Dibuat</div></th>
              <th class="p-3 bg-gray-100 border-y border-gray-200"><div class="text-center font-medium text-gray-600">Aksi</div></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rows.data && rows.data.length" v-for="(row, idx) in rows.data" :key="row.id" class="border-b border-gray-200">
              <td class="p-3 text-center">{{ (rows.current_page - 1) * rows.per_page + idx + 1 }}</td>
              <td class="p-3">{{ row.title }}</td>
              <td class="p-3">
                <img v-if="row.img_url" :src="row.img_url" alt="gambar" class="h-10 rounded" />
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="p-3 text-center">{{ row.user?.name || '-' }}</td>
              <td class="p-3 text-center">{{ formatDate(row.created_at) }}</td>
              <td class="p-3">
                <div class="flex gap-2 justify-center">
                  <button class="p-1 text-yellow-500 hover:text-yellow-700" @click="openEdit(row)">Edit</button>
                  <button class="p-1 text-red-500 hover:text-red-700" @click="confirmDelete(row.id)">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-else>
              <td colspan="6" class="py-6 text-center text-gray-500">Tidak ada data</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="rows.data && rows.data.length" :pagination="rows" class="border-t" />
    </div>

    <!-- Modal -->
    <Dialog v-model:visible="showModal" modal dismissableMask :style="{ width: '520px', padding:'6px' }">
      <template #header>
        <div class="flex items-center justify-between w-full">
          <h3 class="text-xl font-semibold text-gray-900">{{ form.id ? 'Edit' : 'Tambah' }} Pengumuman</h3>
        </div>
      </template>
      <div class="flex flex-col gap-3">
        <div>
          <label class="block mb-1 text-sm text-gray-600">Judul</label>
          <input v-model="form.title" type="text" class="w-full h-10 px-3 rounded border border-gray-300" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="block mb-1 text-sm text-gray-600">Gambar (opsional)</label>
          <div class="flex items-center gap-3">
            <input type="file" accept="image/*" @change="onFileChange" class="w-full h-10 px-3 rounded border border-gray-300" />
            <button v-if="previewUrl || form.img_url" type="button" class="px-2 h-10 text-xs rounded border" @click="clearImage">Hapus</button>
          </div>
          <p v-if="form.errors.img_file" class="mt-1 text-xs text-red-600">{{ form.errors.img_file }}</p>
          <div v-if="previewUrl || form.img_url" class="mt-2">
            <img :src="previewUrl || form.img_url" alt="preview" class="h-24 rounded border" />
          </div>
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
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import Pagination from '@/Components/common/Pagination.vue'
import Dialog from 'primevue/dialog'

defineOptions({ layout: AppLayout })

const props = defineProps({ announcements: Object, q: String })
const rows = computed(() => props.announcements || { data: [] })
const qLocal = ref(props.q || '')

let t = null
watch(qLocal, () => { clearTimeout(t); t = setTimeout(() => fetchList(), 350) })

function fetchList(){ router.get(route('announcements.index'), { q: qLocal.value }, { preserveScroll: true, preserveState: true, replace: true }) }

const showModal = ref(false)
const form = useForm({ id: null, title: '', img_file: null, created_by: null })
const previewUrl = ref('')

function openCreate(){
  form.defaults({ id: null, title: '', created_by: null })
  form.reset(); form.clearErrors(); form.img_file = null; previewUrl.value = ''; showModal.value = true
}
function openEdit(row){
  form.defaults({ id: row.id, title: row.title, created_by: row.created_by || null })
  form.reset(); form.clearErrors(); Object.assign(form, { id: row.id, title: row.title, created_by: row.created_by || null }); form.img_file = null; previewUrl.value = row.img_url || ''; showModal.value = true
}

function submit(){
  const options = { preserveScroll: true, forceFormData: true, onSuccess: () => { showModal.value = false } }
  if(form.id){
    form.put(route('announcements.update', form.id), options)
  } else {
    form.post(route('announcements.store'), options)
  }
}

function confirmDelete(id){
  if(!id) return;
  if(!confirm('Yakin hapus pengumuman ini?')) return;
  router.delete(route('announcements.destroy', id), {
    onSuccess: () => {},
    onError: (errors) => { alert(errors?.message || 'Gagal menghapus data.'); }
  })
}

function formatDate(d){ if(!d) return '-'; return new Date(d).toLocaleString('id-ID') }

function onFileChange(e){
  const f = e?.target?.files?.[0]
  if(!f) return
  form.img_file = f
  previewUrl.value = URL.createObjectURL(f)
}

function clearImage(){
  form.img_file = null
  previewUrl.value = ''
}
</script>
