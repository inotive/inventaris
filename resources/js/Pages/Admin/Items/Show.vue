<template>

    <Head :title="`Detail Barang - ${item?.name || ''}`" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <!-- Header with Icon and Breadcrumb -->
        <div
            class="flex overflow-hidden flex-col gap-3 p-6 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg dark:bg-blue-900/30">
                        <svg class="text-blue-600 w-7 h-7 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800 dark:text-white">Detail Barang</h1>
                        <Breadcrumb :items="breadcrumbs" />
                    </div>
                </div>
                <Link :href="route('items.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                Kembali
                </Link>
            </div>
        </div>

        <!-- Item Info Card with Photo -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Main Item Card -->
            <div
                class="lg:col-span-2 bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-white/[0.03]">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <!-- Item Photo -->
                        <div class="flex-shrink-0">
                            <img :src="item?.photo ? `/storage/${item.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(item?.name || 'Item')}&background=e5e7eb&color=374151&size=128`"
                                :alt="item?.name"
                                class="object-cover w-24 h-24 border border-gray-200 rounded-lg dark:border-gray-600" />
                        </div>
                        <!-- Item Details -->
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ item?.name }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ item?.code }}</p>
                            <div class="flex gap-4 mt-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Kategori</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ item?.category ||
                                        '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Satuan</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ item?.unit || '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-4">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Total Persediaan</span>
                                <span class="text-2xl font-bold text-gray-800 dark:text-white">{{
                                    formatNumber(totalStock) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock In Card -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-white/[0.03]">
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg dark:bg-green-900/30">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 12H4M12 4v16" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ formatNumber(stockIn) }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Stok Masuk (Bulan ini)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Out Card -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg dark:bg-red-900/30">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ formatNumber(stockOut) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Stok Keluar (Bulan ini)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Section: Stok Gudang & Riwayat Stok -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-white/[0.03]">
            <!-- Tabs Header -->
            <div class="px-6 pt-4 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-4">
                    <button v-for="tab in tabs" :key="tab.value" :class="[
                        'px-4 py-2 text-sm font-medium rounded-t transition-colors',
                        activeTab === tab.value
                            ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400'
                            : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
                    ]" @click="activeTab = tab.value">
                        {{ tab.label }}
                    </button>
                </div>
            </div>


            <!-- Tab Content -->
            <div class="p-6">
                <!-- Stok Gudang Tab -->
                <div v-if="activeTab === 'warehouse'" class="overflow-auto" data-simplebar>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="p-3 bg-gray-100 border-gray-200 border-y dark:bg-gray-800 dark:border-gray-600">
                                    <div class="font-medium text-left text-gray-600 dark:text-gray-300">Gudang/Cabang
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border-gray-200 border-y dark:bg-gray-800 dark:border-gray-600">
                                    <div class="font-medium text-left text-gray-600 dark:text-gray-300">Stok Saat Ini
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="stocksByBranch && stocksByBranch.length">
                                <tr v-for="s in stocksByBranch" :key="s.branch_id"
                                    class="border-b border-gray-200 dark:border-gray-600">
                                    <td class="p-3 text-gray-800 dark:text-white">{{ s.branch || '-' }}</td>
                                    <td class="p-3 text-gray-800 dark:text-white">{{ formatNumber(s.current_stock) }}
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="2" class="p-6 text-center text-gray-500 dark:text-gray-400">Belum ada data
                                    stok</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Riwayat Stok Tab -->
                <div v-else-if="activeTab === 'history'" class="space-y-3">
                    <template v-if="movements && movements.data && movements.data.length">
                        <div v-for="m in movements.data" :key="m.id"
                            class="p-4 bg-white border border-gray-200 rounded-lg dark:border-gray-600 dark:bg-gray-800/50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{
                                            refLabel(m) }}</span>
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded',
                                            m.type === 'In' || m.type === 'Adjust_In' || m.type === 'Transfer_In' || m.type === 'Return_In'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : m.type === 'Out' || m.type === 'Adjust_Out' || m.type === 'Transfer_Out'
                                                    ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        ]">
                                            {{ typeLabel(m.type) }}
                                        </span>
                                    </div>
                                    <div class="flex gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Stock sebelumnya</span>
                                            <p class="font-medium text-gray-800 dark:text-white">{{
                                                formatNumber(m.current_stock) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Stok Masuk/Keluar</span>
                                            <p class="font-medium text-gray-800 dark:text-white">
                                                <span
                                                    v-if="m.type === 'In' || m.type === 'Adjust_In' || m.type === 'Transfer_In' || m.type === 'Return_In'"
                                                    class="text-green-600 dark:text-green-400">▲</span>
                                                <span v-else class="text-red-600 dark:text-red-400">▼</span>
                                                {{ formatNumber(m.quantity) }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Stock saat ini</span>
                                            <p class="font-medium text-gray-800 dark:text-white">{{
                                                formatNumber(m.last_stock) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(m.at) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ m.branch || '-' }}</p>
                                </div>
                            </div>
                            <div v-if="m.notes" class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ m.notes }}</p>
                            </div>
                        </div>
                    </template>
                    <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Belum ada riwayat stok
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { useAuth } from "@/Composables/useAuth";

defineOptions({ layout: AppLayout });

const { can } = useAuth();

const props = defineProps({
    item: Object,
    stocksByBranch: { type: Array, default: () => [] },
    movements: { type: Object, default: () => ({ data: [] }) },
    stockIn: { type: Number, default: 0 },
    stockOut: { type: Number, default: 0 },
});

const activeTab = ref('warehouse');
const tabs = computed(() => [
    { label: 'Gudang', value: 'warehouse' },
    { label: 'Riwayat', value: 'history' },
]);

const totalStock = computed(() => {
    return props.item?.last_stock ?? 0;
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Daftar Barang", href: route('items.index') },
    { label: props.item?.name || 'Detail' },
];

function formatNumber(n) {
    const num = Number(n ?? 0);
    return Number.isFinite(num) ? num.toLocaleString('id-ID') : String(n);
}

function formatDateTime(val) {
    if (!val) return '-';
    const d = new Date(val);
    if (isNaN(d.getTime())) return String(val);
    return d.toLocaleString('id-ID');
}

function typeLabel(t) {
    const map = {
        In: 'Masuk',
        Out: 'Keluar',
        adjust_in: 'Penyesuaian (+)',
        adjust_out: 'Penyesuaian (-)',
        transfer_in: 'Transfer Masuk',
        transfer_out: 'Transfer Keluar',
        return_in: 'Retur Masuk',
    };
    return map[t] || t;
}

function refLabel(m) {
    if (!m.reference_type) return '-';
    return `${m.reference_type} #${m.reference_id || '-'}`;
}
</script>

<script>
export default {
    components: {
        InfoRow: {
            props: { label: String, value: [String, Number, null] },
            template: `
        <div>
          <label class='block mb-1 text-sm text-gray-600'>{{ label }}</label>
          <div class='p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800'>{{ value ?? '-' }}</div>
        </div>`
        }
    }
}
</script>
