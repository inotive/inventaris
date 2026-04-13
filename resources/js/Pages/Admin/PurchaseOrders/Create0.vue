<template>
    <Head title="Buat Purchase Order" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2">
                <button type="button" class="px-3 h-9 text-sm rounded border" @click="goBack">Batal</button>
                <button type="button" class="px-3 h-9 text-sm rounded bg-blue-600 text-white" @click="saveDraft">Simpan Draft</button>
                <button type="button" class="px-3 h-9 text-sm rounded bg-emerald-600 text-white" @click="sendPO">Kirim</button>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">No. PO</label>
                    <input :value="autoNumber" disabled class="w-full h-10 rounded border-gray-300 bg-gray-50 text-gray-700" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                    <input v-model="form.date" type="date" class="w-full h-10 rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Vendor</label>
                    <select v-model="form.vendor_id" class="w-full h-10 rounded border-gray-300">
                        <option :value="null">Pilih Vendor</option>
                        <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                <textarea v-model="form.notes" rows="2" class="w-full rounded border-gray-300"></textarea>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div class="font-semibold text-gray-700">Item PO</div>
                <button type="button" class="px-3 h-9 text-sm rounded border" @click="addRow">Tambah Baris</button>
            </div>
            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 60px" />
                        <col />
                        <col style="width: 120px" />
                        <col style="width: 140px" />
                        <col style="width: 160px" />
                        <col style="width: 120px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">No</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">Nama Item</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">Qty</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">Harga</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">Subtotal</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200"><div class="px-3 text-left font-medium text-gray-600">Aksi</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, idx) in form.items" :key="row._key" class="border-b border-gray-200">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2"><input v-model="row.name" type="text" class="w-full h-10 rounded border-gray-300" placeholder="Nama item" /></td>
                            <td class="px-3 py-2"><input v-model.number="row.qty" type="number" min="0" class="w-24 h-10 rounded border-gray-300" /></td>
                            <td class="px-3 py-2"><input v-model.number="row.price" type="number" min="0" class="w-32 h-10 rounded border-gray-300" /></td>
                            <td class="px-3 py-2">{{ formatCurrency(row.qty * row.price) }}</td>
                            <td class="px-3 py-2"><button class="px-2 py-1 text-xs rounded border" @click="removeRow(idx)">Hapus</button></td>
                        </tr>
                        <tr v-if="!form.items.length">
                            <td colspan="6" class="py-6 text-center text-gray-500">Belum ada item</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-3 py-3 text-right font-medium">Total</td>
                            <td class="px-3 py-3 font-medium">{{ formatCurrency(grandTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    </template>

<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    autoNumber: { type: String, required: true },
    vendors: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const breadcrumbs = [
    { label: "Dashboard", href: route("dashboard") },
    { label: "Purchase Order", href: route("purchase-orders.index") },
    { label: "Buat" },
];

const form = ref({
    number: props.autoNumber,
    date: new Date().toISOString().slice(0, 10),
    vendor_id: null,
    notes: "",
    status: "draft",
    items: [],
});

const grandTotal = computed(() => {
    return (form.value.items || []).reduce((sum, it) => sum + (Number(it.qty || 0) * Number(it.price || 0)), 0);
});

function addRow(){ form.value.items.push({ _key: crypto.randomUUID?.() || Math.random().toString(36).slice(2), name: "", qty: 0, price: 0 }); }
function removeRow(i){ form.value.items.splice(i,1); }

function saveDraft(){ console.log("save draft", form.value); /* TODO: POST route('purchase-orders.store') */ }
function sendPO(){ console.log("send PO", form.value); /* TODO: POST with status 'sent' */ }
function goBack(){ router.get(route('purchase-orders.index')); }

function formatCurrency(n){
    try { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n || 0); } catch(e){ return n; }
}

defineOptions({ layout: AppLayout });
</script>
