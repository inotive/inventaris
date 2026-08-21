<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SoftDeleteRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // UserFactory masih menghasilkan atribut 'photo' yang tidak ada
        // di tabel users, jadi user dibuat manual di sini.
        $this->user = User::create([
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
        ]);

        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->user->assignRole($superadmin);

        $this->actingAs($this->user);
    }

    private function createMasterData(): array
    {
        return [
            'branch'   => Branch::firstOrCreate(['name' => 'Cabang Pusat']),
            'unit'     => Unit::firstOrCreate(['name' => 'Pieces'], ['short_name' => 'pcs']),
            'category' => ItemCategory::firstOrCreate(['name' => 'ATK']),
            'employee' => Employee::create(['name' => 'Pemesan']),
        ];
    }

    private function createItem(string $code, $master): Item
    {
        return Item::create([
            'branch_id'   => $master['branch']->id,
            'unit_id'     => $master['unit']->id,
            'category_id' => $master['category']->id,
            'name'        => "Barang {$code}",
            'code'        => $code,
        ]);
    }

    /**
     * Beri riwayat transaksi agar item saat dihapus menjadi soft delete
     * (bukan force delete).
     */
    private function giveHistory(Item $item): void
    {
        ItemStock::create([
            'item_id'       => $item->id,
            'type'          => 'In',
            'initial_stock' => 0,
            'amount'        => 10,
            'last_stock'    => 10,
            'tanggal'       => now()->toDateString(),
        ]);
    }

    #[Test]
    public function edit_po_mempertahankan_baris_barang_terarsip(): void
    {
        Permission::firstOrCreate(['name' => 'purchase_orders.edit', 'display_name' => 'Edit PO', 'group_name' => 'Pembelian']);
        $this->user->givePermissionTo('purchase_orders.edit');

        $master = $this->createMasterData();
        $itemA  = $this->createItem('ITM-A', $master);
        $itemB  = $this->createItem('ITM-B', $master);

        $po = PurchaseOrder::create([
            'order_no'    => 'PO-TEST-001',
            'ordered_by'  => $master['employee']->id,
            'ordered_at'  => now(),
        ]);
        $po->items()->create(['item_id' => $itemA->id, 'quantity_ordered' => 5, 'cost' => 1000]);
        $po->items()->create(['item_id' => $itemB->id, 'quantity_ordered' => 3, 'cost' => 2000]);

        // Arsipkan itemA (punya riwayat transaksi)
        $this->giveHistory($itemA);
        $itemA->delete();

        // Edit PO: hanya itemB yang dikirim dari form
        $response = $this->put(route('purchase-orders.update', $po), [
            'order_no'    => 'PO-TEST-001',
            'vendor'      => 'Vendor Baru',
            'ordered_at'  => now()->toDateString(),
            'branch_id'   => $master['branch']->id,
            'amount_paid' => 0,
            'items'       => [
                ['item_id' => $itemB->id, 'quantity_ordered' => 7, 'cost' => 2000],
            ],
        ]);

        $response->assertRedirect();

        // Baris item terarsip TETAP ADA di dokumen (tidak hilang saat edit)
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'item_id'           => $itemA->id,
            'quantity_ordered'  => 5,
        ]);

        // Baris aktif diperbarui
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'item_id'           => $itemB->id,
            'quantity_ordered'  => 7,
        ]);

        $this->assertSame(2, $po->items()->count());
    }

    #[Test]
    public function kode_barang_terarsip_bisa_dipakai_ulang(): void
    {
        Permission::firstOrCreate(['name' => 'items.create', 'display_name' => 'Tambah Barang', 'group_name' => 'Penyimpanan']);
        $this->user->givePermissionTo('items.create');

        $master = $this->createMasterData();
        $itemLama = $this->createItem('ITM-REUSE', $master);
        $this->giveHistory($itemLama);

        // Arsipkan barang lama
        $deleteResponse = $this->delete(route('items.destroy', $itemLama));
        $deleteResponse->assertRedirect();
        $this->assertNotNull(Item::onlyTrashed()->find($itemLama->id));

        // Buat barang baru dengan kode yang sama -> harus berhasil
        $response = $this->post(route('items.store'), [
            'name'        => 'Barang Reuse',
            'code'        => 'ITM-REUSE',
            'category_id' => $master['category']->id,
            'unit_id'     => $master['unit']->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Barang baru dengan kode sama ada di cabang
        $this->assertDatabaseHas('items', [
            'code'      => 'ITM-REUSE',
            'name'      => 'Barang Reuse',
            'branch_id' => $master['branch']->id,
        ]);
    }

    #[Test]
    public function load_items_po_tidak_crash_dan_menyembunyikan_barang_terarsip(): void
    {
        $master = $this->createMasterData();
        $itemA  = $this->createItem('ITM-LD', $master);
        $itemB  = $this->createItem('ITM-LV', $master);

        $po = PurchaseOrder::create([
            'order_no'         => 'PO-TEST-002',
            'ordered_by'       => $master['employee']->id,
            'ordered_at'       => now(),
            'status_delivered' => PurchaseOrder::STATUS_DELIVERED_PENDING,
        ]);
        $po->items()->create(['item_id' => $itemA->id, 'quantity_ordered' => 5, 'cost' => 1000]);
        $po->items()->create(['item_id' => $itemB->id, 'quantity_ordered' => 3, 'cost' => 2000]);

        $this->giveHistory($itemA);
        $itemA->delete();

        $response = $this->get(route('good-receipts.loadItems', ['id' => $po->id, 'type' => 'pembelian']));

        $response->assertOk();
        $items = $response->json();

        $this->assertCount(1, $items);
        $this->assertEquals($itemB->id, $items[0]['item_id']);
    }
}
