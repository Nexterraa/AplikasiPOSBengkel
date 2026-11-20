<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan transaksi lunas dengan ringkasan & filter periode.
     * Omzet dihitung berdasarkan tanggal pembayaran (payments.waktu_bayar) sesuai PRD.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        // Query dasar: transaksi lunas yang punya data pembayaran
        // Filter berdasarkan tanggal pembayaran (waktu_bayar) sesuai PRD
        $baseQuery = Transaction::query()
            ->where('status', 'lunas')
            ->whereHas('payment', function ($q) use ($tanggalMulai, $tanggalAkhir) {
                if ($tanggalMulai) {
                    $q->whereDate('waktu_bayar', '>=', $tanggalMulai);
                }
                if ($tanggalAkhir) {
                    $q->whereDate('waktu_bayar', '<=', $tanggalAkhir);
                }
            });

        // Ringkasan data: hitung di database, bukan di PHP
        $totalTransaksi = (clone $baseQuery)->count();
        $totalOmzet     = (float) (clone $baseQuery)->sum('total');

        $totalCash = (float) (clone $baseQuery)->whereHas('payment', function ($q) {
            $q->where('metode', 'cash');
        })->sum('total');

        $totalQris = (float) (clone $baseQuery)->whereHas('payment', function ($q) {
            $q->where('metode', 'qris');
        })->sum('total');

        // Daftar transaksi dengan eager loading (mencegah N+1 query)
        $transactions = (clone $baseQuery)
            ->with(['customer', 'vehicle', 'user', 'payment'])
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', compact(
            'transactions',
            'totalTransaksi',
            'totalOmzet',
            'totalCash',
            'totalQris',
            'tanggalMulai',
            'tanggalAkhir'
        ));
    }

    /**
     * Tampilkan detail satu transaksi untuk laporan.
     */
    public function show(Transaction $transaction): View
    {
        // Hanya transaksi lunas yang bisa dilihat detail laporannya
        if (!$transaction->isLunas()) {
            abort(404, 'Transaksi tidak ditemukan atau belum lunas.');
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

        return view('admin.reports.show', compact('transaction'));
    }
}
