<template>
    <Head title="Buat Purchase Order" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between">
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Buat Purchase Order
                </div>
                <div class="font-bold text-sky-400 md:text-xl px-7">
                    {{ form.order_no }}
                </div>
            </div>
            <Form
                :form="form"
                :purchaseRequests="purchaseRequests"
                :items="items"
                :user="user"
                :isSuperadmin="isSuperadmin"
                :employees="employees"
                :branches="branches"
                @cancel="goBack"
            />
        </div>

        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between px-6 py-4">
                <div class="font-semibold text-gray-700 dark:text-gray-200">
                    Daftar Item
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Barang
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex cursor-pointer items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
                                    >
                                        Kode Barang
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Pesan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Harga Satuan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Subtotal
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Catatan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="form.items.length === 0"
                        >
                            <td
                                colspan="8"
                                class="py-8 text-center text-gray-500"
                            >
                                Belum ada item. Pilih Purchase Request atau tambah item secara manual.
                            </td>
                        </tr>
                        <tr
                            v-for="(item, i) in form.items"
                            :key="i"
                            class="hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-800"
                        >
                            <td
                                class="w-4 py-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap justify-center"
                                >
                                    {{ i + 1 }}.
                                </div>
                            </td>
                            <td
                                class="px-5 py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <SelectBarang
                                    v-model="item.selectedItem"
                                    :items="getAvailableItems(i)"
                                    label="Pilih Item"
                                    @update:modelValue="updateItemInfo(i, $event)"
                                >
                                    <template #item="{ item: itemData }">
                                        <div class="flex flex-col" v-if="isSuperadmin">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ itemData.name }} ({{ itemData.branch?.name || '-' }})</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ itemData.code }}</span>
                                        </div>
                                        <div class="flex flex-col" v-else>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ itemData.name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ itemData.code }}</span>
                                        </div>
                                    </template>
                                </SelectBarang>
                                <!-- Pesan error validasi untuk item_id -->
                                <div v-if="form.errors[`items.${i}.item_id`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`items.${i}.item_id`][0] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center px-3 whitespace-nowrap justify-center"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ item.code }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <input
                                        v-model.number="item.quantity_ordered"
                                        @input="calculateSubtotal(i)"
                                        type="number"
                                        min="1"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                        required
                                    />
                                    {{ item.unit }}
                                </div>
                                <!-- Pesan error validasi untuk quantity_ordered -->
                                <div v-if="form.errors[`items.${i}.quantity_ordered`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`items.${i}.quantity_ordered`] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <input
                                        v-model.number="item.cost"
                                        @input="calculateSubtotal(i)"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-32 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                                <!-- Pesan error validasi untuk cost -->
                                <div v-if="form.errors[`items.${i}.cost`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`items.${i}.cost`] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center px-3 whitespace-nowrap justify-center"
                                >
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">
                                        {{ formatCurrency(item.quantity_ordered * item.cost) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap justify-center"
                                >
                                    <input
                                        v-model="item.note"
                                        type="text"
                                        placeholder="Masukkan catatan"
                                        class="w-40 h-10 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                                <!-- Pesan error validasi untuk note -->
                                <div v-if="form.errors[`items.${i}.note`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`items.${i}.note`] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center whitespace-nowrap px-4 gap-3"
                                >
                                    <button @click="removeItem(i)" type="button">
                                        <TrashIcon class="text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="form.items.length > 0">
                        <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                            <td colspan="5" class="px-3 py-2 text-right font-semibold">Total:</td>
                            <td class="px-3 py-2 font-semibold text-center">{{ formatCurrency(totalAmount) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>

                <button
                    type="button"
                    @click="addItem"
                    class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap"
                >
                    + Tambah Item
                </button>
            </div>
            <div
                v-if="form.errors.items"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.items[0] }}
            </div>
        </div>

        <div class="flex items-center gap-5 justify-end">
            <button
                type="button"
                @click="goBack"
                class="inline-flex items-center rounded border bg-gray-400 border-gray-300 px-5 py-2 text-sm text-white hover:text-gray-800 hover:bg-gray-300 dark:border-gray-700 dark:text-gray-300"
            >
                Batal
            </button>
            <button
                @click="showPaymentModal"
                :disabled="form.processing || form.items.length === 0"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
            >
                {{ form.processing ? 'Menyimpan...' : 'Buat dengan Pembayaran' }}
            </button>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPayment" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-[500px] max-w-[95vw] rounded-lg bg-white shadow">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold">Konfirmasi Pembayaran (Opsional)</div>
                </div>
                <div class="p-4">
                    <div class="text-sm text-gray-600 mb-4">
                        Masukkan jumlah yang telah dibayar untuk Purchase Order ini. Anda juga dapat menyimpan tanpa pembayaran.
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Order</label>
                        <div class="text-lg font-semibold text-gray-900">{{ formatCurrency(totalAmount) }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah yang Dibayar (Opsional)</label>
                        <input
                            v-model.number="paymentForm.amount_paid"
                            type="number"
                            min="0"
                            :max="totalAmount"
                            step="0.01"
                            :class="[
                                'w-full px-3 py-2 rounded border focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                                paymentForm.amount_paid > totalAmount ? 'border-red-500 bg-red-50' : 'border-gray-300'
                            ]"
                            placeholder="Masukkan jumlah yang dibayar (kosongkan jika tidak ada pembayaran)"
                        />
                        <div v-if="paymentForm.amount_paid > 0" class="text-xs mt-1" :class="paymentForm.amount_paid > totalAmount ? 'text-red-600' : 'text-gray-500'">
                            <span v-if="paymentForm.amount_paid > totalAmount" class="font-medium">
                                Jumlah pembayaran melebihi total order!
                            </span>
                            <span v-else>
                                Sisa: {{ formatCurrency(totalAmount - paymentForm.amount_paid) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 border-t flex justify-between">
                    <button @click="closePaymentModal" class="px-3 h-9 rounded border hover:bg-gray-50">Batal</button>
                    <div class="flex gap-2">
                        <button
                            @click="submitWithPayment"
                            :disabled="paymentForm.amount_paid > totalAmount"
                            class="px-3 h-9 rounded text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="paymentForm.amount_paid > totalAmount ? 'bg-red-600' : 'bg-blue-600'"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Form from "./Form.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SelectBarang from "@/Components/form/SelectBarang.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Purchase Order", href: route("purchase-orders.index") },
    { label: "Buat Purchase Order" },
];

const props = defineProps({
    orderNo: { type: String, required: true },
    purchaseRequests: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    user: { type: Object, required: true },
    isSuperadmin: { type: Boolean, required: true },
});

const form = useForm({
    order_no: props.orderNo,
    ordered_at: new Date().toISOString().split('T')[0],
    vendor: '',
    request_id: '',
    note: '',
    items: [],
    employee_id: props.user?.employee?.id,
    branch_id: props.user?.employee?.branch_id || (props.branches.length > 0 ? props.branches[0].id : null),
    amount_paid: 0,
});

const showPayment = ref(false);

const paymentForm = ref({
    amount_paid: 0,
});

const addItem = () => {
    form.items.push({
        item_id: '',
        selectedItem: null,
        quantity_ordered: 1,
        cost: 0,
        note: '',
        unit: '',
        code: '',
        name: '',
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
    // Trigger reactivity to update available items in other selects
    form.items = [...form.items];
};

const updateItemInfo = (index, selectedItem) => {
    if (selectedItem && selectedItem.id) {
        form.items[index].item_id = selectedItem.id;
        form.items[index].selectedItem = selectedItem;
        form.items[index].unit = selectedItem.unit?.short_name || '';
        form.items[index].code = selectedItem.code || '';
        form.items[index].name = selectedItem.name || '';
    } else {
        form.items[index].item_id = '';
        form.items[index].selectedItem = null;
        form.items[index].unit = '';
        form.items[index].code = '';
        form.items[index].name = '';
    }

    // Trigger reactivity to update available items in other selects
    form.items = [...form.items];
};

const calculateSubtotal = (index) => {
    // Trigger reactivity
    form.items[index] = { ...form.items[index] };
};

const getAvailableItems = (currentIndex) => {
    // Get all currently selected item IDs except the current item
    const selectedItemIds = form.items
        .filter((item, index) => index !== currentIndex && item.item_id)
        .map(item => item.item_id);

    // Filter out selected items from the available items
    return props.items.filter(item => !selectedItemIds.includes(item.id));
};

const totalAmount = computed(() => {
    return form.items.reduce((total, item) => {
        return total + (item.quantity_ordered * item.cost);
    }, 0);
});

const showPaymentModal = () => {
    if (form.items.length === 0) {
        alert('Minimal harus ada satu item');
        return;
    }

    if (!form.vendor || form.vendor.trim() === '') {
        alert('Nama vendor harus diisi');
        return;
    }

    // Reset payment form
    paymentForm.value = {
        amount_paid: 0,
    };

    showPayment.value = true;
};

const submitWithoutPayment = () => {
    if (form.items.length === 0) {
        alert('Minimal harus ada satu item');
        return;
    }

    if (!form.vendor || form.vendor.trim() === '') {
        alert('Nama vendor harus diisi');
        return;
    }

    // Set amount_paid to 0 for no payment
    form.amount_paid = 0;

    // Submit the form
    form.post(route('purchase-orders.store'));
};

const closePaymentModal = () => {
    showPayment.value = false;
};

const submitWithZeroPayment = () => {
    // Set amount_paid to 0 for no payment
    form.amount_paid = 0;

    // Submit the form
    form.post(route('purchase-orders.store'), {
        onSuccess: () => {
            showPayment.value = false;
        }
    });
};

const submitWithPayment = () => {
    if (paymentForm.value.amount_paid < 0) {
        alert('Jumlah pembayaran tidak boleh negatif');
        return;
    }

    if (paymentForm.value.amount_paid > totalAmount.value) {
        alert('Jumlah pembayaran tidak boleh melebihi total order');
        return;
    }

    // Update form with payment data (can be 0 if no amount entered)
    form.amount_paid = paymentForm.value.amount_paid || 0;

    // Submit the form
    form.post(route('purchase-orders.store'), {
        onSuccess: () => {
            showPayment.value = false;
        }
    });
};

const submit = () => {
    if (form.items.length === 0) {
        alert('Minimal harus ada satu item');
        return;
    }

    form.post(route('purchase-orders.store'));
};

const goBack = () => {
    router.get(route('purchase-orders.index'));
};

// Watch untuk Purchase Request yang dipilih
watch(
    () => form.request_id,
    (newVal) => {
        if (!newVal) {
            form.items = [];
            return;
        }

        const pr = props.purchaseRequests.find((p) => p.id === newVal);
        if (pr && pr.items) {
            // Clear existing items first
            form.items = [];

            // Add items from selected Purchase Request
            form.items = pr.items.map((item) => {
                const itemData = props.items.find(i => i.id === item.item_id);
                return {
                    item_id: item.item_id,
                    selectedItem: itemData || null,
                    quantity_ordered: item.quantity_requested || 1,
                    cost: 0, // Default cost, user can fill manually
                    note: item.note || '',
                    unit: itemData?.unit?.short_name || item.unit?.short_name || '',
                    code: itemData?.code || item.code || '',
                    name: itemData?.name || item.name || '',
                };
            });
        }
    }
);

const formatCurrency = (n) => {
    try {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(n || 0);
    } catch(e){
        return n;
    }
};
</script>
