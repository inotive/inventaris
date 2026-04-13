<template>
    <div
        :class="classes"
        :style="{ width: sizePx, height: sizePx }"
        :title="name"
    >
        <span class="leading-none" :style="{ fontSize: fontSizePx }">{{
            initials
        }}</span>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    name: { type: String, default: "" },
    gender: { type: String, default: "" }, // 'Laki-laki' | 'Perempuan' | ''
    size: { type: Number, default: 32 }, // px
    class: { type: String, default: "" },
    colorClass: { type: String, default: "" }, // e.g., 'bg-indigo-500 text-white'
});

const customClass = computed(() => props.class);

const initials = computed(() => {
    const parts = (props.name || "").trim().split(/\s+/).slice(0, 2);
    if (!parts.length) return "?";
    return parts
        .map((p) => p.charAt(0))
        .join("")
        .toUpperCase();
});

const isMale = computed(() => props.gender === "Laki-laki");
const isFemale = computed(() => props.gender === "Perempuan");

const bgClass = computed(() => {
    if (isMale.value) return "bg-blue-100";
    if (isFemale.value) return "bg-pink-100";
    return "bg-gray-200";
});
const textClass = computed(() => {
    if (isMale.value) return "text-blue-700";
    if (isFemale.value) return "text-pink-700";
    return "text-gray-700";
});

const classes = computed(() => {
    const base =
        "inline-flex select-none items-center justify-center rounded-full font-medium";
    if (props.colorClass) return [base, props.colorClass, customClass.value];
    return [base, bgClass.value, textClass.value, customClass.value];
});

const sizePx = computed(() => `${props.size}px`);
const fontSizePx = computed(
    () => `${Math.max(10, Math.round(props.size * 0.45))}px`
);
</script>
