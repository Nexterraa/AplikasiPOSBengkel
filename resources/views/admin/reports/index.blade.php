@extends('layouts.app')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i> Laporan Transaksi</h4>
        <p class="text-muted mb-0 small">Pantau rekap omzet, jumlah transaksi, dan riwayat pembayaran transaksi yang telah lunas.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan Filter:</h6>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Summary Cards (Hanya Transaksi Lunas) -->
<div class="row g-3 mb-4">
    <!-- Jumlah Transaksi -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
                    <i class="bi bi-receipt-cutoff fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Transaksi Lunas</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalTransaksi, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Omzet -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="bg-success-subtle text-success p-3 rounded-3 me-3">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Omzet</div>
                    <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembayaran Cash -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="bg-info-subtle text-info p-3 rounded-3 me-3">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Cash</div>
                    <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalCash, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembayaran QRIS -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="bg-warning-subtle text-warning p-3 rounded-3 me-3">
                    <i class="bi bi-qr-code-scan fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total QRIS</div>
                    <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalQris, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Filter Periode Tanggal -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white fw-bold py-3 border-bottom d-flex align-items-center">
        <i class="bi bi-funnel me-2 text-primary"></i> Filter Periode Laporan
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="tanggal_mulai" class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ $tanggalMulai }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label small fw-semibold text-muted">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control @error('tanggal_akhir') is-invalid @enderror" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
                @if ($tanggalMulai || $tanggalAkhir)
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan Transaksi -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check me-2 text-primary"></i> Daftar Transaksi Berhasil</span>
        @if ($tanggalMulai || $tanggalAkhir)
            <span class="badge bg-light text-dark border small">
                Periode: {{ $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') : 'Awal' }} s.d. {{ $tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') : 'Sekarang' }}
            </span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">No. Invoice</th>
                        <th>Tanggal Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Plat Nomor</th>
                        <th>Kasir</th>
                        <th>Metode</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                {{ $trx->no_transaksi }}
                            </td>
                            <td class="small text-muted">
                                {{ $trx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $trx->customer->name ?? '-' }}</div>
                                <div class="small text-secondary">{{ $trx->customer->phone ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace">{{ $trx->vehicle->plate_number ?? '-' }}</span>
                                <div class="small text-muted">{{ $trx->vehicle->brand ?? '' }} {{ $trx->vehicle->model ?? '' }}</div>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">
                                    {{ $trx->payment && $trx->payment->user ? $trx->payment->user->name : ($trx->user->name ?? 'Kasir') }}
                                </span>
                            </td>
                            <td>
                                @if ($trx->payment)
                                    <span class="badge {{ $trx->payment->isCash() ? 'bg-info text-dark' : 'bg-warning text-dark' }} text-uppercase">
                                        {{ $trx->payment->metode }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> LUNAS
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.reports.show', $trx) }}" class="btn btn-outline-primary" title="Lihat Detail Transaksi">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada data transaksi lunas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($transactions->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
