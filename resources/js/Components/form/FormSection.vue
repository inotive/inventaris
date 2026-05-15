<template>
  <section class="rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-700 dark:bg-white/[0.03]">
    <header class="flex items-start justify-between gap-3 px-4 py-3 md:px-6 md:py-4 border-b border-gray-200 dark:border-gray-700">
      <div>
        <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
        <p v-if="description" class="mt-0.5 text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
      </div>
      <slot name="actions" />
    </header>
    <div class="p-4 md:p-6">
      <slot />
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: null },
});
</script>
