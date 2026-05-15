<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import ConfirmModal from '@/Components/common/ConfirmModal.vue'
import Pagination from '@/Components/common/Pagination.vue'
import PlusSquareIcon from '@/Components/icons/PlusSquareIcon.vue'
import EditIcon from '@/Components/icons/EditIcon.vue'
import TrashIcon from '@/Components/icons/TrashIcon.vue'
import SearchIcon from '@/Components/icons/SearchIcon.vue'
import UpIcon from '@/Components/icons/UpIcon.vue'
import DownIcon from '@/Components/icons/DownIcon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { useAuth } from '@/Composables/useAuth'

const { can } = useAuth()

const props = defineProps({
  categories: Object,
  sortBy: String,
  sortDirection: String,
  search: String,
})

const breadcrumbs = [{ label: 'Konfigurasi' }, { label: 'Kategori Checklist' }]
const search = ref(props.search || '')

let timeout = null
watch(search, (value) => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get(
      route('checklist-categories.index'),
      {
        search: value,
        sortBy: props.sortBy,
        sortDirection: props.sortDirection,
      },
      { preserveScroll: true, preserveState: true, replace: true }
    )
  }, 400)
})

function changeSort(column) {
  let direction = 'asc'
  if (props.sortBy === column && props.sortDirection === 'asc') {
    direction = 'desc'
  }
  router.get(
    route('checklist-categories.index'),
    { search: search.value, sortBy: column, sortDirection: direction },
    { preserveState: true, preserveScroll: true }
  )
}

const selectedItem = ref(null)
const isConfirmModalOpen = ref(false)
const openConfirmModal = (item) => {
  if (!can('checklist_categories.delete')) return
  selectedItem.value = item
  isConfirmModalOpen.value = true
}
const closeConfirmModal = () => {
  isConfirmModalOpen.value = false
  selectedItem.value = null
}
const deleteData = () => {
  if (!can('checklist_categories.delete')) return
  router.delete(route('checklist-categories.destroy', selectedItem.value.id), {
    onSuccess: () => closeConfirmModal(),
    preserveScroll: true,
  })
}

// kontrol tampilan kolom aksi + colspan baris kosong
const showActions = computed(
  () => can('checklist_categories.edit') || can('checklist_categories.delete')
)
const emptyColspan = computed(() => (showActions.value ? 5 : 4))

defineOptions({ layout: AppLayout })
</script>

<template>
  <Head title="Kategori Checklist" />
  <div class="flex flex-col h-full gap-3 px-4">
    <div class="flex items-center justify-between">
      <Breadcrumb :items="breadcrumbs" />
      <Link
        v-if="can('checklist_categories.create')"
        :href="route('checklist-categories.create')"
        class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded"
      >
        <PlusSquareIcon class="w-4 h-4" />
        <span class="hidden text-sm md:block">Tambah Kategori</span>
      </Link>
    </div>

    <div
      class="h-[90%] grid-cols-12 gap-3 md:gap-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
    >
      <div
        class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
          Daftar Kategori Checklist
        </div>
        <div class="flex items-center gap-3">
          <form class="relative py-2" @submit.prevent>
            <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
              <SearchIcon class="text-gray-400" />
            </button>
            <input
              v-model="search"
              type="text"
              placeholder="Cari kategori"
              class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
            />
          </form>
        </div>
      </div>

      <div class="w-full overflow-auto" data-simplebar>
        <table class="min-w-full overflow-auto text-sm">
          <thead>
            <tr>
              <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                <div class="flex items-center justify-center">
                  <p class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">No.</p>
                </div>
              </th>
              <th @click="changeSort('code')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                <div class="flex items-center justify-center gap-2">
                  <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Kode</p>
                  <div class="flex flex-col items-center">
                    <UpIcon :class="['-mb-1', sortBy === 'code' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                    <DownIcon :class="['-mt-1', sortBy === 'code' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                  </div>
                </div>
              </th>
              <th @click="changeSort('name')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                <div class="flex items-center justify-center gap-2">
                  <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Nama</p>
                  <div class="flex flex-col items-center">
                    <UpIcon :class="['-mb-1', sortBy === 'name' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                    <DownIcon :class="['-mt-1', sortBy === 'name' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                  </div>
                </div>
              </th>
              <th class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                <div class="flex items-center justify-center gap-2">
                  <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Deskripsi</p>
                </div>
              </th>
              <th
                v-if="showActions"
                class="py-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
              >
                <div class="flex items-center justify-center">
                  <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Aksi</p>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-if="categories.data && categories.data.length > 0"
              v-for="(c, index) in categories.data"
              :key="c.id"
              class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                <div class="flex items-center justify-center px-3 whitespace-nowrap">
                  <p class="text-gray-500 dark:text-gray-400">
                    {{ (categories.current_page - 1) * categories.per_page + index + 1 }}.
                  </p>
                </div>
              </td>
              <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center whitespace-nowrap">
                  <div class="flex flex-col px-8 leading-tight">
                    <p class="font-mono text-gray-800 dark:text-white/90">{{ c.code }}</p>
                  </div>
                </div>
              </td>
              <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center whitespace-nowrap">
                  <div class="flex flex-col px-8 leading-tight">
                    <p class="font-medium text-gray-800 dark:text-white/90">{{ c.name }}</p>
                  </div>
                </div>
              </td>
              <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center whitespace-nowrap">
                  <div class="flex flex-col px-8 leading-tight">
                    <p class="text-gray-600 dark:text-gray-400">{{ c.description || '-' }}</p>
                  </div>
                </div>
              </td>
              <td
                v-if="showActions"
                class="py-2.5 border-gray-200 border-y dark:border-gray-600"
              >
                <div class="flex justify-center gap-3 px-4 whitespace-nowrap sm:px-0">
                  <Link
                    v-if="can('checklist_categories.edit')"
                    :href="route('checklist-categories.edit', c.id)"
                    title="Edit"
                  >
                    <EditIcon class="text-yellow-500" />
                  </Link>
                  <button
                    v-if="can('checklist_categories.delete')"
                    @click="openConfirmModal(c)"
                    title="Hapus"
                  >
                    <TrashIcon class="text-red-500" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-else>
              <td :colspan="emptyColspan" class="py-6 font-medium text-center text-gray-500 dark:text-gray-400">
                Tidak ada data ditemukan
              </td>
            </tr>
          </tbody>
        </table>

        <ConfirmModal
          :show="isConfirmModalOpen"
          :question="`Yakin ingin menghapus '${selectedItem?.name}' ?`"
          title="Hapus Kategori"
          confirmText="Ya, Hapus!"
          maxWidth="md"
          @close="closeConfirmModal"
          @confirm="deleteData"
        />
      </div>

      <Pagination v-if="categories.data && categories.data.length > 0" :pagination="categories" />
    </div>
  </div>
</template>

<style scoped></style>
