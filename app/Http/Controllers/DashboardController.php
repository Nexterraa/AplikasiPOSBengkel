<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk Role Admin.
     */
    public function admin(): View
    {
        $hasTransaksiTable = Schema::hasTable('transaksi') || Schema::hasTable('transaksis');
        $hasSparepartTable = Schema::hasTable('sparepart') || Schema::hasTable('spareparts');

        $transaksiTable = Schema::hasTable('transaksi') ? 'transaksi' : 'transaksis';
        $sparepartTable = Schema::hasTable('sparepart') ? 'sparepart' : 'spareparts';

        $totalOmzet = $hasTransaksiTable
            ? (float) DB::table($transaksiTable)->where('status', 'LUNAS')->sum('total')
            : 0;

        $totalTransaksiLunas = $hasTransaksiTable
            ? DB::table($transaksiTable)->where('status', 'LUNAS')->count()
            : 0;

        $totalTransaksiBelumBayar = $hasTransaksiTable
            ? DB::table($transaksiTable)->where('status', 'BELUM DIBAYAR')->count()
            : 0;

        $stokMenipisCount = $hasSparepartTable
            ? DB::table($sparepartTable)->whereColumn('stok', '<=', 'stok_minimum')->count()
            : 0;

        $transaksiTerbaru = $hasTransaksiTable
            ? DB::table($transaksiTable)->orderBy('created_at', 'desc')->limit(5)->get()
            : collect();

        return view('dashboard.admin', compact(
            'totalOmzet',
            'totalTransaksiLunas',
            'totalTransaksiBelumBayar',
            'stokMenipisCount',
            'transaksiTerbaru'
        ));
    }

    /**
     * Dashboard untuk Role Kasir.
     */
    public function kasir(): View
    {
        $hasTransaksiTable = Schema::hasTable('transaksi') || Schema::hasTable('transaksis');
        $transaksiTable = Schema::hasTable('transaksi') ? 'transaksi' : 'transaksis';

        $today = date('Y-m-d');

        $omzetHariIni = $hasTransaksiTable
            ? (float) DB::table($transaksiTable)
                ->where('status', 'LUNAS')
                ->whereDate('created_at', $today)
                ->sum('total')
            : 0;

        $transaksiHariIniCount = $hasTransaksiTable
            ? DB::table($transaksiTable)
                ->where('status', 'LUNAS')
                ->whereDate('created_at', $today)
                ->count()
            : 0;

        $pendingHariIniCount = $hasTransaksiTable
            ? DB::table($transaksiTable)
                ->where('status', 'BELUM DIBAYAR')
                ->whereDate('created_at', $today)
                ->count()
            : 0;

        $transaksiTerbaru = $hasTransaksiTable
            ? DB::table($transaksiTable)
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        return view('dashboard.kasir', compact(
            'omzetHariIni',
            'transaksiHariIniCount',
            'pendingHariIniCount',
            'transaksiTerbaru'
        ));
    }
}
