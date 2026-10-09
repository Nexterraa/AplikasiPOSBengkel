@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<!-- Header Welcome -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Admin</h3>
        <p class="text-muted mb-0">Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase shadow-sm">
        <i class="bi bi-shield-lock me-1"></i> Administrator
    </span>
</div>

<!-- Stat Cards Summary -->
<div class="row g-3 mb-4">
    <!-- Total Omzet -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Omzet Lunas</span>
                        <h4 class="fw-bold text-success my-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Hanya Transaksi LUNAS</span>
                    </div>
                    <div class="bg-success text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi LUNAS -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Transaksi Lunas</span>
                        <h4 class="fw-bold text-primary my-1">{{ number_format($totalTransaksiLunas) }}</h4>
                        <span class="text-muted small">Transaksi selesai</span>
                    </div>
                    <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Belum Dibayar -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Belum Dibayar</span>
                        <h4 class="fw-bold text-warning my-1">{{ number_format($totalTransaksiBelumBayar) }}</h4>
                        <span class="text-muted small">Dalam antrean/proses</span>
                    </div>
                    <div class="bg-warning text-dark rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Menipis/Habis -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Stok Menipis</span>
                        <h4 class="fw-bold text-danger my-1">{{ number_format($stokMenipisCount) }}</h4>
                        <span class="text-muted small">Perlu restok segera</span>
                    </div>
                    <div class="bg-danger text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-box-seam-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Layout: Transaksi Terbaru & Quick Menu -->
<div class="row g-4">
    <!-- Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Transaksi Terbaru</h5>
                <span class="badge bg-light text-dark border">5 Terbaru</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">No. Invoice</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Pelanggan</th>
                                <th scope="col">Total</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksiTerbaru as $tx)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $tx->no_invoice ?? '-' }}</td>
                                    <td>{{ isset($tx->created_at) ? date('d/m/Y H:i', strtotime($tx->created_at)) : '-' }}</td>
                                    <td>{{ $tx->nama_pelanggan ?? '-' }}</td>
                                    <td class="fw-bold">Rp {{ number_format($tx->total ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        @if (($tx->status ?? '') === 'LUNAS')
                                            <span class="badge bg-success">LUNAS</span>
                                        @elseif (($tx->status ?? '') === 'BATAL')
                                            <span class="badge bg-secondary">BATAL</span>
                                        @else
                                            <span class="badge bg-warning text-dark">BELUM DIBAYAR</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data transaksi yang tercatat dalam sistem.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Informasi Quick Akses -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-gear-fill me-2 text-primary"></i>Akses Pintar Admin</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary text-start d-flex align-items-center p-3 rounded-3" disabled>
                        <i class="bi bi-tools fs-4 me-3 text-primary"></i>
                        <div>
                            <div class="fw-bold">Kelola Data Master</div>
                            <small class="text-muted">Mekanik, Jasa, & Kategori (Tahap 4)</small>
                        </div>
                    </button>
                    <button class="btn btn-outline-success text-start d-flex align-items-center p-3 rounded-3" disabled>
                        <i class="bi bi-box-seam fs-4 me-3 text-success"></i>
                        <div>
                            <div class="fw-bold">Stok & Sparepart</div>
                            <small class="text-muted">Kelola stok minimum & barang (Tahap 5)</small>
                        </div>
                    </button>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary text-start d-flex align-items-center p-3 rounded-3">
                        <i class="bi bi-person-gear fs-4 me-3 text-secondary"></i>
                        <div>
                            <div class="fw-bold">Pengaturan Akun</div>
                            <small class="text-muted">Ubah profil & password pengguna</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Status Tahap 3</h5>
                <p class="small mb-0">Dashboard Admin telah terintegrasi dengan database secara dinamis dan siap digunakan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
