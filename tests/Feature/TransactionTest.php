<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JasaServis;
use App\Models\Kategori;
use App\Models\Mekanik;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Kategori $kategori;
    private Mekanik $mekanik;
    private JasaServis $jasa;
    private Sparepart $sparepart;
    private Customer $customer;
    private Vehicle $vehicle;

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
            'is_active' => true,
        ]);

        $this->mekanik = Mekanik::create([
            'nama_mekanik' => 'Budi Santoso',
            'telepon' => '081234567890',
            'is_active' => true,
        ]);

        $this->jasa = JasaServis::create([
            'kode_jasa' => 'JS-001',
            'nama_jasa' => 'Servis Ringan Rutin',
            'harga' => 50000,
            'is_active' => true,
        ]);

        $this->sparepart = Sparepart::create([
            'kode_sparepart' => 'SP-001',
            'nama_sparepart' => 'Oli Mesin 10W-40',
            'merek' => 'Yamalube',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 35000,
            'harga_jual' => 45000,
            'wholesale_price' => 40000,
            'wholesale_min_qty' => 5,
            'promo_price' => 42000,
            'stok' => 20,
            'stok_minimum' => 3,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Ahmad Dani',
            'phone' => '08987654321',
        ]);

        $this->vehicle = Vehicle::create([
            'customer_id' => $this->customer->id,
            'plate_number' => 'B 1234 XYZ',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'year' => 2022,
        ]);
    }

    public function test_guest_cannot_access_transaction_pages(): void
    {
        $this->get('/transactions')->assertRedirect('/login');
        $this->get('/transactions/create')->assertRedirect('/login');
        $this->post('/transactions', [])->assertRedirect('/login');
    }

    public function test_admin_and_kasir_can_access_transaction_pages(): void
    {
        $this->actingAs($this->admin)->get('/transactions')->assertStatus(200);
        $this->actingAs($this->admin)->get('/transactions/create')->assertStatus(200);

        $this->actingAs($this->kasir)->get('/transactions')->assertStatus(200);
        $this->actingAs($this->kasir)->get('/transactions/create')->assertStatus(200);
    }

    public function test_can_create_valid_transaction_with_initial_status_belum_dibayar(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'mekanik_id' => $this->mekanik->id,
            'keluhan' => 'Ganti oli dan servis rutin bulanan',
            'jasa_items' => [
                ['jasa_id' => $this->jasa->id, 'qty' => 1],
            ],
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 2],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transactions', 1);

        $transaction = Transaction::first();
        $this->assertEquals('belum_dibayar', $transaction->status);
        $this->assertEquals($this->customer->id, $transaction->customer_id);
        $this->assertEquals($this->vehicle->id, $transaction->vehicle_id);
        $this->assertEquals($this->mekanik->id, $transaction->mekanik_id);

        // Subtotal jasa: 50.000 * 1 = 50.000
        // Subtotal sparepart: 45.000 * 2 = 90.000
        // Grand Total: 140.000
        $this->assertEquals(140000, $transaction->total);
        $this->assertStringStartsWith('INV-' . date('Ymd') . '-', $transaction->no_transaksi);

        $this->assertDatabaseCount('transaction_details', 2);
        $response->assertRedirect(route('transactions.show', $transaction));
    }

    public function test_transaction_without_services_and_without_spareparts_is_rejected(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'jasa_items' => [],
            'sparepart_items' => [],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_mechanic_is_required_if_transaction_has_services(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'mekanik_id' => '', // kosong
            'jasa_items' => [
                ['jasa_id' => $this->jasa->id, 'qty' => 1],
            ],
            'sparepart_items' => [],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);

        $response->assertSessionHasErrors('mekanik_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_mechanic_is_optional_if_only_buying_spareparts(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'mekanik_id' => null,
            'jasa_items' => [],
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transactions', 1);

        $transaction = Transaction::first();
        $this->assertNull($transaction->mekanik_id);
        $this->assertEquals(45000, $transaction->total);
    }

    public function test_inactive_mechanic_is_rejected(): void
    {
        $inactiveMekanik = Mekanik::create([
            'nama_mekanik' => 'Mekanik Nonaktif',
            'telepon' => '089999999',
            'is_active' => false,
        ]);

        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'mekanik_id' => $inactiveMekanik->id,
            'jasa_items' => [
                ['jasa_id' => $this->jasa->id, 'qty' => 1],
            ],
            'sparepart_items' => [],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);

        $response->assertSessionHasErrors('mekanik_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_prices_and_totals_are_calculated_server_side_ignoring_browser_input(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'customer_id' => $this->customer->id,
            'mekanik_id' => $this->mekanik->id,
            'total' => 100, // manipulasi input client
            'jasa_items' => [
                ['jasa_id' => $this->jasa->id, 'qty' => 2, 'harga' => 10, 'subtotal' => 20],
            ],
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1, 'harga' => 5],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);
        $response->assertSessionHasNoErrors();

        $transaction = Transaction::first();
        // Server: Jasa (50.000 * 2 = 100.000) + Sparepart (45.000 * 1 = 45.000) = 145.000
        $this->assertEquals(145000, $transaction->total);

        $jasaDetail = $transaction->details()->where('item_type', 'jasa')->first();
        $this->assertEquals(50000, $jasaDetail->harga);
        $this->assertEquals(100000, $jasaDetail->subtotal);

        $partDetail = $transaction->details()->where('item_type', 'sparepart')->first();
        $this->assertEquals(45000, $partDetail->harga);
        $this->assertEquals(45000, $partDetail->subtotal);
    }

    public function test_wholesale_pricing_rules_validation(): void
    {
        // wholesale_min_qty adalah 5 untuk $this->sparepart (harga grosir 40.000)
        // Jika qty hanya 3, harga grosir ditolak
        $payloadFail = [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'grosir', 'qty' => 3],
            ],
        ];

        $resFail = $this->actingAs($this->kasir)->post('/transactions', $payloadFail);
        $resFail->assertSessionHasErrors('sparepart_items');
        $this->assertDatabaseCount('transactions', 0);

        // Jika qty 5, harga grosir diterima
        $payloadPass = [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'grosir', 'qty' => 5],
            ],
        ];

        $resPass = $this->actingAs($this->kasir)->post('/transactions', $payloadPass);
        $resPass->assertSessionHasNoErrors();

        $transaction = Transaction::first();
        // 40.000 * 5 = 200.000
        $this->assertEquals(200000, $transaction->total);
        $detail = $transaction->details()->where('item_type', 'sparepart')->first();
        $this->assertEquals('grosir', $detail->jenis_harga);
        $this->assertEquals(40000, $detail->harga);
    }

    public function test_promo_pricing_rules_validation(): void
    {
        // $this->sparepart memiliki promo_price 42.000
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'promo', 'qty' => 2],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);
        $response->assertSessionHasNoErrors();

        $transaction = Transaction::first();
        // 42.000 * 2 = 84.000
        $this->assertEquals(84000, $transaction->total);
        $detail = $transaction->details()->where('item_type', 'sparepart')->first();
        $this->assertEquals('promo', $detail->jenis_harga);
        $this->assertEquals(42000, $detail->harga);
    }

    public function test_sparepart_quantity_exceeding_stock_is_rejected(): void
    {
        // Stok sparepart adalah 20. Beli 25 harus ditolak
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 25],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);
        $response->assertSessionHasErrors('sparepart_items');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_stock_is_not_deducted_when_transaction_is_created_or_edited(): void
    {
        $initialStock = $this->sparepart->stok; // 20

        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 5],
            ],
        ];

        $this->actingAs($this->kasir)->post('/transactions', $payload);

        // Pastikan stok tidak berkurang (stok berkurang hanya saat pembayaran lunas di tahap berikutnya)
        $this->assertEquals($initialStock, $this->sparepart->fresh()->stok);

        $transaction = Transaction::first();

        // Coba edit transaksi
        $this->actingAs($this->kasir)->put("/transactions/{$transaction->id}", [
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 10],
            ],
        ]);

        $this->assertEquals($initialStock, $this->sparepart->fresh()->stok);
    }

    public function test_new_customer_and_vehicle_can_be_created_with_transaction(): void
    {
        $payload = [
            'plate_number' => 'd 5678 efg', // huruf kecil akan dinormalisasi ke uppercase
            'brand' => 'Honda',
            'model' => 'Beat Street',
            'year' => 2021,
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '08555123456',
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1],
            ],
        ];

        $response = $this->actingAs($this->kasir)->post('/transactions', $payload);
        $response->assertSessionHasNoErrors();

        // Customer baru dibuat
        $this->assertDatabaseHas('customers', [
            'name' => 'Siti Nurhaliza',
            'phone' => '08555123456',
        ]);

        // Kendaraan baru dibuat dengan plat ternormalisasi
        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'D 5678 EFG',
            'brand' => 'Honda',
            'model' => 'Beat Street',
        ]);

        $transaction = Transaction::first();
        $this->assertEquals('D 5678 EFG', $transaction->vehicle->plate_number);
        $this->assertEquals('Siti Nurhaliza', $transaction->customer->name);
    }

    public function test_transaction_belum_dibayar_can_be_edited(): void
    {
        $transaction = Transaction::create([
            'no_transaksi' => 'INV-20261022-0001',
            'no_invoice' => 'INV-20261022-0001',
            'user_id' => $this->kasir->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'mekanik_id' => $this->mekanik->id,
            'keluhan' => 'Keluhan lama',
            'status' => 'belum_dibayar',
            'total' => 50000,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'item_type' => 'jasa',
            'jasa_servis_id' => $this->jasa->id,
            'nama_item' => $this->jasa->nama_jasa,
            'harga' => 50000,
            'jumlah' => 1,
            'subtotal' => 50000,
        ]);

        $this->actingAs($this->kasir)->get("/transactions/{$transaction->id}/edit")->assertStatus(200);

        // Edit dengan menambah sparepart
        $updatePayload = [
            'mekanik_id' => $this->mekanik->id,
            'keluhan' => 'Keluhan baru diperbarui',
            'jasa_items' => [
                ['jasa_id' => $this->jasa->id, 'qty' => 1],
            ],
            'sparepart_items' => [
                ['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1],
            ],
        ];

        $response = $this->actingAs($this->kasir)->put("/transactions/{$transaction->id}", $updatePayload);
        $response->assertSessionHasNoErrors();

        $fresh = $transaction->fresh();
        $this->assertEquals('Keluhan baru diperbarui', $fresh->keluhan);
        // Total baru: 50.000 + 45.000 = 95.000
        $this->assertEquals(95000, $fresh->total);
        $this->assertEquals(2, $fresh->details()->count());
    }

    public function test_paid_transaction_cannot_be_edited(): void
    {
        $paidTrx = Transaction::create([
            'no_transaksi' => 'INV-20261022-0099',
            'no_invoice' => 'INV-20261022-0099',
            'user_id' => $this->kasir->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'lunas',
            'total' => 50000,
        ]);

        // Akses form edit ditolak 403
        $this->actingAs($this->kasir)->get("/transactions/{$paidTrx->id}/edit")->assertStatus(403);

        // Request update ditolak 403
        $response = $this->actingAs($this->kasir)->put("/transactions/{$paidTrx->id}", [
            'keluhan' => 'Coba manipulasi',
        ]);

        $response->assertStatus(403);
    }

    public function test_invoice_numbers_are_unique_and_sequential(): void
    {
        $todayPrefix = 'INV-' . date('Ymd') . '-';

        $trx1 = $this->actingAs($this->kasir)->post('/transactions', [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1]],
        ]);
        $trx1->assertSessionHasNoErrors();

        $trx2 = $this->actingAs($this->kasir)->post('/transactions', [
            'vehicle_id' => $this->vehicle->id,
            'sparepart_items' => [['sparepart_id' => $this->sparepart->id, 'price_type' => 'satuan', 'qty' => 1]],
        ]);
        $trx2->assertSessionHasNoErrors();

        $transactions = Transaction::orderBy('id')->get();
        $this->assertEquals($todayPrefix . '0001', $transactions[0]->no_transaksi);
        $this->assertEquals($todayPrefix . '0002', $transactions[1]->no_transaksi);
        $this->assertNotEquals($transactions[0]->no_transaksi, $transactions[1]->no_transaksi);
    }
}

