@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->no_transaksi)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">{{ $transaction->no_transaksi }}</h4>
            @if ($transaction->isBelumDibayar())
                <span class="badge bg-warning text-dark px-3 py-1">
                    <i class="bi bi-clock-history me-1"></i> BELUM DIBAYAR
                </span>
            @elseif ($transaction->isLunas())
                <span class="badge bg-success px-3 py-1">
                    <i class="bi bi-check-circle me-1"></i> LUNAS
                </span>
            @elseif ($transaction->isDibatalkan())
                <span class="badge bg-danger px-3 py-1">
                    <i class="bi bi-x-circle me-1"></i> DIBATALKAN
                </span>
            @else
                <span class="badge bg-secondary px-3 py-1 text-uppercase">
                    {{ $transaction->status }}
                </span>
            @endif
        </div>
        <p class="text-muted mb-0 small">Dibuat pada {{ $transaction->created_at->format('d F Y, H:i') }} oleh <strong>{{ $transaction->user->name ?? 'Kasir' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
        @if ($transaction->isBelumDibayar())
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Transaksi
            </a>
            <a href="{{ route('transactions.payment', $transaction) }}" class="btn btn-success btn-sm">
                <i class="bi bi-credit-card me-1"></i> Proses Pembayaran
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalBatalTransaksi">
                <i class="bi bi-x-circle me-1"></i> Batalkan
            </button>
        @elseif ($transaction->isLunas())
            <a href="{{ route('transactions.invoice', $transaction) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Invoice
            </a>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Detail Pelanggan, Kendaraan, dan Mekanik -->
    <div class="col-lg-4">
        <!-- Card Pelanggan & Kendaraan -->
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
                    <div class="small text-muted">Pemilik Kendaraan</div>
                    <div class="fw-bold">{{ $transaction->customer->name ?? '-' }}</div>
                </div>
                <div class="mb-0">
                    <div class="small text-muted">Nomor Telepon</div>
                    <div>{{ $transaction->customer->phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Card Mekanik & Keluhan -->
        <div class="card border-0 shadow-sm rounded-3">
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
                    <div class="small text-muted">Keluhan / Catatan Masuk</div>
                    <div class="p-2 bg-light rounded text-secondary small">
                        {{ $transaction->keluhan ?: 'Tidak ada catatan keluhan.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Jasa & Sparepart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-check me-2 text-primary"></i> Rincian Item Transaksi</span>
                <span class="badge bg-secondary">{{ $transaction->details->count() }} Item</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Tipe</th>
                                <th>Nama Item</th>
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
                                    <td class="text-end">
                                        {{ number_format($d->harga, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
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
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        @if ($transaction->isBelumDibayar())
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
                                <div class="fw-bold text-dark small"><i class="bi bi-info-circle me-1 text-warning"></i> Status: Belum Dibayar</div>
                                <div class="text-muted small">Transaksi dapat diedit, dibayar, atau dibatalkan.</div>
                            </div>
                        @elseif ($transaction->isLunas())
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                                <div class="fw-bold text-success small"><i class="bi bi-check-circle me-1"></i> Status: Lunas</div>
                                <div class="text-muted small">Transaksi ini sudah lunas.</div>
                            </div>
                        @elseif ($transaction->isDibatalkan())
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-danger">
                                <div class="fw-bold text-danger small"><i class="bi bi-x-circle me-1"></i> Status: Dibatalkan</div>
                                <div class="text-muted small">
                                    Transaksi ini telah dibatalkan dan terkunci dari perubahan.
                                    @if ($transaction->alasan_batal)
                                        <br><strong>Alasan:</strong> {{ $transaction->alasan_batal }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted small mb-1">Total Pembayaran:</div>
                        <div class="fs-3 fw-bold text-primary">
                            Rp {{ number_format($transaction->total, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($transaction->isBelumDibayar())
    <!-- Modal Konfirmasi Pembatalan Transaksi — hanya untuk yang BELUM DIBAYAR -->
    <div class="modal fade" id="modalBatalTransaksi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i> Konfirmasi Pembatalan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <p class="mb-2">Apakah Anda yakin ingin membatalkan transaksi <strong>{{ $transaction->no_transaksi }}</strong>?</p>
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Transaksi yang dibatalkan <strong>tidak dapat diubah, dibayar, atau dicetak</strong> invoicenya. Data transaksi tetap tersimpan sebagai riwayat.
                        </div>
                        <div class="mb-3">
                            <label for="alasan_batal" class="form-label fw-semibold">Alasan Pembatalan <span class="text-muted fw-normal">(Opsional)</span></label>
                            <textarea name="alasan_batal" id="alasan_batal" class="form-control" rows="2" placeholder="Contoh: Pelanggan membatalkan / salah input"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger fw-semibold">
                            <i class="bi bi-x-circle me-1"></i> Ya, Batalkan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

