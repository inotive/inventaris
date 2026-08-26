<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemStock;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockInSoftDeleteTest extends TestCase
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

        Role::firstOrCreate(['name' => 'Superadmin']);
        $this->user->assignRole('Superadmin');

        $this->actingAs($this->user);
    }

    private function createItemWithStockIn(): array
    {
        $branch   = Branch::firstOrCreate(['name' => 'Cabang Pusat']);
        $unit     = Unit::firstOrCreate(['name' => 'Pieces'], ['short_name' => 'pcs']);
        $category = ItemCategory::firstOrCreate(['name' => 'ATK']);

        $item = Item::create([
            'branch_id'   => $branch->id,
            'unit_id'     => $unit->id,
            'category_id' => $category->id,
            'name'        => 'Kertas HVS',
            'code'        => 'ITM-STK',
        ]);

        $stock = ItemStock::create([
            'item_id'       => $item->id,
            'type'          => 'In',
            'initial_stock' => 0,
            'amount'        => 10,
            'last_stock'    => 10,
            'tanggal'       => now()->toDateString(),
        ]);

        return [$item, $stock];
    }

    #[Test]
    public function daftar_stok_masuk_tidak_crash_dan_menyembunyikan_transaksi_item_terarsip(): void
    {
        [$item, $stock] = $this->createItemWithStockIn();

        // Arsipkan barang (soft delete) yang punya riwayat transaksi
        $item->delete();

        $response = $this->get(route('stock-in.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Admin/StockIns/Index')
                ->has('itemStocks.data', 0)
        );
    }

    #[Test]
    public function daftar_stok_masuk_tetap_menampilkan_transaksi_item_aktif(): void
    {
        [, $stock] = $this->createItemWithStockIn();

        $response = $this->get(route('stock-in.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Admin/StockIns/Index')
                ->has('itemStocks.data', 1)
                ->where('itemStocks.data.0.id', $stock->id)
        );
    }
}
