<template>
    <div class="flex h-screen dark:bg-gray-900">
        <AppSidebar />
        <Backdrop />
        <div
            class="flex flex-col h-full w-full bg-gray-100 dark:border-gray-800 dark:bg-gray-950 overflow-hidden"
            :class="[
                isExpanded || isHovered ? 'lg:ml-[250px]' : 'lg:ml-[115px]',
            ]"
        >
            <AppHeader />

            <!-- Scrollable page content -->
            <div class="flex-1 overflow-hidden">
                <div
                    data-simplebar
                    class="h-full p-5 overflow-y-auto no-scrollbar"
                >
                    <slot />
                </div>
            </div>

            <!-- Footer stays at the bottom of the layout, outside scroll area -->
            <AppFooter />
        </div>
    </div>
</template>

<script setup>
import AppSidebar from "@/Components/layout/AppSidebar.vue";
import Backdrop from "@/Components/layout/Backdrop.vue";
import AppHeader from "@/Components/layout/AppHeader.vue";
import AppFooter from "@/Components/layout/AppFooter.vue";
import { useSidebar } from "@/Composables/useSidebar";
import { onMounted } from "vue";
import { initFlowbite } from "flowbite";

// initialize components based on data attribute selectors
onMounted(() => {
    initFlowbite();
});

const { isExpanded, isHovered } = useSidebar();
</script>

<style>
.no-scrollbar {
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE & Edge lama */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari */
}
</style>
