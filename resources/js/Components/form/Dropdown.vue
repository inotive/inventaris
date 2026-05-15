<script setup>
import { ref, computed, watch, nextTick } from "vue";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: "Pilih Item",
    },
    modelValue: {
        type: [String, Number, Object, null],
        default: null,
    },
});

const search = ref("");
const open = ref(false);
const selected = ref(props.modelValue);
const searchInput = ref(null);

const filteredItems = computed(() =>
    props.items.filter((item) =>
        item.name.toLowerCase().includes(search.value.toLowerCase())
    )
);

const selectItem = (item) => {
    selected.value = item;
    emit("update:modelValue", item.id); // kirim ke parent / form
    open.value = false;
};

watch(open, async (val) => {
    if (val) {
        await nextTick();
        searchInput.value?.focus(); // autofocus input saat dropdown buka
    }
});
</script>

<template>
    <div class="relative">
        <button
            @click="open = !open"
            data-dropdown-toggle="dropdownSearch"
            class="w-full rounded-lg text-sm placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-400 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 justify-between border px-4 py-2 text-center inline-flex items-center"
            type="button"
        >
            <span>{{ selected ? selected.name : label }}</span>
            <svg
                class="w-2.5 h-2.5 ms-3"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 10 6"
            >
                <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m1 1 4 4 4-4"
                />
            </svg>
        </button>

        <div
            id="dropdownSearch"
            class="z-10 w-full hidden bg-gray-100 rounded-lg shadow-sm dark:bg-gray-700"
        >
            <div class="px-5 py-3">
                <input
                    v-model="search"
                    ref="searchInput"
                    type="text"
                    class="w-full p-2 text-sm border rounded-lg text-gray-900 border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500"
                    placeholder="Search..."
                />
            </div>

            <ul
                class="h-full px-5 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200"
            >
                <li
                    v-for="item in filteredItems"
                    :key="item.id"
                    @click="selectItem(item)"
                    class="w-full py-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300"
                >
                    {{ item.name }}
                </li>
                <li
                    v-if="filteredItems.length === 0"
                    class="px-4 py-2 text-gray-500 italic"
                >
                    Tidak ada hasil
                </li>
            </ul>
        </div>
    </div>
</template>
