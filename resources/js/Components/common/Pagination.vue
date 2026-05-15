<script setup>
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
    perPageOptions: {
        type: Array,
        default: () => [10, 15, 25, 50, 100],
    },
    clientSide: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['page-changed', 'per-page-changed']);

const goTo = (url) => {
    if (!url) return;
    
    // If client-side pagination, emit event instead of router.get
    if (props.clientSide) {
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const pageNum = parseInt(urlParams.get('page') || '1');
        emit('page-changed', pageNum);
        return;
    }
    
    router.get(
        url,
        {},
        { preserveScroll: true, preserveState: true, replace: true }
    );
};

const changePerPage = (event) => {
    const newPerPage = parseInt(event.target.value);
    
    // If client-side pagination, emit event
    if (props.clientSide) {
        emit('per-page-changed', newPerPage);
        emit('page-changed', 1); // Reset to page 1
        return;
    }
    
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set("per_page", newPerPage);
    currentUrl.searchParams.delete("page"); // Reset ke halaman pertama saat ganti per page

    router.get(
        currentUrl.pathname + currentUrl.search,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
};

const currentPerPage = computed(() => {
    return props.pagination.per_page || 10;
});
</script>

<template>
    <div class="flex justify-between items-center px-5 py-3">
        <!-- Mobile -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="goTo(pagination.prev_page_url)"
                :disabled="!pagination.prev_page_url"
                class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
            >
                Sebelumnya
            </button>
            <button
                @click="goTo(pagination.next_page_url)"
                :disabled="!pagination.next_page_url"
                class="inline-flex relative items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
            >
                Berikutnya
            </button>
        </div>

        <!-- Desktop -->
        <div
            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
        >
            <div class="flex gap-4 items-center">
                <div class="flex gap-2 items-center">
                    <label
                        for="per-page"
                        class="text-sm text-gray-700 whitespace-nowrap"
                        >Tampilkan</label
                    >
                    <select
                        id="per-page"
                        :value="currentPerPage"
                        @change="changePerPage"
                        class="px-3 py-1.5 text-sm bg-white rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="option in perPageOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </option>
                    </select>
                    <span class="text-sm text-gray-700 whitespace-nowrap"
                        >per halaman</span
                    >
                </div>
                <!-- <p class="text-sm text-gray-600">
                    Menampilkan
                    <span class="font-medium text-gray-900">{{ pagination.from }}</span>
                    ke
                    <span class="font-medium text-gray-900">{{ pagination.to }}</span>
                    dari
                    <span class="font-medium text-gray-900">{{ pagination.total }}</span>
                    hasil
                </p> -->
            </div>
            <div>
                <nav
                    class="inline-flex isolate -space-x-px rounded-md"
                    aria-label="Pagination"
                >
                    <!-- Numbered Links -->
                    <template v-for="(link, i) in pagination.links" :key="i">
                        <span
                            v-if="!link.url"
                            class="inline-flex relative items-center px-4 py-2 text-sm font-semibold text-gray-400"
                            v-html="link.label"
                        ></span>
                        <button
                            v-else
                            @click="goTo(link.url)"
                            :class="[
                                'relative inline-flex items-center rounded px-4 py-2 text-sm font-semibold focus:z-20',
                                link.active
                                    ? 'bg-blue-500 text-white'
                                    : 'text-gray-700 border border-gray-300 hover:bg-gray-50',
                            ]"
                            v-html="link.label"
                        ></button>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
