@extends('layouts.app')

@section('title', 'Dashboard Kasir')

@section('content')
<!-- Header Welcome -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Kasir</h3>
        <p class="text-muted mb-0">Selamat bekerja, <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    <span class="badge bg-primary fs-6 px-3 py-2 text-uppercase shadow-sm">
        <i class="bi bi-person-badge me-1"></i> Kasir Active
    </span>
</div>

<!-- Quick Action Banner -->
<div class="card border-0 shadow-sm rounded-3 bg-gradient bg-primary text-white mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-cart-plus me-2"></i>Siap Memproses Transaksi?</h4>
            <p class="mb-0 opacity-75">Kelola pelayanan servis motor & pembelian sparepart pelanggan dengan cepat.</p>
        </div>
        <div>
            <button class="btn btn-light btn-lg px-4 py-2 fw-semibold text-primary shadow-sm" disabled>
                <i class="bi bi-plus-circle me-1"></i> Transaksi Baru (Tahap 6)
            </button>
        </div>
    </div>
</div>

<!-- Stat Cards Summary Hari Ini -->
<div class="row g-3 mb-4">
    <!-- Omzet Hari Ini -->
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Omzet Hari Ini</span>
                        <h4 class="fw-bold text-success my-1">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">LUNAS Hari Ini</span>
                    </div>
                    <div class="bg-success text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi LUNAS Hari Ini -->
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Selesai Hari Ini</span>
                        <h4 class="fw-bold text-primary my-1">{{ number_format($transaksiHariIniCount) }}</h4>
                        <span class="text-muted small">Transaksi LUNAS</span>
                    </div>
                    <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Hari Ini -->
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Belum Dibayar</span>
                        <h4 class="fw-bold text-warning my-1">{{ number_format($pendingHariIniCount) }}</h4>
                        <span class="text-muted small">Menunggu pembayaran</span>
                    </div>
                    <div class="bg-warning text-dark rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaksi Terbaru Hari Ini -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Transaksi Hari Ini</h5>
        <span class="badge bg-light text-dark border">Tanggal: {{ date('d/m/Y') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">No. Invoice</th>
                        <th scope="col">Jam</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksiTerbaru as $tx)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $tx->no_invoice ?? '-' }}</td>
                            <td>{{ isset($tx->created_at) ? date('H:i', strtotime($tx->created_at)) : '-' }}</td>
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
                                Belum ada transaksi yang diproses hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
