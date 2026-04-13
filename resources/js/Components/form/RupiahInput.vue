<template>
    <div class="flex rounded-lg shadow-sm">
        <span
            class="inline-flex items-center px-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 text-gray-600 text-sm"
        >
            Rp.
        </span>
        <input
            type="text"
            :value="displayValue"
            @input="onInput"
            @keydown="onKeydown"
            placeholder="Masukkan nominal pembayaran"
            class="flex-1 min-w-0 block w-full rounded-r-lg text-sm placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-300 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
        />
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const formatRupiah = (n) => new Intl.NumberFormat("id-ID").format(n || 0);

const displayValue = computed(() =>
    props.modelValue ? formatRupiah(props.modelValue) : ""
);

function onInput(e) {
    // Ambil angka saja
    const raw = e.target.value.replace(/\D/g, "");
    const num = raw ? parseInt(raw, 10) : "";
    emit("update:modelValue", num);
}

function onKeydown(e) {
    // Hanya izinkan angka, backspace, delete, tab, arrow
    if (
        !/[0-9]/.test(e.key) &&
        !["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab"].includes(
            e.key
        )
    ) {
        e.preventDefault();
    }
}
</script>
