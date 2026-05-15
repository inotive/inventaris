<script setup>
import { computed } from 'vue'

const props = defineProps({
    perPage: { type: Number, required: true },
    totalItems: { type: Number, required: true },
    currentPage: { type: Number, default: 1 },
    maxButtons: { type: Number, default: 4 },
    perPageOptions: { type: Array, default: () => [10, 20, 50, 100] }
})

const emit = defineEmits(['page-changed', 'per-page-changed'])

const totalPages = computed(() =>
    Math.max(1, Math.ceil(props.totalItems / props.perPage))
)

const from = computed(() => {
    if (props.totalItems === 0) return 0
    return (props.currentPage - 1) * props.perPage + 1
})

const to = computed(() =>
    Math.min(props.totalItems, props.currentPage * props.perPage)
)

function goTo(page) {
    if (page < 1 || page > totalPages.value || page === props.currentPage) return
    emit('page-changed', page)
}

function changePerPage(e) {
    emit('per-page-changed', Number(e.target.value))
}

const pageList = computed(() => {
    const total = totalPages.value
    const cur = props.currentPage
    const max = Math.max(3, props.maxButtons)
    if (total <= max) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }

    const middleWindow = max - 2
    let start = Math.max(2, cur - Math.floor((middleWindow - 1) / 2))
    let end = Math.min(total - 1, start + middleWindow - 1)
    start = Math.max(2, end - middleWindow + 1)

    const pages = [1]
    if (start > 2) pages.push('…')
    for (let p = start; p <= end; p++) pages.push(p)
    if (end < total - 1) pages.push('…')
    pages.push(total)
    return pages
})
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-t border-white/10 px-5 py-3">
        <!-- Select per page -->
        <div>
            <label class="mr-2 text-sm text-gray-400">Show</label>
            <select
                :value="perPage"
                @change="changePerPage"
                class="rounded border border-white/10 bg-white/5 px-2 py-1 text-sm text-gray-200"
            >
                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span class="ml-2 text-sm text-gray-400">per page</span>
        </div>

        <!-- Mobile: Prev / Next -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="goTo(currentPage - 1)"
                :disabled="currentPage <= 1"
                class="relative inline-flex items-center rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-white/10 disabled:opacity-50"
            >
                Previous
            </button>
            <button
                @click="goTo(currentPage + 1)"
                :disabled="currentPage >= totalPages"
                class="relative ml-3 inline-flex items-center rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-white/10 disabled:opacity-50"
            >
                Next
            </button>
        </div>

        <!-- Desktop -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-end gap-6">
            <!-- Info 1-10 of 52 -->
            <div class="text-sm text-gray-500 flex items-center">
            {{ from }}-{{ to }} of {{ totalItems }}
            </div>

            <div>
            <nav class="isolate inline-flex -space-x-px rounded-md" aria-label="Pagination">
                <!-- Previous -->
                <button
                @click="goTo(currentPage - 1)"
                :disabled="currentPage <= 1"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 inset-ring inset-ring-gray-700 hover:bg-white/5 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                >
                <span class="sr-only">Previous</span>
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" />
                </svg>
                </button>

                <!-- Numbered Links -->
                <template v-for="(p, i) in pageList" :key="i">
                <span
                    v-if="typeof p === 'string'"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 inset-ring inset-ring-gray-700"
                >
                    {{ p }}
                </span>

                <button
                    v-else
                    @click="goTo(p)"
                    :class="[
                    'relative inline-flex items-center rounded px-4 py-2 text-sm font-semibold focus:z-20',
                    p === currentPage
                        ? 'bg-blue-500 text-white focus-visible:outline-indigo-500'
                        : 'text-gray-500 inset-ring inset-ring-gray-700 hover:bg-black/5',
                    ]"
                >
                    {{ p }}
                </button>
                </template>

                <!-- Next -->
                <button
                @click="goTo(currentPage + 1)"
                :disabled="currentPage >= totalPages"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 inset-ring inset-ring-gray-700 hover:bg-white/5 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                >
                <span class="sr-only">Next</span>
                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" />
                </svg>
                </button>
            </nav>
            </div>
        </div>
    </div>
</template>
