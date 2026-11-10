<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan transaksi lunas dengan ringkasan & filter periode.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'tanggal_mulai.date'           => 'Format tanggal mulai tidak valid.',
            'tanggal_akhir.date'           => 'Format tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
            'start_date.date'              => 'Format tanggal mulai tidak valid.',
            'end_date.date'                => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal'      => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $tanggalMulai = $request->input('tanggal_mulai') ?? $request->input('start_date');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? $request->input('end_date');

        // Query dasar: Hanya transaksi berstatus 'lunas' (sesuai aturan bisnis: omzet & laporan hanya dari transaksi lunas)
        $baseQuery = Transaction::query()->where('status', 'lunas');

        if ($tanggalMulai) {
            $baseQuery->whereDate('created_at', '>=', $tanggalMulai);
        }

        if ($tanggalAkhir) {
            $baseQuery->whereDate('created_at', '<=', $tanggalAkhir);
        }

        // Ringkasan Laporan: Aggregasi dilakukan langsung di database (bukan di PHP memory)
        $totalTransaksi = (clone $baseQuery)->count();
        $totalOmzet = (float) (clone $baseQuery)->sum('total');

        $totalCash = (float) (clone $baseQuery)->whereHas('payment', function ($q) {
            $q->where('metode', 'cash');
        })->sum('total');

        $totalQris = (float) (clone $baseQuery)->whereHas('payment', function ($q) {
            $q->where('metode', 'qris');
        })->sum('total');

        // Daftar transaksi dengan Eager Loading relasi untuk mencegah N+1 Query
        $transactions = (clone $baseQuery)
            ->with(['customer', 'vehicle', 'user', 'payment.user'])
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
     * Tampilkan detail transaksi untuk laporan.
     */
    public function show(Transaction $transaction): View
    {
        // Pastikan hanya transaksi lunas yang dapat diakses di laporan
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
