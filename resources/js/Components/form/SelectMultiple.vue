<template>
    <div class="relative" ref="dropdownWrapper">
        <!-- Tombol -->
        <button
            :id="buttonId"
            @click="toggleDropdown"
            :disabled="props.disabled"
            type="button"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 flex justify-between items-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{ 'border-red-500': props.error }"
        >
            <span class="truncate text-left">
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
            ref="dropdownElement"
            class="fixed bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-gray-700 dark:border-gray-600 z-[99999]"
            :style="dropdownStyle"
        >
            <!-- Search -->
            <div class="p-3 border-b border-gray-100 dark:border-gray-600">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:bg-gray-600 dark:text-white dark:border-gray-500"
                    @click.stop
                    @keydown.stop
                />
            </div>

            <!-- List Items -->
            <ul class="max-h-60 overflow-y-auto py-1" data-simplebar>
                <!-- Clear/Reset Option -->
                <li
                    class="px-4 py-3 cursor-pointer flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150 border-b border-gray-100 dark:border-gray-600"
                    @click="clearSelection"
                >
                    <div
                        class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors duration-150"
                        :class="
                            selectedItems.length === filteredItems.length && filteredItems.length > 0
                                ? 'border-blue-600 bg-blue-600'
                                : selectedItems.length > 0
                                ? 'border-blue-600 bg-blue-600'
                                : 'border-gray-300 dark:border-gray-500'
                        "
                    >
                        <svg
                            v-if="selectedItems.length === filteredItems.length && filteredItems.length > 0"
                            class="w-3 h-3 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <div
                            v-else-if="selectedItems.length > 0 && selectedItems.length < filteredItems.length"
                            class="w-2 h-2 bg-blue-600 rounded"
                        ></div>
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ label }}</span>
                </li>

                <li
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="px-4 py-3 cursor-pointer flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': isSelected(item) }"
                    @click="toggleItem(item)"
                >
                    <div
                        class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors duration-150"
                        :class="isSelected(item)
                            ? 'border-blue-600 bg-blue-600'
                            : 'border-gray-300 dark:border-gray-500'"
                    >
                        <svg
                            v-if="isSelected(item)"
                            class="w-3 h-3 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                    <!-- slot untuk custom render -->
                    <slot name="item" :item="item">
                        <span class="text-gray-900 dark:text-gray-100">{{ item.name }}</span>
                        <span v-if="item.department || item.branch" class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                            ({{ [item.department?.name, item.branch?.name].filter(Boolean).join(' - ') }})
                        </span>
                    </slot>
                </li>
                <li
                    v-if="filteredItems.length === 0"
                    class="px-4 py-6 text-center text-gray-500 dark:text-gray-400"
                >
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
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from "vue";

const buttonId = `select-multiple-btn-${Math.random().toString(36).substr(2, 9)}`;

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: "Pilih Item",
    },
    labelKey: { type: String, default: "name" },
    searchKey: { type: String, default: "name" },
    disabled: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const search = ref("");
const dropdownWrapper = ref(null);
const dropdownElement = ref(null);
const dropdownStyle = ref({});

const selectedItems = computed(() => {
    if (!props.modelValue || !Array.isArray(props.modelValue)) return [];
    return props.items.filter(item =>
        props.modelValue.includes(item.id) ||
        props.modelValue.some(val => typeof val === 'object' && val?.id === item.id)
    );
});

const selectedLabel = computed(() => {
    if (selectedItems.value.length === 0) {
        return props.label;
    }
    if (selectedItems.value.length === 1) {
        return selectedItems.value[0][props.labelKey] || selectedItems.value[0].name;
    }
    return `${selectedItems.value.length} item dipilih`;
});

