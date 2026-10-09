<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasir;
    protected Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Oli & Pelumas',
        ]);
    }

    public function test_admin_can_view_stok_masuk_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.stok.create'));
        $response->assertStatus(200);
        $response->assertSee('Stok Masuk');
    }

    public function test_admin_can_add_stok_masuk_successfully(): void
    {
        $sparepart = Sparepart::create([
            'kode_sparepart' => 'OLI-001',
            'nama_sparepart' => 'Oli Yamalube 1L',
            'merek' => 'Yamaha',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 45000,
            'harga_jual' => 55000,
            'stok' => 10,
            'stok_minimum' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.stok.store'), [
            'sparepart_id' => $sparepart->id,
            'jumlah' => 15,
            'catatan' => 'Restok awal bulan',
        ]);

        $response->assertRedirect(route('admin.stok.create'));
        $response->assertSessionHas('success');

        // Verify stok updated
        $this->assertDatabaseHas('spareparts', [
            'id' => $sparepart->id,
            'stok' => 25,
        ]);

        // Verify stock movement logged
        $this->assertDatabaseHas('stock_movements', [
            'sparepart_id' => $sparepart->id,
            'user_id' => $this->admin->id,
            'jenis' => 'masuk',
            'jumlah' => 15,
            'stok_sebelum' => 10,
            'stok_sesudah' => 25,
            'catatan' => 'Restok awal bulan',
        ]);
    }

    public function test_stok_masuk_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.stok.store'), []);

        $response->assertSessionHasErrors(['sparepart_id', 'jumlah']);
    }

    public function test_stok_masuk_rejects_zero_and_negative_jumlah(): void
    {
        $sparepart = Sparepart::create([
            'kode_sparepart' => 'OLI-002',
            'nama_sparepart' => 'Oli MPX 1L',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 40000,
            'harga_jual' => 50000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        // Test zero
        $responseZero = $this->actingAs($this->admin)->post(route('admin.stok.store'), [
            'sparepart_id' => $sparepart->id,
            'jumlah' => 0,
        ]);
        $responseZero->assertSessionHasErrors('jumlah');

        // Test negative
        $responseNegative = $this->actingAs($this->admin)->post(route('admin.stok.store'), [
            'sparepart_id' => $sparepart->id,
            'jumlah' => -5,
        ]);
        $responseNegative->assertSessionHasErrors('jumlah');

        // Assert stok unchanged
        $this->assertEquals(5, $sparepart->fresh()->stok);
    }

    public function test_stok_masuk_rejects_inactive_sparepart(): void
    {
        $inactiveSparepart = Sparepart::create([
            'kode_sparepart' => 'OLI-OLD',
            'nama_sparepart' => 'Oli Discontinued',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 30000,
            'harga_jual' => 40000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.stok.store'), [
            'sparepart_id' => $inactiveSparepart->id,
            'jumlah' => 10,
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $inactiveSparepart->fresh()->stok);
    }

    public function test_kasir_cannot_access_stok_masuk(): void
    {
        $responseForm = $this->actingAs($this->kasir)->get(route('admin.stok.create'));
        $responseForm->assertStatus(403);

        $responseStore = $this->actingAs($this->kasir)->post(route('admin.stok.store'), [
            'sparepart_id' => 1,
            'jumlah' => 10,
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_kasir_cannot_access_riwayat_stok(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('admin.stok.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_riwayat_stok_and_filter_by_sparepart(): void
    {
        $sp1 = Sparepart::create([
            'kode_sparepart' => 'SP-1',
            'nama_sparepart' => 'Busi Shell',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 15000,
            'harga_jual' => 20000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        $sp2 = Sparepart::create([
            'kode_sparepart' => 'SP-2',
            'nama_sparepart' => 'Busi NGK',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 18000,
            'harga_jual' => 25000,
            'stok' => 5,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        StockMovement::create([
            'sparepart_id' => $sp1->id,
            'user_id' => $this->admin->id,
            'jenis' => 'masuk',
            'jumlah' => 10,
            'stok_sebelum' => 0,
            'stok_sesudah' => 10,
            'catatan' => 'Restok busi shell',
        ]);

        StockMovement::create([
            'sparepart_id' => $sp2->id,
            'user_id' => $this->admin->id,
            'jenis' => 'masuk',
            'jumlah' => 5,
            'stok_sebelum' => 0,
            'stok_sesudah' => 5,
            'catatan' => 'Restok busi ngk',
        ]);

        // View all
        $responseIndex = $this->actingAs($this->admin)->get(route('admin.stok.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Busi Shell');
        $responseIndex->assertSee('Busi NGK');

        // Filter by SP1
        $responseFilter = $this->actingAs($this->admin)->get(route('admin.stok.index', ['sparepart_id' => $sp1->id]));
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('Restok busi shell');
        $responseFilter->assertDontSee('Restok busi ngk');
    }

    public function test_status_stok_accessor_returns_correct_values(): void
    {
        $spAman = new Sparepart(['stok' => 10, 'stok_minimum' => 5]);
        $this->assertEquals('Aman', $spAman->status_stok);

        $spMenipis = new Sparepart(['stok' => 5, 'stok_minimum' => 5]);
        $this->assertEquals('Menipis', $spMenipis->status_stok);

        $spMenipis2 = new Sparepart(['stok' => 2, 'stok_minimum' => 5]);
        $this->assertEquals('Menipis', $spMenipis2->status_stok);

        $spHabis = new Sparepart(['stok' => 0, 'stok_minimum' => 5]);
        $this->assertEquals('Habis', $spHabis->status_stok);
    }
}
