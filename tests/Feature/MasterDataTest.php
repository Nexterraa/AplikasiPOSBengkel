<?php

namespace Tests\Feature;

use App\Models\JasaServis;
use App\Models\Kategori;
use App\Models\Mekanik;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasir;

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
    }

    public function test_admin_can_access_kategori_index_and_create(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kategori.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Oli Mesin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategoris', ['nama_kategori' => 'Oli Mesin']);
    }

    public function test_kasir_cannot_access_kategori(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('admin.kategori.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_crud_sparepart(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Pelumas']);

        $response = $this->actingAs($this->admin)->post(route('admin.spareparts.store'), [
            'kode_sparepart' => 'OLI-001',
            'nama_sparepart' => 'Oli Shell Helix 1L',
            'merek' => 'Shell',
            'kategori_id' => $kategori->id,
            'harga_beli' => 45000,
            'harga_jual' => 55000,
            'stok' => 10,
            'stok_minimum' => 3,
        ]);

        $response->assertRedirect(route('admin.spareparts.index'));
        $this->assertDatabaseHas('spareparts', [
            'kode_sparepart' => 'OLI-001',
            'nama_sparepart' => 'Oli Shell Helix 1L',
        ]);
    }

    public function test_admin_can_crud_jasa_servis(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.jasa.store'), [
            'kode_jasa' => 'JS-001',
            'nama_jasa' => 'Servis Injeksi',
            'harga' => 50000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jasa_servis', [
            'kode_jasa' => 'JS-001',
            'nama_jasa' => 'Servis Injeksi',
        ]);
    }

    public function test_admin_can_crud_mekanik(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mekanik.store'), [
            'nama_mekanik' => 'Ahmad Suhendra',
            'telepon' => '081299887766',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mekaniks', [
            'nama_mekanik' => 'Ahmad Suhendra',
        ]);
    }

    public function test_admin_can_create_kasir_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kasir.store'), [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'kasirbaru@test.com',
            'role' => 'kasir',
        ]);
    }

    public function test_kategori_cannot_be_deleted_if_used_by_sparepart(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Rem']);
        Sparepart::create([
            'kode_sparepart' => 'REM-001',
            'nama_sparepart' => 'Kampas Rem Depan',
            'kategori_id' => $kategori->id,
            'harga_beli' => 20000,
            'harga_jual' => 30000,
            'stok' => 5,
            'stok_minimum' => 2,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kategori.destroy', $kategori->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }
}

