@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i> Riwayat Transaksi</h4>
        <p class="text-muted mb-0 small">Kelola dan pantau seluruh transaksi servis serta sparepart bengkel.</p>
    </div>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
        <i class="bi bi-cart-plus"></i> Transaksi Baru
    </a>
</div>

<!-- Filter dan Pencarian -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="No. Invoice, Plat, Pelanggan..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="belum_dibayar" {{ $status === 'belum_dibayar' ? 'selected' : '' }}>BELUM DIBAYAR</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>LUNAS</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if ($search || $status || $date)
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Invoice</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th>Mekanik</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('transactions.show', $trx) }}" class="text-decoration-none">
                                    {{ $trx->no_transaksi }}
                                </a>
                            </td>
                            <td class="small text-muted">
                                {{ $trx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $trx->customer->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $trx->customer->phone ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace">{{ $trx->vehicle->plate_number ?? '-' }}</span>
                                <div class="small text-muted">{{ $trx->vehicle->brand ?? '' }} {{ $trx->vehicle->model ?? '' }}</div>
                            </td>
                            <td>
                                {{ $trx->mekanik->nama_mekanik ?? '-' }}
                            </td>
                            <td class="text-end fw-bold">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($trx->isBelumDibayar())
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-clock-history me-1"></i> BELUM DIBAYAR
                                    </span>
                                @elseif ($trx->isLunas())
                                    <span class="badge bg-success px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> LUNAS
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1 text-uppercase">
                                        {{ $trx->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transactions.show', $trx) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($trx->isBelumDibayar())
                                        <a href="{{ route('transactions.edit', $trx) }}" class="btn btn-outline-warning" title="Edit Transaksi">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada transaksi yang ditemukan.
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

