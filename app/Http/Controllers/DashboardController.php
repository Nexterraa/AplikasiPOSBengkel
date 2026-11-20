<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk Role Admin.
     * Menampilkan omzet seluruh bengkel, grafik, dan stok menipis.
     */
    public function admin(): View
    {
        // Hitung omzet dan jumlah transaksi lunas bulan ini
        $totalOmzet = Transaction::where('status', 'lunas')
            ->sum('total');

        $totalTransaksiLunas = Transaction::where('status', 'lunas')
            ->count();

        $totalTransaksiBelumBayar = Transaction::where('status', 'belum_dibayar')
            ->count();

        // Daftar sparepart yang stoknya menipis atau habis (untuk ditampilkan di dashboard)
        $sparepartMenipis = Sparepart::where('is_active', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->get();

        $stokMenipisCount = $sparepartMenipis->count();

        // 5 Transaksi terbaru (semua status)
        $transaksiTerbaru = Transaction::with(['customer', 'vehicle', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Data grafik penjualan 7 hari terakhir (untuk Chart.js)
        // Format: ['label' => tanggal, 'total' => omzet]
        $grafikData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->format('Y-m-d');
            $label   = now()->subDays($i)->format('d M');

            $omzetHari = Transaction::where('status', 'lunas')
                ->whereDate('updated_at', $tanggal) // updated_at berubah saat status jadi lunas
                ->sum('total');

            $grafikData[] = [
                'label' => $label,
                'total' => (float) $omzetHari,
            ];
        }

        return view('dashboard.admin', compact(
            'totalOmzet',
            'totalTransaksiLunas',
            'totalTransaksiBelumBayar',
            'stokMenipisCount',
            'sparepartMenipis',
            'transaksiTerbaru',
            'grafikData'
        ));
    }

    /**
     * Dashboard untuk Role Kasir.
     * Hanya menghitung transaksi yang dibuat kasir yang sedang login.
     */
    public function kasir(): View
    {
        $kasirId = Auth::id();
        $today   = now()->toDateString();

        // Omzet hari ini dari transaksi kasir ini yang lunas
        $omzetHariIni = Transaction::where('status', 'lunas')
            ->where('user_id', $kasirId)
            ->whereDate('updated_at', $today)
            ->sum('total');

        // Jumlah transaksi lunas hari ini dari kasir ini
        $transaksiHariIniCount = Transaction::where('status', 'lunas')
            ->where('user_id', $kasirId)
            ->whereDate('updated_at', $today)
            ->count();

        // Jumlah transaksi yang belum dibayar hari ini dari kasir ini
        $pendingHariIniCount = Transaction::where('status', 'belum_dibayar')
            ->where('user_id', $kasirId)
            ->whereDate('created_at', $today)
            ->count();

        // 5 transaksi terbaru dari kasir ini
        $transaksiTerbaru = Transaction::with(['customer', 'vehicle'])
            ->where('user_id', $kasirId)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.kasir', compact(
            'omzetHariIni',
            'transaksiHariIniCount',
            'pendingHariIniCount',
            'transaksiTerbaru'
        ));
    }
}
