@extends('layouts.app')

@section('title', 'Data Sparepart & Produk')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>Data Sparepart & Produk</h3>
        <p class="text-muted mb-0">Kelola informasi produk, sparepart, dan batasan stok minimum</p>
    </div>
    <a href="{{ route('admin.spareparts.create') }}" class="btn btn-primary fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Tambah Sparepart
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <form action="{{ route('admin.spareparts.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, atau merek..." value="{{ $search }}">
            </div>
            <div class="col-md-3">
                <select name="kategori_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary me-1" type="submit"><i class="bi bi-search me-1"></i> Filter</button>
                @if($search || $kategoriId)
                    <a href="{{ route('admin.spareparts.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Kode</th>
                        <th scope="col">Nama Sparepart</th>
                        <th scope="col">Kategori & Merek</th>
                        <th scope="col">Harga Beli</th>
                        <th scope="col">Harga Jual</th>
                        <th scope="col">Stok</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($spareparts as $sp)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $sp->kode_sparepart }}</td>
                            <td class="fw-semibold">{{ $sp->nama_sparepart }}</td>
                            <td>
                                <span class="badge bg-light text-dark border me-1">{{ $sp->kategori->nama_kategori ?? '-' }}</span>
                                <small class="text-muted">{{ $sp->merek ? "($sp->merek)" : '' }}</small>
                            </td>
                            <td>Rp {{ number_format($sp->harga_beli, 0, ',', '.') }}</td>
                            <td class="fw-bold text-success">Rp {{ number_format($sp->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                @if($sp->stok <= 0)
                                    <span class="badge bg-danger">Habis ({{ $sp->stok }})</span>
                                @elseif($sp->stok <= $sp->stok_minimum)
                                    <span class="badge bg-warning text-dark">Menipis ({{ $sp->stok }})</span>
                                @else
                                    <span class="badge bg-success">Aman ({{ $sp->stok }})</span>
                                @endif
                                <small class="text-muted d-block fs-xs">Min: {{ $sp->stok_minimum }}</small>
                            </td>
                            <td>
                                @if($sp->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.spareparts.edit', $sp->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                @if($sp->is_active)
                                    <form action="{{ route('admin.spareparts.destroy', $sp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan sparepart ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye-slash me-1"></i> Nonaktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data sparepart.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($spareparts->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $spareparts->links() }}
        </div>
    @endif
</div>
@endsection

