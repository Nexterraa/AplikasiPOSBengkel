@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Data Pelanggan</h4>
        <small class="text-muted">Kelola data pelanggan bengkel dan kendaraan yang dimiliki</small>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pelanggan
    </a>
</div>

{{-- Filter & Pencarian --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama pelanggan atau nomor telepon..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-filter me-1"></i> Cari
                </button>
                @if ($search)
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Pelanggan --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th class="text-center">Kendaraan</th>
                        <th>Terdaftar</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ $customers->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="fw-bold text-decoration-none text-dark d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span>{{ $customer->name }}</span>
                                </a>
                            </td>
                            <td>
                                <i class="bi bi-telephone me-1 text-muted"></i>{{ $customer->phone }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $customer->vehicles_count > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' }} px-2 py-1">
                                    <i class="bi bi-bicycle me-1"></i> {{ $customer->vehicles_count }} Kendaraan
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $customer->created_at ? $customer->created_at->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-info me-1" title="Lihat Detail & Kendaraan">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Data">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pelanggan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                @if ($search)
                                    Tidak ditemukan pelanggan yang cocok dengan kata kunci "<strong>{{ $search }}</strong>".
                                @else
                                    Belum ada data pelanggan yang terdaftar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($customers->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection

