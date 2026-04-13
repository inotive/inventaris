<template>
    <Link href="/" class="lg:hidden flex items-center gap-2">
        <div
            class="flex justify-center items-center w-[45px] h-[45px] font-bold text-blue-600 bg-blue-50 rounded-xl"
        >
            SP
        </div>
        <div class="flex flex-col leading-tight">
            <div class="text-blue-600 font-bold tracking-wider">
                Sistem
            </div>
            <div class="text-sky-500 italic text-sm">Penyimpanan</div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
</script>
