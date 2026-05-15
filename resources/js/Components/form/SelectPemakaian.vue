<template>
    <div class="relative" ref="dropdownWrapper">
        <!-- Tombol -->
        <button @click="toggleDropdown" :disabled="props.disabled" type="button"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 flex justify-between items-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="truncate text-left">
                {{ selectedLabel }}
            </span>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div v-show="open"
            class="fixed bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-gray-700 dark:border-gray-600 z-[99999]"
            :style="dropdownStyle">
            <!-- Search -->
            <div class="p-3 border-b border-gray-100 dark:border-gray-600">
                <input v-model="search" type="text" placeholder="Cari..." @keydown.enter.prevent="onEnterCreate"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-600 dark:text-white dark:border-gray-500" />
            </div>

            <!-- List Items -->
            <ul class="max-h-60 overflow-y-auto py-1" data-simplebar>
                <li v-if="taggable && canCreate"
                    class="px-4 py-3 cursor-pointer flex items-center gap-3 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-600 transition-colors duration-150"
                    @click="createTag">
                    <div class="w-4 h-4 rounded-full border-2 border-blue-600 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                    </div>
                    <span class="font-medium">Tambah: "{{ search }}"</span>
                </li>
                <li v-for="item in filteredItems" :key="item.id"
                    class="px-4 py-3 cursor-pointer flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': isSelected(item) }" @click="selectItem(item)">
                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors duration-150"
                        :class="isSelected(item)
                            ? 'border-blue-600 bg-blue-600'
                            : 'border-gray-300 dark:border-gray-500'">
                        <div v-if="isSelected(item)" class="w-2 h-2 rounded-full bg-white"></div>
                    </div>
                    <!-- slot untuk custom render -->
                    <slot name="item" :item="item">
                        <span class="text-gray-900 dark:text-gray-100">{{ item.name }}</span>
                        <!-- default fallback -->
                    </slot>
                </li>
                <li v-if="!taggable && filteredItems.length === 0"
                    class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="text-sm">Tidak ada hasil</span>
                    </div>
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
    taggable: {
        type: Boolean,
        default: false,
    },
    labelKey: { type: String, default: "name" },
    searchKey: { type: String, default: "name" },
    disabled: { type: Boolean, default: false },
});


const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const search = ref("");
const dropdownWrapper = ref(null);
const dropdownStyle = ref({});

const selectedItem = computed(
    () => props.items.find((i) => i.id === props.modelValue) || null
);

const selectedLabel = computed(() => {
    if (selectedItem.value) {
        return getDisplayLabel(selectedItem.value);
    }
    if (typeof props.modelValue === "string" && props.modelValue)
        return props.modelValue;
    return props.label;
});

function getDisplayLabel(item) {
    if (props.labelKey && item[props.labelKey]) {
        if (item.type?.name) {
            return `${item[props.labelKey]} - ${item.type.name}`;
        }
        if (item.branch?.name) {
            return `${item[props.labelKey]} - ${item.branch.name}`;
        }
        return item[props.labelKey];
    }
    return item.name || "";
}

const filteredItems = computed(() => {
    const q = (search.value || "").toLowerCase();
    return props.items.filter((i) => {
        const searchField = (i[props.searchKey] || "").toString().toLowerCase();
        const labelField = getDisplayLabel(i).toLowerCase();
        return searchField.includes(q) || labelField.includes(q);
    });
});

function toggleDropdown() {
    if (!open.value) {
        // Use nextTick to ensure DOM is ready
        setTimeout(() => calculateDropdownPosition(), 0);
    }
    open.value = !open.value;
}

function calculateDropdownPosition() {
    if (!dropdownWrapper.value || !open.value) return;

    const rect = dropdownWrapper.value.getBoundingClientRect();
    const dropdownHeight = 310; // max-h-60 = 240px
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // Calculate position
    let top = rect.bottom + 8; // 8px margin
    let left = rect.left;

    // Calculate optimal width
    const minWidth = rect.width;
    const maxWidth = Math.min(viewportWidth - 32, 400); // 16px margin on each side, max 400px
    const optimalWidth = Math.max(minWidth, maxWidth);

    // Check if dropdown goes beyond viewport bottom
    if (top + dropdownHeight > viewportHeight) {
        // Show above the input if it fits better
        const spaceAbove = rect.top;
        const spaceBelow = viewportHeight - rect.bottom;

        if (spaceAbove > spaceBelow) {
            top = rect.top - dropdownHeight - 8;
        } else {
            // Otherwise, constrain to viewport
            top = viewportHeight - dropdownHeight - 8;
        }
    }

    // Ensure dropdown doesn't go above viewport
    if (top < 8) {
        top = 8;
    }

    // Check if dropdown goes beyond viewport right
    if (left + optimalWidth > viewportWidth) {
        left = viewportWidth - optimalWidth - 16; // 16px margin from right
    }

    // Ensure minimum left position
    if (left < 16) {
        left = 16;
    }

    dropdownStyle.value = {
        top: `${top}px`,
        left: `${left}px`,
        minWidth: `${minWidth}px`,
        width: `${optimalWidth}px`,
        maxWidth: `${maxWidth}px`,
        maxHeight: `${dropdownHeight}px`
    };
}

function closeDropdown() {
    open.value = false;
}

function selectItem(item) {
    emit("update:modelValue", item.id);
    closeDropdown();
}

function isSelected(item) {
    return props.modelValue === item.id;
}

// Tutup dropdown jika klik di luar
function handleClickOutside(e) {
    if (dropdownWrapper.value && !dropdownWrapper.value.contains(e.target)) {
        closeDropdown();
    }
}

// Handle scroll on all parent containers
function handleScroll() {
    if (open.value) {
        calculateDropdownPosition();
    }
}

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    window.addEventListener("resize", calculateDropdownPosition);
    window.addEventListener("scroll", handleScroll, true); // Use capture phase
    // Also listen to scroll on the document body
    document.body.addEventListener("scroll", handleScroll, true);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("resize", calculateDropdownPosition);
    window.removeEventListener("scroll", handleScroll, true);
    document.body.removeEventListener("scroll", handleScroll, true);
});

// Taggable helpers
const existsExact = computed(() =>
    props.items.some(
        (i) =>
            (i[props.searchKey] || "").toString().toLowerCase() ===
            (search.value || "").toLowerCase()
    )
);

const canCreate = computed(
    () => props.taggable && !!search.value && !existsExact.value
);

function createTag() {
    if (!canCreate.value) return;
    emit("update:modelValue", search.value);
    closeDropdown();
}

function onEnterCreate() {
    if (canCreate.value) createTag();
}
</script>
