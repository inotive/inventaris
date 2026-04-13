<template>
    <div class="relative" ref="dropdownWrapper">
        <!-- Tombol -->
        <button
            :id="buttonId"
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

        <!-- Dropdown with Teleport -->
        <teleport to="body">
        <div
            v-show="open"
            :style="dropdownStyle"
            class="fixed mt-2 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-700 dark:border-gray-600 z-[9999]"
        >
            <!-- Search -->
            <div class="p-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari..."
                    @keydown.enter.prevent="onEnterCreate"
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
                    v-if="taggable && canCreate"
                    class="px-4 py-2 cursor-pointer flex items-center gap-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-600"
                    @click="createTag"
                >
                    Tambah: "{{ search }}"



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
                    <slot name="item" :item="item">
                        <span>{{ item.name }}</span>
                        <!-- default fallback -->
                    </slot>
                </li>
                <li
                    v-if="!taggable && filteredItems.length === 0"
                    class="px-4 py-2 text-gray-500 italic"
                >
                    Tidak ada hasil





                </li>
            </ul>
        </div>
        </teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from "vue";

const buttonId = `select-btn-${Math.random().toString(36).substr(2, 9)}`;

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
    // optional key to persist added options across page reloads
    persistKey: { type: String, default: null },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const search = ref("");
const dropdownWrapper = ref(null);
const dropdownStyle = ref({});
// locally added items (e.g., when user presses Enter or clicks "Tambah:")
const localAdded = ref([]);


const selectedItem = computed(() => {
    // match by id
    const byId = props.items.find((i) => i.id === props.modelValue) || localAdded.value.find((i) => i.id === props.modelValue);
    if (byId) return byId;
    // if modelValue is a string (label), match by labelKey
    if (typeof props.modelValue === "string" && props.modelValue) {
        const mv = props.modelValue.toString().toLowerCase();
        return (
            props.items.find((i) => (i[props.labelKey] || "").toString().toLowerCase() === mv) ||
            localAdded.value.find((i) => (i[props.labelKey] || "").toString().toLowerCase() === mv) ||
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
    // Merge localAdded and props.items, dedupe by label (case-insensitive) then by id
    const map = new Map();
    const pushUnique = (src) => {
        src.forEach((i) => {
            const key = (i[props.labelKey] || "").toString().toLowerCase();
            if (!map.has(key)) map.set(key, i);
        });
    };
    pushUnique(localAdded.value);
    pushUnique(props.items || []);
    const merged = Array.from(map.values());

    return merged.filter((i) => {
        const searchField = (i[props.searchKey] || "").toString().toLowerCase();
        const labelField = getDisplayLabel(i).toLowerCase();
        return searchField.includes(q) || labelField.includes(q);
    });
});

function toggleDropdown() {
    open.value = !open.value;
    if (open.value) {
        nextTick(() => {
            updateDropdownPosition();
        });
    }
}

function updateDropdownPosition() {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    const rect = button.getBoundingClientRect();
    const dropdownHeight = 200; // approximate height
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    
    // Determine if dropdown should open upward
    const openUpward = spaceBelow < dropdownHeight && spaceAbove > spaceBelow;
    
    if (openUpward) {
        dropdownStyle.value = {
            top: `${rect.top - dropdownHeight}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    } else {
        dropdownStyle.value = {
            top: `${rect.bottom + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    }
}












































function closeDropdown() {
    open.value = false;
}

function selectItem(item) {
    // if item comes from local list and parent expects string, still send id so model can store uniquely
    // but to maintain backward compat, if modelValue is currently a string, send label back
    if (typeof props.modelValue === "string") {
        emit("update:modelValue", item.id);
    } else {
        emit("update:modelValue", item.id);
    }
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
    window.addEventListener("scroll", updateDropdownPosition, true);
    window.addEventListener("resize", updateDropdownPosition);
    // hydrate local from storage if available
    if (props.persistKey) {
        try {
            const raw = localStorage.getItem(`select_local_${props.persistKey}`);
            if (raw) localAdded.value = JSON.parse(raw);
        } catch {}
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("scroll", updateDropdownPosition, true);
    window.removeEventListener("resize", updateDropdownPosition);
    // persist on unmount
    if (props.persistKey) {
        try { localStorage.setItem(`select_local_${props.persistKey}`, JSON.stringify(localAdded.value)); } catch {}
    }
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
    const label = (search.value || "").toString();
    // avoid duplicates by label (case-insensitive)
    const exists = (arr) => arr.some((i) => (i[props.labelKey] || "").toString().toLowerCase() === label.toLowerCase());
    if (!exists(localAdded.value) && !exists(props.items || [])) {
        // create a local item with generated id to avoid clashing ids
        console.log(localAdded.value);
        const localItem = { id: `${label}`, [props.labelKey]: label };
        localAdded.value.unshift(localItem);
        if (props.persistKey) {
            try { localStorage.setItem(`select_local_${props.persistKey}`, JSON.stringify(localAdded.value)); } catch {}
        }
    }
    // set model to label string so parent can decide how to persist
    emit("update:modelValue", label);
    closeDropdown();
}

function onEnterCreate() {
    if (canCreate.value) createTag();
}
</script>
