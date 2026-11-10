<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JasaServis;
use App\Models\Kategori;
use App\Models\Mekanik;
use App\Models\Payment;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Customer $customer;
    private Vehicle $vehicle;
    private Mekanik $mekanik;
    private Kategori $kategori;
    private Sparepart $sparepart;
    private JasaServis $jasa;

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

        $this->customer = Customer::create([
            'name' => 'Bambang Supriyadi',
            'phone' => '08123456789',
        ]);

        $this->vehicle = Vehicle::create([
            'customer_id' => $this->customer->id,
            'plate_number' => 'B 1234 ABC',
            'brand' => 'Honda',
            'model' => 'Vario 150',
            'year' => 2021,
        ]);

        $this->mekanik = Mekanik::create([
            'nama_mekanik' => 'Agus Santoso',
            'telepon' => '08987654321',
            'is_active' => true,
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Oli & Pelumas',
            'is_active' => true,
        ]);

        $this->sparepart = Sparepart::create([
            'kode_sparepart' => 'SP-REP-01',
            'nama_sparepart' => 'Oli MPX2',
            'kategori_id' => $this->kategori->id,
            'harga_beli' => 40000,
            'harga_jual' => 55000,
            'stok' => 20,
            'stok_minimum' => 5,
            'is_active' => true,
        ]);

        $this->jasa = JasaServis::create([
            'kode_jasa' => 'JS-REP-01',
            'nama_jasa' => 'Ganti Oli Mesin',
            'harga' => 15000,
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi dan detailnya.
     */
    private function createTransaction(string $status, float $total, ?string $createdAt = null): Transaction
    {
        $noTrx = 'INV-' . ($createdAt ? date('Ymd', strtotime($createdAt)) : date('Ymd')) . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $trx = new Transaction([
            'no_transaksi' => $noTrx,
            'no_invoice'   => $noTrx,
            'user_id'      => $this->kasir->id,
            'customer_id'  => $this->customer->id,
            'vehicle_id'   => $this->vehicle->id,
            'mekanik_id'   => $this->mekanik->id,
            'keluhan'      => 'Ganti oli rutin',
            'status'       => $status,
            'total'        => $total,
        ]);

        if ($createdAt) {
            $trx->created_at = Carbon::parse($createdAt);
            $trx->updated_at = Carbon::parse($createdAt);
        }

        $trx->save();

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'item_type'      => 'jasa',
            'jasa_servis_id' => $this->jasa->id,
            'nama_item'      => $this->jasa->nama_jasa,
            'harga'          => 15000,
            'jumlah'         => 1,
            'subtotal'       => 15000,
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'item_type'      => 'sparepart',
            'sparepart_id'   => $this->sparepart->id,
            'nama_item'      => $this->sparepart->nama_sparepart,
            'jenis_harga'    => 'satuan',
            'harga'          => $total - 15000,
            'jumlah'         => 1,
            'subtotal'       => $total - 15000,
        ]);

        return $trx;
    }

    /**
     * Helper untuk membuat pembayaran pada transaksi.
     */
    private function attachPayment(Transaction $trx, string $metode, ?string $paidAt = null): Payment
    {
        $paidTime = $paidAt ? Carbon::parse($paidAt) : ($trx->created_at ?? now());

        $payment = new Payment([
            'transaction_id'  => $trx->id,
            'user_id'         => $this->kasir->id,
            'metode'          => $metode,
            'total_bayar'     => $trx->total,
            'jumlah_diterima' => $trx->total,
            'kembalian'       => 0,
            'catatan'         => 'Lunas',
            'waktu_bayar'     => $paidTime,
        ]);

        $payment->created_at = $paidTime;
        $payment->updated_at = $paidTime;
        $payment->save();

        return $payment;
    }

    /**
     * Test 1: Transaksi lunas muncul di laporan.
     */
    public function test_paid_transactions_appear_in_report(): void
    {
        $paidTrx = $this->createTransaction('lunas', 70000);
        $this->attachPayment($paidTrx, 'cash');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee($paidTrx->no_transaksi);
        $response->assertSee('B 1234 ABC');
        $response->assertSee('Bambang Supriyadi');
        $response->assertSee('Rp 70.000');
    }

    /**
     * Test 2: Transaksi belum_dibayar tidak masuk laporan.
     */
    public function test_unpaid_transactions_do_not_appear_in_report(): void
    {
        $unpaidTrx = $this->createTransaction('belum_dibayar', 100000);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertDontSee($unpaidTrx->no_transaksi);
    }

    /**
     * Test 3: Omzet hanya menghitung transaksi lunas.
     */
    public function test_total_revenue_only_calculates_paid_transactions(): void
    {
        // 2 Transaksi Lunas: 70.000 + 80.000 = 150.000
        $t1 = $this->createTransaction('lunas', 70000);
        $this->attachPayment($t1, 'cash');

        $t2 = $this->createTransaction('lunas', 80000);
        $this->attachPayment($t2, 'qris');

        // 1 Transaksi Belum Dibayar: 500.000 (tidak boleh masuk omzet)
        $this->createTransaction('belum_dibayar', 500000);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        // Total omzet yang tampil harus Rp 150.000, bukan 650.000
        $response->assertSee('Rp 150.000');
        $response->assertDontSee('Rp 650.000');
    }

    /**
     * Test 4: Jumlah transaksi hanya menghitung transaksi lunas.
     */
    public function test_transaction_count_only_calculates_paid_transactions(): void
    {
        $t1 = $this->createTransaction('lunas', 50000);
        $this->attachPayment($t1, 'cash');

        $t2 = $this->createTransaction('lunas', 60000);
        $this->attachPayment($t2, 'cash');

        // 3 Transaksi belum dibayar
        $this->createTransaction('belum_dibayar', 30000);
        $this->createTransaction('belum_dibayar', 40000);
        $this->createTransaction('belum_dibayar', 50000);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalTransaksi', 2);
    }

    /**
     * Test 5: Filter tanggal bekerja.
     */
    public function test_date_filter_filters_transactions_correctly(): void
    {
        // Transaksi di tanggal 10 Oktober 2026
        $t1 = $this->createTransaction('lunas', 50000, '2026-10-10 10:00:00');
        $this->attachPayment($t1, 'cash', '2026-10-10 10:00:00');

        // Transaksi di tanggal 15 Oktober 2026
        $t2 = $this->createTransaction('lunas', 60000, '2026-10-15 14:00:00');
        $this->attachPayment($t2, 'cash', '2026-10-15 14:00:00');

        // Filter periode 12 s/d 16 Oktober 2026
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'tanggal_mulai' => '2026-10-12',
            'tanggal_akhir' => '2026-10-16',
        ]));

        $response->assertStatus(200);
        $response->assertSee($t2->no_transaksi);
        $response->assertDontSee($t1->no_transaksi);
        $response->assertViewHas('totalTransaksi', 1);
        $response->assertViewHas('totalOmzet', 60000.0);
    }

    /**
     * Test 6: Transaksi di luar periode filter tidak muncul.
     */
    public function test_transactions_outside_date_filter_range_do_not_appear(): void
    {
        $oldTrx = $this->createTransaction('lunas', 100000, '2026-09-01 10:00:00');
        $this->attachPayment($oldTrx, 'cash', '2026-09-01 10:00:00');

        $futureTrx = $this->createTransaction('lunas', 120000, '2026-11-20 10:00:00');
        $this->attachPayment($futureTrx, 'cash', '2026-11-20 10:00:00');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_akhir' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $response->assertDontSee($oldTrx->no_transaksi);
        $response->assertDontSee($futureTrx->no_transaksi);
        $response->assertViewHas('totalTransaksi', 0);
        $response->assertViewHas('totalOmzet', 0.0);
    }

    /**
     * Test 7: Cash dan QRIS dihitung sesuai data pembayaran.
     */
    public function test_cash_and_qris_totals_calculated_correctly_from_payments(): void
    {
        // 2 Transaksi Cash: 50.000 + 70.000 = 120.000
        $c1 = $this->createTransaction('lunas', 50000);
        $this->attachPayment($c1, 'cash');

        $c2 = $this->createTransaction('lunas', 70000);
        $this->attachPayment($c2, 'cash');

        // 1 Transaksi QRIS: 85.000
        $q1 = $this->createTransaction('lunas', 85000);
        $this->attachPayment($q1, 'qris');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalCash', 120000.0);
        $response->assertViewHas('totalQris', 85000.0);
        $response->assertViewHas('totalOmzet', 205000.0);
    }

    /**
     * Test 8: User tanpa authorization tidak dapat mengakses laporan.
     */
    public function test_unauthorized_user_cannot_access_reports(): void
    {
        // Guest tidak boleh akses -> redirect ke login
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));

        // Kasir dilarang akses laporan admin -> 403 Forbidden
        $this->actingAs($this->kasir)->get(route('admin.reports.index'))->assertStatus(403);
    }

    /**
     * Test 9: Detail laporan menampilkan data transaksi yang benar.
     */
    public function test_report_detail_displays_correct_transaction_data(): void
    {
        $trx = $this->createTransaction('lunas', 70000);
        $this->attachPayment($trx, 'cash');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.show', $trx));

        $response->assertStatus(200);
        $response->assertSee($trx->no_transaksi);
        $response->assertSee('Bambang Supriyadi');
        $response->assertSee('B 1234 ABC');
        $response->assertSee('Agus Santoso'); // Mekanik
        $response->assertSee('Ganti Oli Mesin'); // Jasa snapshot
        $response->assertSee('Oli MPX2'); // Sparepart snapshot
        $response->assertSee('cash');
        $response->assertSee('Rp 70.000');
    }

    /**
     * Test 10: Detail laporan untuk transaksi belum lunas ditolak 404.
     */
    public function test_report_detail_for_unpaid_transaction_is_rejected(): void
    {
        $unpaidTrx = $this->createTransaction('belum_dibayar', 70000);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.show', $unpaidTrx));

        $response->assertStatus(404);
    }

    /**
     * Test 11: Validasi tanggal akhir tidak boleh lebih awal dari tanggal mulai.
     */
    public function test_invalid_date_filter_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'tanggal_mulai' => '2026-10-20',
            'tanggal_akhir' => '2026-10-10', // Lebih awal dari mulai
        ]));

        $response->assertSessionHasErrors('tanggal_akhir');
    }
}
