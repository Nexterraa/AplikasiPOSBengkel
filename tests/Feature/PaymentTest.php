<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JasaServis;
use App\Models\Kategori;
use App\Models\Mekanik;
use App\Models\Payment;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
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
            'nama_kategori' => 'Oli',
            'is_active' => true,
        ]);

        $this->mekanik = Mekanik::create([
            'nama_mekanik' => 'Budi',
            'telepon' => '081234567890',
            'is_active' => true,
        ]);

        $this->jasa = JasaServis::create([
            'kode_jasa' => 'JS-PAY-001',
            'nama_jasa' => 'Servis Ringan',
            'harga' => 50000,
            'is_active' => true,
        ]);

        $this->sparepart = Sparepart::create([
            'kode_sparepart' => 'SP-PAY-001',
            'nama_sparepart' => 'Oli Mesin',
            'merek' => 'Yamalube',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 30000,
            'harga_jual' => 45000,
            'stok' => 10,
            'stok_minimum' => 2,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Pelanggan Test',
            'phone' => '089876543210',
        ]);

        $this->vehicle = Vehicle::create([
            'customer_id' => $this->customer->id,
            'plate_number' => 'Z 9999 PAY',
            'brand' => 'Honda',
            'model' => 'Vario',
            'year' => 2023,
        ]);
    }

    /**
     * Helper: buat transaksi belum dibayar dengan jasa + sparepart.
     */
    private function createPendingTransaction(int $sparepartQty = 2): Transaction
    {
        $jasaSubtotal = 50000;
        $partSubtotal = 45000 * $sparepartQty;
        $total = $jasaSubtotal + $partSubtotal;

        $trx = Transaction::create([
            'no_transaksi' => 'INV-' . date('Ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'no_invoice'   => null,
            'user_id'      => $this->kasir->id,
            'customer_id'  => $this->customer->id,
            'vehicle_id'   => $this->vehicle->id,
            'mekanik_id'   => $this->mekanik->id,
            'keluhan'      => 'Ganti oli',
            'status'       => 'belum_dibayar',
            'total'        => $total,
        ]);

        $trx->update(['no_invoice' => $trx->no_transaksi]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'item_type'      => 'jasa',
            'jasa_servis_id' => $this->jasa->id,
            'nama_item'      => $this->jasa->nama_jasa,
            'harga'          => 50000,
            'jumlah'         => 1,
            'subtotal'       => 50000,
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'item_type'      => 'sparepart',
            'sparepart_id'   => $this->sparepart->id,
            'nama_item'      => $this->sparepart->nama_sparepart,
            'jenis_harga'    => 'satuan',
            'harga'          => 45000,
            'jumlah'         => $sparepartQty,
            'subtotal'       => $partSubtotal,
        ]);

        return $trx;
    }

    public function test_guest_cannot_access_payment_page(): void
    {
        $trx = $this->createPendingTransaction();

        $this->get("/transactions/{$trx->id}/payment")->assertRedirect('/login');
        $this->post("/transactions/{$trx->id}/pay")->assertRedirect('/login');
    }

    public function test_kasir_can_view_payment_form_for_pending_transaction(): void
    {
        $trx = $this->createPendingTransaction();

        $response = $this->actingAs($this->kasir)->get("/transactions/{$trx->id}/payment");
        $response->assertStatus(200);
        $response->assertSee($trx->no_transaksi);
        $response->assertSee('Proses Pembayaran');
    }

    public function test_cash_payment_with_exact_amount_succeeds(): void
    {
        $trx = $this->createPendingTransaction();
        $total = $trx->total; // 50000 + 90000 = 140000

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $total,
        ]);

        $response->assertSessionHasNoErrors();

        $trx->refresh();
        $this->assertEquals('lunas', $trx->status);

        $payment = Payment::where('transaction_id', $trx->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('cash', $payment->metode);
        $this->assertEquals($total, $payment->total_bayar);
        $this->assertEquals($total, $payment->jumlah_diterima);
        $this->assertEquals(0, $payment->kembalian);
    }

    public function test_cash_payment_with_overpayment_calculates_change(): void
    {
        $trx = $this->createPendingTransaction();
        $total = $trx->total; // 140000

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => 200000,
        ]);

        $response->assertSessionHasNoErrors();

        $payment = Payment::where('transaction_id', $trx->id)->first();
        $this->assertEquals(200000, $payment->jumlah_diterima);
        $this->assertEquals(200000 - $total, $payment->kembalian);
    }

    public function test_cash_payment_rejected_if_amount_less_than_total(): void
    {
        $trx = $this->createPendingTransaction();

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => 10000, // kurang dari total
        ]);

        $response->assertSessionHasErrors('jumlah_diterima');

        $trx->refresh();
        $this->assertEquals('belum_dibayar', $trx->status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_qris_manual_payment_succeeds(): void
    {
        $trx = $this->createPendingTransaction();
        $total = $trx->total;

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode' => 'qris',
        ]);

        $response->assertSessionHasNoErrors();

        $trx->refresh();
        $this->assertEquals('lunas', $trx->status);

        $payment = Payment::where('transaction_id', $trx->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('qris', $payment->metode);
        $this->assertEquals($total, $payment->total_bayar);
        $this->assertEquals($total, $payment->jumlah_diterima);
        $this->assertEquals(0, $payment->kembalian);
    }

    public function test_duplicate_payment_is_rejected(): void
    {
        $trx = $this->createPendingTransaction();

        // Bayar pertama kali
        $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $trx->refresh();
        $this->assertEquals('lunas', $trx->status);

        // Coba bayar lagi
        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $response->assertSessionHasErrors('pembayaran');
        $this->assertDatabaseCount('payments', 1); // tetap 1
    }

    public function test_paid_transaction_cannot_be_paid_again(): void
    {
        $trx = $this->createPendingTransaction();
        $trx->update(['status' => 'lunas']);

        $response = $this->actingAs($this->kasir)->get("/transactions/{$trx->id}/payment");
        $response->assertStatus(403);

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $response->assertSessionHasErrors('pembayaran');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_stock_is_deducted_only_after_successful_payment(): void
    {
        $initialStock = $this->sparepart->stok; // 10
        $qty = 2;
        $trx = $this->createPendingTransaction($qty);

        // Sebelum bayar, stok belum berkurang
        $this->assertEquals($initialStock, $this->sparepart->fresh()->stok);

        // Bayar
        $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        // Setelah bayar, stok berkurang
        $this->assertEquals($initialStock - $qty, $this->sparepart->fresh()->stok);
    }

    public function test_stock_not_deducted_if_payment_fails_insufficient_money(): void
    {
        $initialStock = $this->sparepart->stok;
        $trx = $this->createPendingTransaction();

        // Bayar gagal (uang kurang)
        $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => 100, // kurang
        ]);

        // Stok tetap
        $this->assertEquals($initialStock, $this->sparepart->fresh()->stok);
    }

    public function test_stock_not_deducted_if_stock_insufficient_at_payment(): void
    {
        // Set stok jadi 1, tapi transaksi butuh 2
        $this->sparepart->update(['stok' => 1]);

        $trx = $this->createPendingTransaction(2);

        $response = $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $response->assertSessionHasErrors('stok');

        $trx->refresh();
        $this->assertEquals('belum_dibayar', $trx->status);
        $this->assertDatabaseCount('payments', 0);
        $this->assertEquals(1, $this->sparepart->fresh()->stok);
    }

    public function test_stock_movement_is_recorded_after_payment(): void
    {
        $trx = $this->createPendingTransaction(3);

        $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $movement = StockMovement::where('sparepart_id', $this->sparepart->id)
            ->where('jenis', 'keluar')
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(3, $movement->jumlah);
        $this->assertEquals(10, $movement->stok_sebelum);
        $this->assertEquals(7, $movement->stok_sesudah);
        $this->assertStringContains($trx->no_transaksi, $movement->catatan);
    }

    public function test_invoice_only_accessible_for_paid_transactions(): void
    {
        $trx = $this->createPendingTransaction();

        // Belum dibayar → 403
        $response = $this->actingAs($this->kasir)->get("/transactions/{$trx->id}/invoice");
        $response->assertStatus(403);

        // Bayar
        $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        // Sudah lunas → 200
        $response = $this->actingAs($this->kasir)->get("/transactions/{$trx->id}/invoice");
        $response->assertStatus(200);
        $response->assertSee($trx->no_transaksi);
        $response->assertSee('LUNAS');
    }

    public function test_admin_can_also_process_payment(): void
    {
        $trx = $this->createPendingTransaction();

        $response = $this->actingAs($this->admin)->post("/transactions/{$trx->id}/pay", [
            'metode'          => 'cash',
            'jumlah_diterima' => $trx->total,
        ]);

        $response->assertSessionHasNoErrors();

        $trx->refresh();
        $this->assertEquals('lunas', $trx->status);

        $payment = Payment::where('transaction_id', $trx->id)->first();
        $this->assertEquals($this->admin->id, $payment->user_id);
    }

    public function test_inactive_user_cannot_access_payment_or_pay(): void
    {
        $inactiveKasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $trx = $this->createPendingTransaction();

        $this->actingAs($inactiveKasir)->get("/transactions/{$trx->id}/payment")->assertRedirect('/login');
        $this->actingAs($inactiveKasir)->post("/transactions/{$trx->id}/pay", [
            'metode' => 'qris',
        ])->assertRedirect('/login');
    }

    public function test_atomic_transaction_rolls_back_if_failure_occurs(): void
    {
        $initialStock = $this->sparepart->stok;
        $trx = $this->createPendingTransaction(2);

        // Pasang event listener pada StockMovement::creating untuk mensimulasikan kegagalan sistem di tengah proses
        StockMovement::saving(function () {
            throw new \Exception('Simulasi kegagalan pencatatan stock movement');
        });

        try {
            $this->actingAs($this->kasir)->post("/transactions/{$trx->id}/pay", [
                'metode'          => 'cash',
                'jumlah_diterima' => $trx->total,
            ]);
        } catch (\Exception $e) {
            // Expected simulation exception
        }

        // Verifikasi semua perubahan di-rollback
        $trx->refresh();
        $this->assertEquals('belum_dibayar', $trx->status);
        $this->assertEquals($initialStock, $this->sparepart->fresh()->stok);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    /**
     * Custom assertion: check string contains substring.
     */
    private function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'."
        );
    }
}

