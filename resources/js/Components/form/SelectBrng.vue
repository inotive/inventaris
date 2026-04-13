<template>
    <div class="relative" ref="dropdownWrapper">
        <!-- Tombol -->
        <button
            @click="toggleDropdown"
            :disabled="props.disabled"
            type="button"
            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 flex justify-between items-center"
        >
            <span class="truncate">
                {{ selectedLabel }}
            </span>
            <svg
                class="w-4 h-4 ml-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-show="open"
            class="absolute mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-700 dark:border-gray-600 z-50"
        >
            <!-- Search -->
            <div class="p-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sky-500 dark:bg-gray-600 dark:text-white"
                />
            </div>

            <!-- List Items -->
            <ul class="max-h-24 overflow-y-auto" data-simplebar>
                <!-- Clear/Reset Option -->
                <li
                    class="px-4 py-2 cursor-pointer flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-600 border-b border-gray-200"
                    @click="clearSelection"
                >
                    <input
                        type="radio"
                        class="text-blue-600"
                        :checked="!modelValue || modelValue === null"
                        @change.stop
                    />
                    <span class="font-medium text-gray-700">{{ label }}</span>
                </li>
                <li
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="px-4 py-2 cursor-pointer flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-600"
                    @click="selectItem(item)"
                >
                    <input
                        type="radio"
                        class="text-blue-600"
                        :checked="isSelected(item)"
                        @change.stop
                    />
                    <!-- slot untuk custom render -->
                    <slot v-if="!isSuperadmin" name="item" :item="item">
                        <span>{{ item.name }}</span>
                        <!-- default fallback -->
                    </slot>
                    <slot v-else name="item" :item="item">
                        <span>{{ item.name }} ({{ item.branch?.name || '-' }})</span>
                        <!-- default fallback -->
                    </slot>
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

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    label: {
        type: String,
        default: "Pilih Item",
    },
    labelKey: { type: String, default: "name" },
    searchKey: { type: String, default: "name" },
    disabled: { type: Boolean, default: false },
    isSuperadmin: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const search = ref("");
const dropdownWrapper = ref(null);

const selectedItem = computed(() => {
    // match by id
    const byId = props.items.find((i) => i.id === props.modelValue);
    if (byId) return byId;
    // if modelValue is a string (label), match by labelKey
    if (typeof props.modelValue === "string" && props.modelValue) {
        const mv = props.modelValue.toString().toLowerCase();
        return (
            props.items.find((i) => (i[props.labelKey] || "").toString().toLowerCase() === mv) ||
            null
        );
    }
    return null;
});

const selectedLabel = computed(() => {
    if (selectedItem.value) return selectedItem.value[props.labelKey];
    if (typeof props.modelValue === "string" && props.modelValue)
        return props.modelValue;
    return props.label;
});

function getDisplayLabel(item) {
    if (props.labelKey && item[props.labelKey]) {
        if (item.type?.name) {
            return `${item[props.labelKey]} - ${item.type.name}`;
        }
        return item[props.labelKey];
    }
    return item.name || "";
}

const filteredItems = computed(() => {
    const q = (search.value || "").toLowerCase();

    return (props.items || []).filter((i) => {
        const searchField = (i[props.searchKey] || "").toString().toLowerCase();
        const labelField = getDisplayLabel(i).toLowerCase();
        return searchField.includes(q) || labelField.includes(q);
    });
});

function toggleDropdown() {
    open.value = !open.value;
}












































function closeDropdown() {
    open.value = false;
}

function selectItem(item) {
    emit("update:modelValue", item.id);
    closeDropdown();
}

function clearSelection() {
    emit("update:modelValue", null);
    closeDropdown();
}

function isSelected(item) {
    return (
        props.modelValue === item.id ||
        (typeof props.modelValue === "string" && (item[props.labelKey] || "") === props.modelValue)
    );
}

// Tutup dropdown jika klik di luar
function handleClickOutside(e) {
    if (dropdownWrapper.value && !dropdownWrapper.value.contains(e.target)) {
        closeDropdown();
    }
}

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
