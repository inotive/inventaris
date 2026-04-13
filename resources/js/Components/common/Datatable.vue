<template>
    <div class="p-6">
        <div
            class="flex justify-between items-center py-4"
            v-if="safeConfig.showHeader"
        >
            <h1 class="text-2xl font-semibold">{{ safeConfig.title }}</h1>
            <div class="relative">
                <span class="flex absolute inset-y-0 left-0 items-center pl-2">
                    <svg
                        class="w-4 h-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="8"
                            stroke="currentColor"
                            stroke-width="2"
                            fill="none"
                        />
                        <line
                            x1="21"
                            y1="21"
                            x2="16.65"
                            y2="16.65"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                    </svg>
                </span>
                <div class="flex gap-2 justify-between">
                    <input
                        :value="safeConfig.search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Search..."
                        class="p-2 pl-8 text-sm text-gray-500 bg-gray-50 rounded-xl border border-gray-300"
                    />
                    <div>
                        <slot name="addFilter" />
                    </div>
                </div>
            </div>
        </div>
        <div class="datatable-body">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="(column, index) in safeConfig.columns"
                            :key="index"
                            scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase cursor-pointer"
                            @click="changeSort(column)"
                            :style="column.style || ''"
                        >
                            {{ column.label }}
                            <span v-if="column.sortable">
                                <svg
                                    v-if="sortKey === column.key && sortAsc"
                                    class="inline ml-1 w-3 h-3 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M5 15l7-7 7 7" />
                                </svg>
                                <svg
                                    v-else-if="
                                        sortKey === column.key && !sortAsc
                                    "
                                    class="inline ml-1 w-3 h-3 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg
                                    v-else
                                    class="inline ml-1 w-3 h-3 text-gray-300"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M5 15l7-7 7 7" />
                                </svg>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <slot name="filterHeader" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="safeConfig.loading">
                        <td
                            :colspan="safeConfig.columns.length"
                            class="px-6 py-4 text-center whitespace-nowrap"
                        >
                            <div
                                class="flex justify-center items-center space-x-2"
                            >
                                <svg
                                    class="w-5 h-5 text-gray-400 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                                <span>Loading...</span>
                            </div>
                        </td>
                    </tr>
                    <template v-else>
                        <slot name="body" />
                    </template>
                    <tr
                        v-if="
                            !safeConfig.loading && safeConfig.data.length === 0
                        "
                    >
                        <td
                            :colspan="safeConfig.columns.length"
                            class="px-6 py-4 text-center whitespace-nowrap"
                        >
                            <div
                                class="flex flex-col justify-center items-center"
                            >
                                <NotFound />
                                <h1 class="text-2xl font-semibold">
                                    Tidak ada data
                                </h1>
                                <p class="text-gray-500">
                                    Data yang Anda cari tidak tersedia dalam
                                    sistem.
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <DataTablePagination
                v-if="!safeConfig.loading && safeConfig.data.length > 0"
                :per-page="safeConfig.perPage"
                :total-items="safeConfig.totalItems"
                :current-page="safeConfig.currentPage"
                @page-changed="(page) => emit('page-changed', page)"
                @per-page-changed="(perPage) => perPageChanged(perPage)"
            />
        </div>
    </div>
</template>
<script setup>
import { ref, watch, computed } from "vue";
import NotFound from "@/Components/icons/SubmissionIcons/NotFound.vue";
import _ from "lodash";
import DataTablePagination from "@/Components/common/DataTablePagination.vue";

const props = defineProps({
    config: {
        showHeader: {
            type: Boolean,
            default: true,
        },
        perPage: {
            type: Number,
            default: 10,
        },
        search: {
            type: String,
            default: "",
        },
        title: {
            type: String,
            default: "Data Table",
        },
        columns: {
            type: Array,
            required: true,
        },
        data: {
            type: Array,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        totalItems: {
            type: Number,
            required: true,
        },
        currentPage: {
            type: Number,
            default: 1,
        },
    },
});
// Build a safe config to avoid runtime errors if parent passes nothing/partial
const safeConfig = computed(() => {
    const c = props.config || {};
    return {
        showHeader: c.showHeader ?? true,
        perPage: c.perPage ?? 10,
        search: c.search ?? "",
        title: c.title ?? "Data Table",
        columns: Array.isArray(c.columns) ? c.columns : [],
        data: Array.isArray(c.data) ? c.data : [],
        loading: c.loading ?? false,
        totalItems: Number.isFinite(c.totalItems) ? c.totalItems : 0,
        currentPage: c.currentPage ?? 1,
    };
});
const sortKey = ref("");
const sortAsc = ref(true);
const changeSort = (column) => {
    if (!column.sortable) return;
    if (sortKey.value === column.key) {
        sortAsc.value = !sortAsc.value;
    } else {
        sortKey.value = column.key;
        sortAsc.value = true;
    }
    emit("sort-change", { key: sortKey.value, asc: sortAsc.value });
};

const perPageChanged = (newPerPage) => {
    emit("per-page-changed", newPerPage);
};

const emit = defineEmits([
    "sort-change",
    "search-change",
    "page-changed",
    "per-page-changed",
]);
watch(
    () => safeConfig.value.search,
    (newVal) => {
        emit("search-change", newVal);
    }
);

const debouncedSearch = _.debounce((value) => {
    emit("search-change", value);
}, 400);

const onSearchInput = (event) => {
    debouncedSearch(event.target.value);
};
</script>
<style scoped></style>
