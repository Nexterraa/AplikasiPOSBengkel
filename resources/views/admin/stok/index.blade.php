@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pergerakan Stok</h4>
        <small class="text-muted">Catatan pergerakan stok sparepart (stok masuk, keluar, dan koreksi)</small>
    </div>
    <a href="{{ route('admin.stok.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-box-arrow-in-down me-1"></i> Tambah Stok Masuk
    </a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.stok.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="sparepart_id" class="form-label small fw-semibold text-muted">Filter Sparepart</label>
                <select name="sparepart_id" id="sparepart_id" class="form-select">
                    <option value="">-- Semua Sparepart --</option>
                    @foreach ($spareparts as $sp)
                        <option value="{{ $sp->id }}" {{ request('sparepart_id') == $sp->id ? 'selected' : '' }}>
                            {{ $sp->nama_sparepart }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                @if (request()->filled('sparepart_id'))
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Riwayat --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Waktu</th>
                        <th>Sparepart</th>
                        <th>Jenis</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Stok Sebelum &rarr; Sesudah</th>
                        <th>Oleh (Admin)</th>
                        <th class="pe-3">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $index => $item)
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ $movements->firstItem() + $index }}
                            </td>
                            <td class="small">
                                <div>{{ $item->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-muted small">{{ $item->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->sparepart->nama_sparepart ?? '-' }}</div>
                                <div class="small text-muted">
                                    Kode: {{ $item->sparepart->kode_sparepart ?? '-' }}
                                    @if ($item->sparepart)
                                        | Status:
                                        @if ($item->sparepart->status_stok === 'Aman')
                                            <span class="badge bg-success">Aman</span>
                                        @elseif ($item->sparepart->status_stok === 'Menipis')
                                            <span class="badge bg-warning text-dark">Menipis</span>
                                        @else
                                            <span class="badge bg-danger">Habis</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($item->jenis === 'masuk')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-arrow-down-left me-1"></i> Stok Masuk
                                    </span>
                                @elseif ($item->jenis === 'keluar')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-arrow-up-right me-1"></i> Stok Keluar
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        <i class="bi bi-sliders me-1"></i> Koreksi
                                    </span>
                                @endif
                            </td>
                            <td class="text-center fw-bold fs-6 {{ $item->jenis === 'masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $item->jenis === 'masuk' ? '+' : '-' }}{{ number_format($item->jumlah) }}
                            </td>
                            <td class="text-center small">
                                <span class="text-muted">{{ number_format($item->stok_sebelum) }}</span>
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                <span class="fw-bold">{{ number_format($item->stok_sesudah) }}</span>
                            </td>
                            <td class="small">
                                <i class="bi bi-person me-1 text-muted"></i>{{ $item->user->name ?? 'Sistem' }}
                            </td>
                            <td class="pe-3 small text-muted">
                                {{ $item->catatan ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada riwayat pergerakan stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($movements->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection

