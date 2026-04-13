<template>
    <div class="min-h-screen">
        <main>
            <FlashMessage />
            <slot />
        </main>
    </div>
</template>

<script setup>
import FlashMessage from "@/Components/layout/FlashMessage.vue";
</script>
