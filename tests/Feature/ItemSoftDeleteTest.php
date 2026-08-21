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
use Tests\TestCase;

class ItemSoftDeleteTest extends TestCase
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
        $this->actingAs($this->user);
    }

    private function createMasterData(): array
    {
        return [
            'branch'   => Branch::firstOrCreate(['name' => 'Cabang Pusat']),
            'branch2'  => Branch::firstOrCreate(['name' => 'Cabang Cabang']),
            'unit'     => Unit::firstOrCreate(['name' => 'Pieces'], ['short_name' => 'pcs']),
            'category' => ItemCategory::firstOrCreate(['name' => 'ATK']),
        ];
    }

    /**
     * Membuat satu grup item (satu kode, tersebar di beberapa cabang).
     */
    private function createItemGroup(string $code = 'ITM-001', int $branches = 2): array
    {
        $master = $this->createMasterData();
        $branchList = [$master['branch'], $master['branch2']];

        $items = [];
        foreach (array_slice($branchList, 0, $branches) as $branch) {
            $items[] = Item::create([
                'branch_id'   => $branch->id,
                'unit_id'     => $master['unit']->id,
                'category_id' => $master['category']->id,
                'name'        => 'Kertas HVS',
                'code'        => $code,
            ]);
        }

        return $items;
    }

    #[Test]
    public function item_model_menggunakan_soft_delete(): void
    {
        [$item] = $this->createItemGroup();

        $item->delete();

        // Kolom deleted_at terisi
        $this->assertNotNull($item->fresh()->deleted_at);

        // Baris masih ada di database
        $this->assertDatabaseHas('items', ['id' => $item->id]);

        // Tidak muncul di query default
        $this->assertNull(Item::find($item->id));

        // Masih bisa ditemukan dengan withTrashed
        $this->assertTrue(Item::withTrashed()->where('id', $item->id)->exists());

        // Bisa di-restore
        Item::withTrashed()->where('id', $item->id)->first()->restore();
        $this->assertNotNull(Item::find($item->id));
        $this->assertNull($item->fresh()->deleted_at);
    }

    #[Test]
    public function destroy_barang_tanpa_riwayat_dihapus_permanen(): void
    {
        [$item] = $this->createItemGroup();

        $response = $this->delete(route('items.destroy', $item));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Hard delete: baris benar-benar hilang dari database
        $this->assertDatabaseMissing('items', ['code' => 'ITM-001']);
        $this->assertSame(0, Item::withTrashed()->count());
    }

    #[Test]
    public function destroy_barang_berriwayat_hanya_diarsipkan_soft_delete(): void
    {
        [$item] = $this->createItemGroup();

        // Beri riwayat transaksi stok
        ItemStock::create([
            'item_id'       => $item->id,
            'type'          => 'In',
            'initial_stock' => 0,
            'amount'        => 10,
            'last_stock'    => 10,
            'tanggal'       => now()->toDateString(),
        ]);

        $response = $this->delete(route('items.destroy', $item));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Soft delete: baris masih ada tapi deleted_at terisi
        $this->assertDatabaseHas('items', ['id' => $item->id]);
        $this->assertNotNull(Item::onlyTrashed()->find($item->id));
        $this->assertNull(Item::find($item->id));
    }

    #[Test]
    public function destroy_menghapus_seluruh_grup_kode_di_semua_cabang(): void
    {
        $items = $this->createItemGroup('ITM-002', 2);
        $this->assertCount(2, $items);

        $response = $this->delete(route('items.destroy', $items[0]));

        $response->assertRedirect();

        // Semua item dengan kode yang sama ikut terhapus
        $this->assertSame(0, Item::withTrashed()->where('code', 'ITM-002')->count());
    }

    #[Test]
    public function bulk_destroy_menghapus_banyak_grup_sekaligus(): void
    {
        $groupA = $this->createItemGroup('ITM-A', 2);
        $groupB = $this->createItemGroup('ITM-B', 1);

        $response = $this->post(route('items.bulk-destroy'), [
            'codes' => ['ITM-A', 'ITM-B'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Berhasil menghapus 2 barang');

        $this->assertSame(0, Item::withTrashed()->whereIn('code', ['ITM-A', 'ITM-B'])->count());
    }

    #[Test]
    public function bulk_destroy_validasi_tanpa_codes_gagal(): void
    {
        $response = $this->post(route('items.bulk-destroy'), []);

        $response->assertSessionHasErrors(['codes']);

        // Tidak ada item yang terhapus
        $this->assertSame(0, Item::count());
    }

    #[Test]
    public function barang_terarsip_tidak_muncul_di_daftar_items(): void
    {
        [$item] = $this->createItemGroup();

        ItemStock::create([
            'item_id'       => $item->id,
            'type'          => 'In',
            'initial_stock' => 0,
            'amount'        => 5,
            'last_stock'    => 5,
            'tanggal'       => now()->toDateString(),
        ]);

        $this->delete(route('items.destroy', $item));

        // Query default model tidak lagi menampilkan barang terarsip
        $this->assertSame(0, Item::where('code', 'ITM-001')->count());
        // 2 item (2 cabang) masih ada di database sebagai arsip
        $this->assertSame(2, Item::withTrashed()->where('code', 'ITM-001')->count());
    }
}
