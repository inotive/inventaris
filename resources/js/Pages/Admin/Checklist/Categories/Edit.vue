<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  category: { type: Object, required: true },
})

const breadcrumbs = [
  { label: 'Konfigurasi' },
  { label: 'Kategori Checklist' },
  { label: 'Ubah' },
]

const form = useForm({
  name: props.category.name || '',
  code: props.category.code || '',
  description: props.category.description || '',
})

const submit = () => {
  form.put(route('checklist-categories.update', props.category.id))
}

defineOptions({ layout: AppLayout })
</script>

<template>
  <Head title="Ubah Kategori Checklist" />
  <div class="px-6 py-6">
    <div class="flex justify-between items-center mb-4">
      <Breadcrumb :items="breadcrumbs" />
      <Link :href="route('checklist-categories.index')" as="button" class="inline-flex items-center px-3 py-2 rounded-md p-button p-component">
        <i class="mr-2 lni lni-chevron-left" /> Kembali
      </Link>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Informasi Kategori</h2>
      </div>
      <form class="p-4 space-y-4" @submit.prevent="submit">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block mb-1 text-sm text-gray-600">Nama</label>
            <input v-model="form.name" type="text" placeholder="Nama kategori" class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
            <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-600">Kode</label>
            <input v-model="form.code" type="text" placeholder="Kode unik" class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
            <div v-if="form.errors.code" class="text-sm text-red-500">{{ form.errors.code }}</div>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 text-sm text-gray-600">Deskripsi</label>
            <textarea v-model="form.description" rows="3" placeholder="Deskripsi (opsional)" class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
            <div v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</div>
          </div>
        </div>
        <div class="flex gap-2 justify-end pt-2">
          <Link :href="route('checklist-categories.index')" as="button" class="px-3 py-2 rounded-md border">Batal</Link>
          <button type="submit" class="px-3 py-2 rounded-md text-white bg-blue-600" :disabled="form.processing">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</template>
