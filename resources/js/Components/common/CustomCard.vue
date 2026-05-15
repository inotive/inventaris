<script setup lang="ts">
import { defineProps } from 'vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="bg-white rounded-lg border border-gray-200">
        <div v-if="title" class="flex justify-between items-center px-4 py-3 border-b">
            <h2 class="text-xl font-semibold text-gray-800">{{ title }}</h2>
        </div>
        <div class="p-4">
            <slot />
        </div>
    </div>
</template>
