<template>
    <span class="inline-flex flex-col ml-1 align-middle">
        <svg
            :class="[
                'w-3 h-3 -mb-1',
                active && direction === 'asc'
                    ? 'text-blue-600 dark:text-blue-400'
                    : 'text-gray-400 dark:text-gray-600',
            ]"
            fill="currentColor"
            viewBox="0 0 20 20"
        >
            <path
                d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
            />
        </svg>
        <svg
            :class="[
                'w-3 h-3 -mt-1',
                active && direction === 'desc'
                    ? 'text-blue-600 dark:text-blue-400'
                    : 'text-gray-400 dark:text-gray-600',
            ]"
            fill="currentColor"
            viewBox="0 0 20 20"
        >
            <path
                d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"
            />
        </svg>
    </span>
</template>

<script setup>
defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    direction: {
        type: String,
        default: 'asc',
        validator: (value) => ['asc', 'desc'].includes(value),
    },
});
</script>
