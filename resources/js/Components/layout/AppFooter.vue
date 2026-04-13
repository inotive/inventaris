<!-- Footer.vue -->
<template>
    <footer class="p-4">
        <div
            class="container flex flex-col gap-2 justify-between items-center mx-auto text-sm md:flex-row"
        >
            <div
                class="flex order-2 gap-1 items-center text-gray-700 md:order-1 dark:text-gray-300"
            >
                <span class="text-gray-500"
                    >{{ new Date().getFullYear() }} &copy;</span
                >
                <Link
                    href="/"
                    class="font-bold text-blue-600 hover:text-blue-700"
                >
                    Sistem Penyimpanan
                </Link>
                <span class="text-gray-500">- Backoffice Website</span>
            </div>

            <ul
                class="flex order-1 items-center space-x-5 font-semibold text-gray-600 md:order-2 dark:text-gray-400"
            >
                <li>Versi 1.0.0</li>
            </ul>
        </div>
    </footer>
</template>