function getDisplayLabel(item) {
    if (props.labelKey && item[props.labelKey]) {
        if (item.department?.name || item.branch?.name) {
            const dept = item.department?.name || '';
            const branch = item.branch?.name || '';
            return `${item[props.labelKey]} (${[dept, branch].filter(Boolean).join(' - ')})`;
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

function toggleDropdown(e) {
    // Prevent event from bubbling to document click handler
    if (e) {
        e.stopPropagation();
    }
    if (!open.value) {
        // Use nextTick to ensure DOM is ready
        setTimeout(() => calculateDropdownPosition(), 0);
    }
    open.value = !open.value;
}

function calculateDropdownPosition() {
    if (!dropdownWrapper.value || !open.value) return;

    const rect = dropdownWrapper.value.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // Calculate actual dropdown height based on content
    // Search input: ~60px (padding + border)
    // Each item: ~48px (py-3 = 12px top + 12px bottom + ~24px content)
    // Empty state: ~80px
    const searchHeight = 60;
    const itemHeight = 48;
    const emptyStateHeight = 80;
    const padding = 8; // py-1 = 4px top + 4px bottom

    let contentHeight = searchHeight + padding;

    // Add height for "Select All" option
    contentHeight += itemHeight;

    if (filteredItems.value.length > 0) {
        contentHeight += filteredItems.value.length * itemHeight;
    } else {
        contentHeight += emptyStateHeight;
    }

    // Use max-h-60 (240px) as maximum, but use actual height if smaller
    const maxHeight = 310;
    const actualHeight = Math.min(contentHeight, maxHeight);
    const dropdownHeight = actualHeight + 16; // Add some margin for rounded corners

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
        maxHeight: `${maxHeight}px`
    };
}

function closeDropdown() {
    open.value = false;
}

function isSelected(item) {
    if (!props.modelValue || !Array.isArray(props.modelValue)) return false;
    return props.modelValue.includes(item.id) ||
           props.modelValue.some(val => typeof val === 'object' && val?.id === item.id);
}

function toggleItem(item) {
    const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const itemId = item.id;

    if (isSelected(item)) {
        // Remove item
        const newValue = currentValue.filter(val => {
            if (typeof val === 'object') {
                return val?.id !== itemId;
            }
            return val !== itemId;
        });
        emit("update:modelValue", newValue);
    } else {
        // Add item
        const newValue = [...currentValue, itemId];
        emit("update:modelValue", newValue);
    }
}

function clearSelection() {
    emit("update:modelValue", []);
}

// Tutup dropdown jika klik di luar
function handleClickOutside(e) {
    // Don't close if dropdown is not open
    if (!open.value) return;

    // Check if click is inside dropdownWrapper (button) or dropdownElement (dropdown menu)
    const isClickInsideWrapper = dropdownWrapper.value && dropdownWrapper.value.contains(e.target);
    const isClickInsideDropdown = dropdownElement.value && dropdownElement.value.contains(e.target);

    // Only close if click is outside both wrapper and dropdown
    if (!isClickInsideWrapper && !isClickInsideDropdown) {
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
    // Use capture phase to catch events before they reach modal handlers
    document.addEventListener("click", handleClickOutside, true);
    document.addEventListener("mousedown", handleClickOutside, true);
    window.addEventListener("resize", calculateDropdownPosition);
    window.addEventListener("scroll", handleScroll, true); // Use capture phase
    // Also listen to scroll on the document body
    document.body.addEventListener("scroll", handleScroll, true);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside, true);
    document.removeEventListener("mousedown", handleClickOutside, true);
    window.removeEventListener("resize", calculateDropdownPosition);
    window.removeEventListener("scroll", handleScroll, true);
    document.body.removeEventListener("scroll", handleScroll, true);
});

// Watch for changes in filteredItems to recalculate position
watch([filteredItems], () => {
    if (open.value) {
        nextTick(() => {
            calculateDropdownPosition();
        });
    }
});

// Watch for search changes to recalculate position
watch(search, () => {
    if (open.value) {
        nextTick(() => {
            calculateDropdownPosition();
        });
    }
});
</script>

