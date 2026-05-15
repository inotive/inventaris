<template>
    <dialog
        class="z-50 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
    >
        <div
            v-if="showSlot"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4"
            :class="{ 'px-0': maxWidth === 'full' }"
        >
            <!-- Background Overlay -->
            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 bg-gray-500 dark:bg-gray-950 opacity-75"
                    @click="close"
                />
            </transition>

            <!-- Modal Content -->
            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all w-full mx-auto"
                    :class="[maxWidthClass, maxWidth === 'full' ? 'h-screen m-0 rounded-none' : 'mb-6']"
                >
                    <!-- Header -->
                    <div
                        class="px-8 py-4 border-b flex justify-between items-center"
                    >
                        <h3 class="text-lg font-medium text-blue-500">
                            {{ title }}
                        </h3>
                        <button
                            @click="close"
                            class="text-blue-500 hover:text-blue-700 py-1 px-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-400"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Body -->
                    <div
                        class="px-8 py-4 overflow-y-auto"
                        :class="maxWidth === 'full' ? 'max-h-[calc(100vh-140px)]' : 'max-h-[70vh]'"
                        data-simplebar
                    >
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-5 flex justify-end gap-4">
                        <button
                            type="button"
                            class="px-4 py-2 w-full bg-gray-200 font-semibold text-gray-600 rounded-lg hover:bg-gray-300"
                            @click="close"
                        >
                            {{ closeText }}
                        </button>
                        <button
                            v-if="confirmText"
                            type="button"
                            class="px-4 py-2 bg-blue-500 w-full text-white rounded-lg hover:bg-blue-600"
                            @click="confirm"
                        >
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </dialog>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    maxWidth: { type: String, default: "md" },
    closeable: { type: Boolean, default: true },
    title: { type: String, default: "" },
    closeText: { type: String, default: "Batalkan" },
    confirmText: { type: String, default: "" },
});

const emit = defineEmits(["close", "confirm"]);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    (val) => {
        if (val) {
            document.body.style.overflow = "hidden";
            showSlot.value = true;
            dialog.value?.showModal();
        } else {
            document.body.style.overflow = null;
            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    }
);

const confirm = () => emit("confirm");

const close = () => {
    if (props.closeable) emit("close");
};

const closeOnEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        e.preventDefault();
        close();
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
        "3xl": "sm:max-w-3xl",
        "4xl": "sm:max-w-4xl",
        "5xl": "sm:max-w-5xl",
        "6xl": "sm:max-w-6xl",
        "7xl": "sm:max-w-7xl",
        "full": "max-w-full",
    }[props.maxWidth];
});
</script>
