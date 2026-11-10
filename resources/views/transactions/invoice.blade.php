@extends('layouts.app')

@section('title', 'Invoice ' . $transaction->no_transaksi)

@section('content')
<!-- Navigasi Aksi Layar (Disembunyikan saat mencetak) -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-printer me-2 text-primary"></i> Invoice Resmi Bengkel</h4>
        <p class="text-muted mb-0 small">Invoice transaksi lunas <strong>{{ $transaction->no_transaksi }}</strong> siap dicetak atau disimpan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Detail Transaksi
        </a>
        <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-cart-plus me-1"></i> Transaksi Baru
        </a>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Cetak Invoice
        </button>
    </div>
</div>

<!-- Container Invoice Cetak -->
<div class="card border-0 shadow-sm rounded-3 invoice-container bg-white mx-auto p-4 p-md-5" style="max-width: 850px;">
    <!-- Header Invoice -->
    <div class="row align-items-center border-bottom pb-4 mb-4">
        <div class="col-sm-7 mb-3 mb-sm-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-wrench-adjustable text-warning fs-2"></i>
                <h3 class="fw-bold text-dark mb-0 tracking-tight">POS BENGKEL MOTOR</h3>
            </div>
            <p class="text-muted small mb-0">Spesialis Servis Ringan, Tune Up, Ganti Oli & Sparepart Motor</p>
            <p class="text-secondary small mb-0">Jl. Raya Bengkel No. 123 • Telp/WA: 0812-3456-7890</p>
        </div>
        <div class="col-sm-5 text-sm-end">
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 fw-bold text-uppercase mb-2 d-inline-block">
                <i class="bi bi-check-circle-fill me-1"></i> LUNAS
            </span>
            <div class="small text-muted">Nomor Invoice:</div>
            <div class="fs-5 fw-bold font-monospace text-primary">{{ $transaction->no_transaksi }}</div>
            <div class="small text-secondary">{{ $transaction->created_at->format('d/m/Y H:i') }} WIB</div>
        </div>
    </div>

    <!-- Informasi Pelanggan, Kendaraan, dan Kasir -->
    <div class="row g-3 mb-4 text-small">
        <div class="col-sm-4">
            <div class="p-3 bg-light rounded-3 h-100 border">
                <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-person me-1"></i> Pelanggan</h6>
                <div class="fw-bold">{{ $transaction->customer->name ?? '-' }}</div>
                <div class="small text-secondary">{{ $transaction->customer->phone ?? '-' }}</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="p-3 bg-light rounded-3 h-100 border">
                <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-bicycle me-1"></i> Kendaraan</h6>
                <div class="fw-bold font-monospace text-primary fs-6">{{ $transaction->vehicle->plate_number ?? '-' }}</div>
                <div class="small text-secondary">{{ $transaction->vehicle->brand ?? '' }} {{ $transaction->vehicle->model ?? '' }} ({{ $transaction->vehicle->year ?? '-' }})</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="p-3 bg-light rounded-3 h-100 border">
                <h6 class="fw-bold text-uppercase text-muted small mb-2"><i class="bi bi-info-circle me-1"></i> Pelaksana</h6>
                <div class="small"><span class="text-muted">Kasir:</span> <strong>{{ $transaction->payment && $transaction->payment->user ? $transaction->payment->user->name : ($transaction->user->name ?? 'Kasir') }}</strong></div>
                <div class="small"><span class="text-muted">Mekanik:</span> <strong>{{ $transaction->mekanik ? $transaction->mekanik->nama_mekanik : 'Tanpa Mekanik' }}</strong></div>
            </div>
        </div>
    </div>

    @if ($transaction->keluhan)
        <div class="mb-4 p-2 bg-light rounded border small">
            <span class="text-muted fw-semibold">Keluhan / Catatan:</span> {{ $transaction->keluhan }}
        </div>
    @endif

    <!-- Tabel Rincian Jasa & Sparepart -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 45%;">Rincian Jasa / Sparepart</th>
                    <th class="text-center" style="width: 15%;">Jenis</th>
                    <th class="text-end" style="width: 15%;">Tarif / Harga</th>
                    <th class="text-center" style="width: 8%;">Qty</th>
                    <th class="text-end pe-3" style="width: 17%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->details as $idx => $d)
                    <tr>
                        <td class="text-center small">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $d->nama_item }}</div>
                        </td>
                        <td class="text-center small">
                            @if ($d->isJasa())
                                <span class="badge bg-light text-dark border">Jasa</span>
                            @else
                                <span class="badge bg-light text-dark border text-capitalize">Part ({{ $d->jenis_harga ?: 'Satuan' }})</span>
                            @endif
                        </td>
                        <td class="text-end small">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                        <td class="text-center small">{{ $d->jumlah }}</td>
                        <td class="text-end pe-3 fw-bold small">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Ringkasan Total & Pembayaran -->
    <div class="row justify-content-end mb-4">
        <div class="col-sm-6">
            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Total Tagihan:</span>
                    <span class="fw-bold fs-6 text-dark">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Metode Pembayaran:</span>
                    <span class="fw-bold text-uppercase badge bg-secondary">{{ $transaction->payment->metode ?? 'CASH' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Jumlah Diterima:</span>
                    <span>Rp {{ number_format($transaction->payment->jumlah_diterima ?? $transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Kembalian:</span>
                    <span class="fw-bold text-success">Rp {{ number_format($transaction->payment->kembalian ?? 0, 0, ',', '.') }}</span>
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

    <!-- Catatan & Tanda Tangan -->
    <div class="row align-items-end mt-4 pt-3 border-top small text-muted">
        <div class="col-8">
            <p class="mb-1 fw-bold text-dark">Catatan Pelanggan:</p>
            <ul class="mb-0 ps-3 small text-secondary">
                <li>Terima kasih atas kepercayaan Anda menyervis di POS Bengkel Motor.</li>
                <li>Garansi servis berlaku 7 (tujuh) hari kerja setelah tanggal invoice.</li>
                <li>Barang/sparepart yang sudah dibeli tidak dapat ditukar atau dikembalikan kecuali cacat pabrik.</li>
            </ul>
        </div>
        <div class="col-4 text-center">
            <div class="small text-muted mb-4">Hormat Kami,</div>
            <div class="fw-bold text-dark pt-3 border-top d-inline-block px-3">
                {{ $transaction->payment && $transaction->payment->user ? $transaction->payment->user->name : ($transaction->user->name ?? 'Kasir') }}
            </div>
            <div class="small text-muted">Kasir Bengkel</div>
        </div>
    </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    .navbar, .sidebar, footer, .d-print-none, .btn {
        display: none !important;
    }
    .container-fluid, .col-md-9, .col-lg-10 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .invoice-container {
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    .badge {
        border: 1px solid #333 !important;
        color: #000 !important;
        background-color: transparent !important;
    }
}
</style>
@endsection

