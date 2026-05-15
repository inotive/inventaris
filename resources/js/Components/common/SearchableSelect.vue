<template>
    <div class="searchable-select-wrapper">
        <Multiselect
            v-model="selectedValue"
            :options="options"
            :searchable="true"
            :close-on-select="true"
            :clear-on-select="false"
            :preserve-search="false"
            :placeholder="placeholder"
            :label="labelKey"
            :track-by="trackByKey"
            :loading="loading"
            :disabled="disabled"
            :allow-empty="allowEmpty"
            :show-no-results="true"
            :internal-search="true"
            @select="onSelect"
            @remove="onRemove"
            @search-change="onSearchChange"
            :class="customClass"
        >
            <template #noResult>
                <span class="text-sm text-gray-500"
                    >Tidak ada data ditemukan</span
                >
            </template>
            <template #noOptions>
                <span class="text-sm text-gray-500"
                    >Tidak ada opsi tersedia</span
                >
            </template>
        </Multiselect>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";

const props = defineProps({
    modelValue: {
        type: [String, Number, Object, Array],
        default: null,
    },
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: "Pilih...",
    },
    labelKey: {
        type: String,
        default: "name",
    },
    trackByKey: {
        type: String,
        default: "id",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    allowEmpty: {
        type: Boolean,
        default: true,
    },
    customClass: {
        type: String,
        default: "",
    },
});

const emit = defineEmits([
    "update:modelValue",
    "select",
    "remove",
    "search-change",
]);

const selectedValue = computed({
    get() {
        if (
            props.modelValue === null ||
            props.modelValue === "" ||
            props.modelValue === undefined
        ) {
            return null;
        }

        // If modelValue is an object, return it directly
        if (typeof props.modelValue === "object" && props.modelValue !== null) {
            return props.modelValue;
        }

        // If modelValue is a primitive (id), find the matching option
        const found = props.options.find(
            (option) => option[props.trackByKey] == props.modelValue,
        );
        return found || null;
    },
    set(value) {
        if (value === null || value === undefined) {
            emit("update:modelValue", "");
        } else if (typeof value === "object") {
            // Emit the id/value based on trackByKey
            emit("update:modelValue", value[props.trackByKey]);
        } else {
            emit("update:modelValue", value);
        }
    },
});

const onSelect = (selectedOption) => {
    emit("select", selectedOption);
};

const onRemove = (removedOption) => {
    emit("remove", removedOption);
};

const onSearchChange = (searchQuery) => {
    emit("search-change", searchQuery);
};
</script>

<style scoped>
.searchable-select-wrapper {
    width: 100%;
}

/* Override vue-multiselect default styles to match your design */
:deep(.multiselect) {
    min-height: 40px;
    font-size: 0.875rem;
}

:deep(.multiselect__tags) {
    min-height: 40px;
    padding: 8px 40px 0 8px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
}

:deep(.multiselect__tags:hover) {
    border-color: #9ca3af;
}

:deep(.multiselect__tags:focus-within) {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

:deep(.multiselect__input) {
    font-size: 0.875rem;
    padding: 0;
    margin-bottom: 8px;
}

:deep(.multiselect__input::placeholder) {
    color: #9ca3af;
}

:deep(.multiselect__single) {
    font-size: 0.875rem;
    margin-bottom: 8px;
    padding: 0;
    color: #1f2937;
}

:deep(.multiselect__placeholder) {
    font-size: 0.875rem;
    color: #9ca3af;
    margin-bottom: 8px;
    padding: 0;
}

:deep(.multiselect__select) {
    height: 40px;
    padding: 4px 8px;
}

:deep(.multiselect__select:before) {
    border-color: #6b7280 transparent transparent;
    border-width: 5px 5px 0;
}

:deep(.multiselect__content-wrapper) {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    margin-top: 4px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(.multiselect__content) {
    max-height: 240px;
}

:deep(.multiselect__option) {
    font-size: 0.875rem;
    padding: 10px 12px;
    min-height: 40px;
    color: #1f2937;
}

:deep(.multiselect__option--highlight) {
    background: #eff6ff;
    color: #1e40af;
}

:deep(.multiselect__option--selected) {
    background: #3b82f6;
    color: white;
    font-weight: 500;
}

:deep(.multiselect__option--selected.multiselect__option--highlight) {
    background: #2563eb;
    color: white;
}

:deep(.multiselect__spinner) {
    background: white;
}

/* Disabled state */
:deep(.multiselect--disabled) {
    opacity: 0.6;
    pointer-events: none;
}

:deep(.multiselect--disabled .multiselect__tags) {
    background: #f3f4f6;
}

/* Hide "Press enter to select" message */
:deep(.multiselect__option--highlight::after) {
    display: none !important;
}

:deep(.multiselect__option::after) {
    display: none !important;
}
</style>
