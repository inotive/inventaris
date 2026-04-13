<template>
    <div class="relative w-full">
        <label v-if="label" :for="id" class="block mb-1 text-sm font-medium text-gray-900 dark:text-gray-300">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>

        <div
            class="flex flex-wrap items-center gap-2 p-2 w-full bg-white rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <!-- Selected Tags -->
            <div v-for="(tag, index) in modelValue" :key="index"
                class="flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded dark:bg-blue-900 dark:text-blue-300">
                {{ tag }}
                <button type="button" @click="removeTag(index)"
                    class="ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 focus:outline-none">
                    &times;
                </button>
            </div>

            <!-- Input Field -->
            <input :id="id" ref="inputRef" v-model="inputValue" type="text"
                class="flex-1 min-w-[120px] bg-transparent border-none outline-none text-sm text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:ring-0"
                :placeholder="modelValue.length > 0 ? '' : placeholder" @keydown.enter.prevent="addTag"
                @keydown.backspace="handleBackspace" @focus="showSuggestions = true" @blur="handleBlur"
                autocomplete="off" />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mt-1 text-sm text-red-500">
            {{ error }}
        </div>

        <!-- Suggestions Dropdown -->
        <ul v-if="showSuggestions && filteredSuggestions.length > 0"
            class="absolute z-10 w-full mt-1 max-h-48 overflow-auto bg-white rounded-md shadow-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
            <li v-for="(suggestion, index) in filteredSuggestions" :key="index"
                @mousedown.prevent="selectSuggestion(suggestion)"
                class="px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ displayKey && typeof suggestion === 'object' ? suggestion[displayKey] : suggestion }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    suggestions: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => 'tag-input-' + Math.random().toString(36).substr(2, 9),
    },
    placeholder: {
        type: String,
        default: 'Add tags...',
    },
    required: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    displayKey: {
        type: String,
        default: '',
    }
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref('');
const showSuggestions = ref(false);
const inputRef = ref(null);

const filteredSuggestions = computed(() => {
    const query = (inputValue.value || '').toLowerCase();
    return props.suggestions.filter(s => {
        if (!s) return false;

        let val = s;
        if (props.displayKey && typeof s === 'object') {
            val = s[props.displayKey];
        }

        if (typeof val !== 'string') return false;

        return val.toLowerCase().includes(query) && !props.modelValue.includes(val);
    });
});

function addTag() {
    const val = inputValue.value.trim();
    if (val && !props.modelValue.includes(val)) {
        emit('update:modelValue', [...props.modelValue, val]);
        inputValue.value = '';
    }
    // Don't clear if duplicate to let user know? Or just clear.
    // Standard behavior: clear.
    inputValue.value = '';
}

function removeTag(index) {
    const newTags = [...props.modelValue];
    newTags.splice(index, 1);
    emit('update:modelValue', newTags);
}

function handleBackspace(e) {
    if (inputValue.value === '' && props.modelValue.length > 0) {
        removeTag(props.modelValue.length - 1);
    }
}

function selectSuggestion(suggestion) {
    let val = suggestion;
    if (props.displayKey && typeof suggestion === 'object') {
        val = suggestion[props.displayKey];
    }

    if (!props.modelValue.includes(val)) {
        emit('update:modelValue', [...props.modelValue, val]);
    }
    inputValue.value = '';
    showSuggestions.value = false;
    // Keep focus
    if (inputRef.value) inputRef.value.focus();
}

function handleBlur() {
    // Delay hiding to allow click event on suggestion to fire
    setTimeout(() => {
        showSuggestions.value = false;
        // Optionally add current text as tag on blur?
        // User might expect this behavior.
        if (inputValue.value.trim()) {
            addTag();
        }
    }, 200);
}
</script>
