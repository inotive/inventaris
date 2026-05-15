<template>
    <div class="relative z-0 w-full group">
        <input
            v-model="value"
            :type="type"
            class="block px-0 py-2.5 w-full text-sm placeholder-transparent placeholder-gray-300 text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none peer focus:border-blue-400 focus:placeholder-gray-400 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-400"
            :placeholder="placeholder"
        />
        <label
            class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-85 transform text-sm text-gray-700 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-400 rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-400"
        >
            {{ label }}
        </label>
        <p v-if="error" class="mt-1 text-xs text-rose-600">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed } from "vue";

// Props
const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: {
        type: String,
        default: "Masukkan teks",
    },
    type: {
        type: String,
        default: "text",
    },
    error: {
        type: String,
        default: "",
    },
});

const label = computed(() => props.label ?? props.placeholder);

// Emit
const emit = defineEmits(["update:modelValue"]);

// v-model binding
const value = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});
</script>
