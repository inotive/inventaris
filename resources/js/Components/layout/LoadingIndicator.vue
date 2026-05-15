<template>
  <div
    v-if="isPageLoading"
    class="fixed inset-0 bg-white z-40 flex items-center justify-center"
  >
    <div class="w-16 h-16 border-4 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
  </div>
</template>

<script setup>
import { isPageLoading } from '@/Composables/loading'

</script>

<style scoped>
</style>
