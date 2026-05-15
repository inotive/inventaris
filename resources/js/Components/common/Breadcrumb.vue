<template>
    <nav
        class="flex px-2 text-gray-500 dark:text-gray-300 text-sm sm:text-base space-x-1.5 font-semibold"
    >
        <template v-for="(item, index) in items" :key="index">
            <span v-if="index > 0">/</span>
            <template v-if="item.href">
                <Link
                    :href="item.href"
                    class="hover:text-blue-500 hover:underline"
                >
                    {{ item.label }}
                </Link>
            </template>
            <template v-else>
                <span
                    :class="[index === items.length - 1 ? 'text-sky-500' : '']"
                    >{{ item.label }}</span
                >
            </template>
        </template>
    </nav>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>
