<template>
  <div :class="wrapperClass">
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  columns: { type: Number, default: 2 },
  gap: { type: String, default: 'gap-4 md:gap-5' },
});

const wrapperClass = computed(() => {
  const base = 'grid grid-cols-1';
  const mdCols = props.columns === 3 ? 'md:grid-cols-3' : 'md:grid-cols-2';
  return `${base} ${mdCols} ${props.gap}`;
});
</script>
