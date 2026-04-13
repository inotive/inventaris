<script setup>
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
    perPageOptions: {
        type: Array,
        default: () => [10, 15, 25, 50, 100]
    }
});

const emit = defineEmits(['page-change', 'per-page-change']);

const goTo = (url) => {
    if (!url) return;

    // Extract page number from URL
    const urlObj = new URL(url);
    const page = urlObj.searchParams.get('page') || 1;

    // Emit page-change event instead of direct navigation
    emit('page-change', parseInt(page));
};

const changePerPage = (event) => {
    const newPerPage = parseInt(event.target.value);
    console.log('Pagination changePerPage called with:', newPerPage);

    // Emit event to parent component to handle the change
    emit('per-page-change', newPerPage);
};

const currentPerPage = computed(() => {
    return props.pagination.per_page || 15;
});
</script>

<template>
    <div
        class="flex items-center justify-between border-t border-white/10 px-5 py-3"
    >
        <!-- Mobile -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="goTo(pagination.prev_page_url)"
                :disabled="!pagination.prev_page_url"
                class="relative inline-flex items-center rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-white/10 disabled:opacity-50"
            >
                Previous
            </button>
            <button
                @click="goTo(pagination.next_page_url)"
                :disabled="!pagination.next_page_url"
                class="relative ml-3 inline-flex items-center rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-white/10 disabled:opacity-50"
            >
                Next
            </button>
        </div>

        <!-- Desktop -->
        <div
            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
        >
            <div class="flex flex-col gap-1 items-start">
                <div class="flex items-center gap-2">
                    <label for="per-page" class="text-sm text-gray-500">Tampilkan:</label>
                    <select
                        id="per-page"
                        :value="currentPerPage"
                        @change="changePerPage"
                        class="px-2 py-1 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="option in perPageOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </option>
                    </select>
                    <span class="text-sm text-gray-500">per halaman</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Menampilkan
                    <span class="font-medium">{{ pagination.from }}</span>
                    ke
                    <span class="font-medium">{{ pagination.to }}</span>
                    dari
                    <span class="font-medium">{{ pagination.total }}</span>
                    hasil
                </p>
            </div>
            <div>
                <nav
                    class="isolate inline-flex -space-x-px rounded-md"
                    aria-label="Pagination"
                >
                    <!-- Previous -->
                    <!-- <button
                        @click="goTo(pagination.prev_page_url)"
                        :disabled="!pagination.prev_page_url"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 inset-ring inset-ring-gray-700 hover:bg-white/5 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                    >
                        <span class="sr-only">Previous</span>
                        <svg
                            class="size-5"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            />
                        </svg>
                    </button> -->

                    <!-- Numbered Links -->
                    <template v-for="(link, i) in pagination.links" :key="i">
                        <span
                            v-if="!link || !link.url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 inset-ring inset-ring-gray-700"
                            v-html="link?.label || ''"
                        ></span>
                        <button
                            v-else
                            @click="goTo(link.url)"
                            :class="[
                                'relative inline-flex items-center rounded px-4 py-2 text-sm font-semibold focus:z-20',
                                link.active
                                    ? 'bg-blue-500 text-white focus-visible:outline-indigo-500'
                                    : 'text-gray-500 inset-ring inset-ring-gray-700 hover:bg-black/5',
                            ]"
                            v-html="link.label"
                        ></button>
                    </template>

                    <!-- Next -->
                    <!-- <button
                        @click="goTo(pagination.next_page_url)"
                        :disabled="!pagination.next_page_url"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 inset-ring inset-ring-gray-700 hover:bg-white/5 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                    >
                        <span class="sr-only">Next</span>
                        <svg
                            class="size-5"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                            />
                        </svg>
                    </button> -->
                </nav>
            </div>
        </div>
    </div>
</template>
