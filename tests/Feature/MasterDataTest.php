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

    public function test_kode_jasa_otomatis_generated_correctly_and_sequential(): void
    {
        $this->assertEquals('JS-001', JasaServis::generateNextKode());

        // Simpan jasa pertama tanpa mengisi kode manual
        $response = $this->actingAs($this->admin)->post(route('admin.jasa.store'), [
            'nama_jasa' => 'Ganti Oli Mesin',
            'harga' => 20000,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('jasa_servis', [
            'kode_jasa' => 'JS-001',
            'nama_jasa' => 'Ganti Oli Mesin',
        ]);

        // Kode berikutnya harus JS-002
        $this->assertEquals('JS-002', JasaServis::generateNextKode());

        // Simpan jasa kedua
        $this->actingAs($this->admin)->post(route('admin.jasa.store'), [
            'nama_jasa' => 'Tune Up Standar',
            'harga' => 45000,
        ]);
        $this->assertDatabaseHas('jasa_servis', [
            'kode_jasa' => 'JS-002',
            'nama_jasa' => 'Tune Up Standar',
        ]);

        // Data lama dengan format lain tidak mengganggu urutan JS-
        JasaServis::create([
            'kode_jasa' => 'SRV-CUSTOM',
            'nama_jasa' => 'Layanan Khusus',
            'harga' => 100000,
            'is_active' => true,
        ]);

        $this->assertEquals('JS-003', JasaServis::generateNextKode());
    }

    public function test_kode_sparepart_otomatis_generated_correctly_and_sequential(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Pelumas']);

        $this->assertEquals('SP-001', Sparepart::generateNextKode());

        // Simpan sparepart pertama tanpa kode manual
        $response = $this->actingAs($this->admin)->post(route('admin.spareparts.store'), [
            'nama_sparepart' => 'Oli Yamalube Matic 0.8L',
            'merek' => 'Yamaha',
            'kategori_id' => $kategori->id,
            'harga_beli' => 38000,
            'harga_jual' => 45000,
            'stok' => 15,
            'stok_minimum' => 5,
        ]);
        $response->assertRedirect(route('admin.spareparts.index'));
        $this->assertDatabaseHas('spareparts', [
            'kode_sparepart' => 'SP-001',
            'nama_sparepart' => 'Oli Yamalube Matic 0.8L',
        ]);

        // Kode berikutnya harus SP-002
        $this->assertEquals('SP-002', Sparepart::generateNextKode());

        // Simpan sparepart kedua
        $this->actingAs($this->admin)->post(route('admin.spareparts.store'), [
            'nama_sparepart' => 'Busi NGK Standar',
            'merek' => 'NGK',
            'kategori_id' => $kategori->id,
            'harga_beli' => 12000,
            'harga_jual' => 18000,
            'stok' => 20,
            'stok_minimum' => 5,
        ]);
        $this->assertDatabaseHas('spareparts', [
            'kode_sparepart' => 'SP-002',
            'nama_sparepart' => 'Busi NGK Standar',
        ]);

        // Jika ada kode SP-005, kode berikutnya melompat ke SP-006
        Sparepart::create([
            'kode_sparepart' => 'SP-005',
            'nama_sparepart' => 'Filter Udara',
            'kategori_id' => $kategori->id,
            'harga_beli' => 25000,
            'harga_jual' => 35000,
            'stok' => 10,
            'stok_minimum' => 3,
            'is_active' => true,
        ]);

        $this->assertEquals('SP-006', Sparepart::generateNextKode());
    }

    public function test_form_tambah_jasa_dan_sparepart_menampilkan_kode_otomatis_readonly(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Ban']);

        // Form jasa servis (modal di index)
        $responseJasa = $this->actingAs($this->admin)->get(route('admin.jasa.index'));
        $responseJasa->assertStatus(200);
        $responseJasa->assertSee('value="JS-001"', false);
        $responseJasa->assertSee('readonly', false);

        // Form tambah sparepart
        $responseSparepart = $this->actingAs($this->admin)->get(route('admin.spareparts.create'));
        $responseSparepart->assertStatus(200);
        $responseSparepart->assertSee('value="SP-001"', false);
        $responseSparepart->assertSee('readonly', false);
    }

    public function test_artisan_command_standardizes_master_codes_sequentially_without_changing_ids(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Umum']);

        // Data lama dengan berbagai macam format kode
        $jasa1 = JasaServis::create(['kode_jasa' => 'SRV-01', 'nama_jasa' => 'Servis Ringan', 'harga' => 30000]);
        $jasa2 = JasaServis::create(['kode_jasa' => 'JS-999', 'nama_jasa' => 'Servis Berat', 'harga' => 150000]);
        $jasa3 = JasaServis::create(['kode_jasa' => 'CUSTOM-SVC', 'nama_jasa' => 'Ganti Ban', 'harga' => 25000]);

        $sp1 = Sparepart::create(['kode_sparepart' => 'OLI-001', 'nama_sparepart' => 'Oli Mesin', 'kategori_id' => $kategori->id, 'harga_beli' => 30000, 'harga_jual' => 40000, 'stok' => 10, 'stok_minimum' => 2]);
        $sp2 = Sparepart::create(['kode_sparepart' => 'SP-888', 'nama_sparepart' => 'Busi', 'kategori_id' => $kategori->id, 'harga_beli' => 10000, 'harga_jual' => 15000, 'stok' => 5, 'stok_minimum' => 2]);
        $sp3 = Sparepart::create(['kode_sparepart' => 'REM-DEPAN', 'nama_sparepart' => 'Kampas Rem', 'kategori_id' => $kategori->id, 'harga_beli' => 20000, 'harga_jual' => 30000, 'stok' => 8, 'stok_minimum' => 2]);

        // Uji dry-run: tidak boleh mengubah database
        $this->artisan('pos:standardize-codes', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals('SRV-01', $jasa1->fresh()->kode_jasa);
        $this->assertEquals('OLI-001', $sp1->fresh()->kode_sparepart);

        // Jalankan perintah standardisasi sesungguhnya
        $this->artisan('pos:standardize-codes', ['--no-interaction' => true])
            ->assertSuccessful();

        // Verifikasi ID tidak berubah dan kode terurut rapi
        $this->assertEquals('JS-001', $jasa1->fresh()->kode_jasa);
        $this->assertEquals('JS-002', $jasa2->fresh()->kode_jasa);
        $this->assertEquals('JS-003', $jasa3->fresh()->kode_jasa);

        $this->assertEquals('SP-001', $sp1->fresh()->kode_sparepart);
        $this->assertEquals('SP-002', $sp2->fresh()->kode_sparepart);
        $this->assertEquals('SP-003', $sp3->fresh()->kode_sparepart);

        // Verifikasi data baru selanjutnya melanjutkan nomor terakhir dengan benar
        $this->assertEquals('JS-004', JasaServis::generateNextKode());
        $this->assertEquals('SP-004', Sparepart::generateNextKode());
    }

    public function test_admin_can_delete_mekanik(): void
    {
        $mekanik = Mekanik::create([
            'nama_mekanik' => 'Budi Santoso',
            'telepon' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mekanik.destroy', $mekanik->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data mekanik berhasil dihapus.');
        $this->assertDatabaseMissing('mekaniks', ['id' => $mekanik->id]);
    }

    public function test_admin_can_delete_jasa_servis(): void
    {
        $jasa = JasaServis::create([
            'kode_jasa' => 'JS-001',
            'nama_jasa' => 'Ganti Oli',
            'harga' => 25000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.jasa.destroy', $jasa->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data jasa servis berhasil dihapus.');
        $this->assertDatabaseMissing('jasa_servis', ['id' => $jasa->id]);
    }

    public function test_admin_can_delete_sparepart(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Oli']);
        $sparepart = Sparepart::create([
            'kode_sparepart' => 'SP-001',
            'nama_sparepart' => 'Oli Yamalube 0.8L',
            'kategori_id' => $kategori->id,
            'harga_beli' => 35000,
            'harga_jual' => 45000,
            'stok' => 10,
            'stok_minimum' => 3,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.spareparts.destroy', $sparepart->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data sparepart berhasil dihapus.');
        $this->assertDatabaseMissing('spareparts', ['id' => $sparepart->id]);
    }

    public function test_admin_can_delete_kasir_account(): void
    {
        $kasirUser = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kasir.destroy', $kasirUser->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Akun kasir berhasil dihapus.');
        $this->assertDatabaseMissing('users', ['id' => $kasirUser->id]);
    }

    public function test_admin_cannot_delete_own_account_or_admin_account(): void
    {
        // Mencoba menghapus akun sendiri
        $responseSelf = $this->actingAs($this->admin)->delete(route('admin.kasir.destroy', $this->admin->id));
        $responseSelf->assertRedirect();
        $responseSelf->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // Mencoba menghapus akun admin lain
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $responseOther = $this->actingAs($this->admin)->delete(route('admin.kasir.destroy', $otherAdmin->id));
        $responseOther->assertRedirect();
        $responseOther->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }
}

