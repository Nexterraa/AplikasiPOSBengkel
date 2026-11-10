@extends('layouts.app')

@section('title', 'Detail Laporan Transaksi ' . $transaction->no_transaksi)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">{{ $transaction->no_transaksi }}</h4>
            <span class="badge bg-success px-3 py-1">
                <i class="bi bi-check-circle me-1"></i> LUNAS
            </span>
        </div>
        <p class="text-muted mb-0 small">
            Transaksi pada {{ $transaction->created_at->format('d F Y, H:i') }} WIB • Kasir Pembuat: <strong>{{ $transaction->user->name ?? 'Kasir' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Laporan
        </a>
        <a href="{{ route('transactions.invoice', $transaction) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Lihat Invoice Resmi
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Pelanggan, Kendaraan, Mekanik, dan Kasir -->
    <div class="col-lg-4">
        <!-- Data Pelanggan & Kendaraan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-bottom">
                <i class="bi bi-bicycle me-2 text-primary"></i> Data Pelanggan & Kendaraan
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Nomor Polisi</div>
                    <div class="fs-5 fw-bold font-monospace text-primary">
                        {{ $transaction->vehicle->plate_number ?? '-' }}
                    </div>
                    <div class="small text-secondary">
                        {{ $transaction->vehicle->brand ?? '' }} {{ $transaction->vehicle->model ?? '' }} ({{ $transaction->vehicle->year ?? '-' }})
                    </div>
                </div>
                <hr class="my-2">
                <div class="mb-2">
                    <div class="small text-muted">Nama Pelanggan</div>
                    <div class="fw-bold">{{ $transaction->customer->name ?? '-' }}</div>
                </div>
                <div class="mb-0">
                    <div class="small text-muted">Nomor Telepon</div>
                    <div>{{ $transaction->customer->phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Mekanik & Keluhan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-bottom">
                <i class="bi bi-person-workspace me-2 text-primary"></i> Mekanik & Keluhan
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Mekanik yang Menangani</div>
                    <div class="fw-bold">
                        @if ($transaction->mekanik)
                            {{ $transaction->mekanik->nama_mekanik }} ({{ $transaction->mekanik->telepon ?? '-' }})
                        @else
                            <span class="text-muted fst-italic">Tanpa Mekanik (Hanya Sparepart)</span>
                        @endif
                    </div>
                </div>
                <hr class="my-2">
                <div class="mb-0">
                    <div class="small text-muted">Keluhan Pelanggan</div>
                    <div class="p-2 bg-light rounded text-secondary small">
                        {{ $transaction->keluhan ?: 'Tidak ada catatan keluhan.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Pembayaran & Kasir -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-bold py-3 border-bottom">
                <i class="bi bi-credit-card me-2 text-primary"></i> Informasi Pembayaran
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Metode Pembayaran:</span>
                    <span class="fw-bold text-uppercase badge bg-secondary">{{ $transaction->payment->metode ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Total Tagihan:</span>
                    <span class="fw-bold">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Uang Diterima:</span>
                    <span>Rp {{ number_format($transaction->payment->jumlah_diterima ?? $transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Kembalian:</span>
                    <span class="fw-bold text-success">Rp {{ number_format($transaction->payment->kembalian ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Kasir Pemroses:</span>
                    <span class="fw-semibold">{{ $transaction->payment && $transaction->payment->user ? $transaction->payment->user->name : ($transaction->user->name ?? 'Kasir') }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Waktu Bayar:</span>
                    <span>{{ $transaction->payment && $transaction->payment->waktu_bayar ? $transaction->payment->waktu_bayar->format('d/m/Y H:i') . ' WIB' : '-' }}</span>
                </div>
                @if ($transaction->payment && $transaction->payment->catatan)
                    <hr class="my-2">
                    <div class="small text-muted">
                        <span class="fw-semibold">Catatan:</span> {{ $transaction->payment->catatan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Snapshot Jasa & Sparepart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-check me-2 text-primary"></i> Rincian Item Transaksi (Snapshot)</span>
                <span class="badge bg-secondary">{{ $transaction->details->count() }} Item</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Tipe</th>
                                <th>Nama Jasa / Sparepart</th>
                                <th>Jenis Harga</th>
                                <th class="text-end">Harga (Rp)</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end pe-3">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaction->details as $d)
                                <tr>
                                    <td class="ps-3">
                                        @if ($d->isJasa())
                                            <span class="badge bg-info text-dark"><i class="bi bi-tools me-1"></i> Jasa</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-box-seam me-1"></i> Part</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $d->nama_item }}
                                    </td>
                                    <td>
                                        @if ($d->isSparepart())
                                            <span class="badge bg-light text-dark border text-capitalize">
                                                {{ $d->jenis_harga ?: 'Satuan' }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end small">
                                        {{ number_format($d->harga, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center small">
                                        {{ $d->jumlah }}
                                    </td>
                                    <td class="text-end pe-3 fw-bold">
                                        {{ number_format($d->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Tidak ada item rincian transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">Status Transaksi:</span>
                        <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Transaksi Berhasil (Lunas)</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small mb-1">Total Transaksi:</div>
                        <div class="fs-3 fw-bold text-primary">
                            Rp {{ number_format($transaction->total, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
