<template>
    <!-- <Loader /> -->
    <ThemeProvider>
        <SidebarProvider>
            <LayoutShell>
                <FlashMessage />
                <slot />
            </LayoutShell>
        </SidebarProvider>
    </ThemeProvider>
</template>

<script setup>
import FlashMessage from "@/Components/layout/FlashMessage.vue";
import Loader from "@/Components/layout/LoadingIndicator.vue";
import ThemeProvider from "@/Components/layout/ThemeProvider.vue";
import SidebarProvider from "@/Components/layout/SidebarProvider.vue";
import LayoutShell from "@/Components/layout/LayoutShell.vue";
</script>
