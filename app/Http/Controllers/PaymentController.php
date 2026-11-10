<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sparepart;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Tampilkan formulir proses pembayaran untuk transaksi belum dibayar.
     */
    public function create(Transaction $transaction): View
    {
        if (!$transaction->isBelumDibayar()) {
            abort(403, 'Transaksi ini sudah lunas atau tidak valid untuk dibayar.');
        }

        $transaction->load(['customer', 'vehicle', 'mekanik', 'details.jasaServis', 'details.sparepart']);

        return view('transactions.payment', compact('transaction'));
    }

    /**
     * Proses pembayaran secara atomik dengan DB::transaction().
     */
    public function store(Request $request, Transaction $transaction): RedirectResponse
    {
        return DB::transaction(function () use ($request, $transaction) {
            // 1. Kunci dan periksa ulang transaksi
            $trx = Transaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            // 2. Pastikan status masih belum_dibayar
            if (!$trx->isBelumDibayar()) {
                throw ValidationException::withMessages([
                    'pembayaran' => ['Transaksi ini sudah berstatus ' . strtoupper($trx->status) . ' dan tidak dapat dibayar lagi.'],
                ]);
            }

            // 3. Periksa ketersediaan stok seluruh sparepart
            $sparepartDetails = $trx->sparepartDetails()->get();
            $groupedQuantities = [];
            foreach ($sparepartDetails as $detail) {
                if ($detail->sparepart_id) {
                    $groupedQuantities[$detail->sparepart_id] = ($groupedQuantities[$detail->sparepart_id] ?? 0) + $detail->jumlah;
                }
            }

            foreach ($groupedQuantities as $sparepartId => $totalRequired) {
                $sparepart = Sparepart::where('id', $sparepartId)->lockForUpdate()->first();
                if (!$sparepart) {
                    throw ValidationException::withMessages([
                        'stok' => ["Sparepart tidak ditemukan."],
                    ]);
                }
                if ($sparepart->stok < $totalRequired) {
                    throw ValidationException::withMessages([
                        'stok' => ["Stok untuk '{$sparepart->nama_sparepart}' tidak mencukupi (Tersedia: {$sparepart->stok}, Dibutuhkan: {$totalRequired}). Pembayaran dibatalkan."],
                    ]);
                }
            }

            // 4. Validasi pembayaran
            $request->validate([
                'metode'          => ['required', 'string', 'in:cash,qris'],
                'jumlah_diterima' => ['nullable', 'numeric'],
                'catatan'         => ['nullable', 'string', 'max:500'],
            ], [
                'metode.required' => 'Metode pembayaran wajib dipilih.',
                'metode.in'       => 'Metode pembayaran harus Cash atau QRIS.',
            ]);

            $metode = strtolower($request->input('metode'));
            $total = (float) $trx->total;
            $jumlahDiterima = 0;
            $kembalian = 0;

            if ($metode === 'cash') {
                $jumlahDiterima = (float) $request->input('jumlah_diterima');
                if ($jumlahDiterima < $total) {
                    throw ValidationException::withMessages([
                        'jumlah_diterima' => ['Jumlah uang yang diterima (Rp ' . number_format($jumlahDiterima, 0, ',', '.') . ') kurang dari total tagihan (Rp ' . number_format($total, 0, ',', '.') . ').'],
                    ]);
                }
                $kembalian = $jumlahDiterima - $total;
            } else { // QRIS Manual
                $jumlahDiterima = $total;
                $kembalian = 0;
            }

            // 5. Simpan data pembayaran
            Payment::create([
                'transaction_id'  => $trx->id,
                'user_id'         => Auth::id() ?? 1,
                'metode'          => $metode,
                'total_bayar'     => $total,
                'jumlah_diterima' => $jumlahDiterima,
                'kembalian'       => $kembalian,
                'catatan'         => $request->input('catatan'),
                'waktu_bayar'     => now(),
            ]);

            // 6. Ubah status transaksi menjadi lunas
            $trx->update([
                'status' => 'lunas',
            ]);

            // 7 & 8. Kurangi stok dan catat pergerakan stok
            foreach ($sparepartDetails as $detail) {
                if ($detail->sparepart_id) {
                    $sparepart = Sparepart::where('id', $detail->sparepart_id)->lockForUpdate()->first();
                    $stokSebelum = $sparepart->stok;
                    $stokSesudah = $stokSebelum - $detail->jumlah;

                    $sparepart->update(['stok' => $stokSesudah]);

                    StockMovement::create([
                        'sparepart_id' => $sparepart->id,
                        'user_id'      => Auth::id() ?? 1,
                        'jenis'        => 'keluar',
                        'jumlah'       => $detail->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'catatan'      => "Penjualan transaksi {$trx->no_transaksi}",
                    ]);
                }
            }

            return redirect()->route('transactions.invoice', $trx)
                ->with('success', "Pembayaran untuk transaksi {$trx->no_transaksi} berhasil diproses. Status transaksi: LUNAS.");
        });
    }

    /**
     * Tampilkan dan cetak invoice resmi untuk transaksi lunas.
     */
    public function invoice(Transaction $transaction): View
    {
        if (!$transaction->isLunas()) {
            abort(403, 'Invoice hanya dapat diakses dan dicetak jika transaksi sudah lunas.');
        }

        $transaction->load([
            'customer',
            'vehicle',
            'mekanik',
            'user',
            'payment.user',
            'details.jasaServis',
            'details.sparepart',
        ]);

        return view('transactions.invoice', compact('transaction'));
    }
}

