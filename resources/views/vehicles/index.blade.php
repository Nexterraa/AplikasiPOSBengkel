@extends('layouts.app')

@section('title', 'Data Kendaraan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Data Kendaraan</h4>
        <small class="text-muted">Kelola data motor pelanggan yang terdaftar untuk servis</small>
    </div>
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kendaraan
    </a>
</div>

{{-- Filter & Pencarian --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('vehicles.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5 col-lg-4">
                <label for="search" class="form-label small fw-semibold text-muted">Cari Kendaraan / Pemilik</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="search" class="form-control" placeholder="No. plat, merek, model, pemilik..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <label for="customer_id" class="form-label small fw-semibold text-muted">Filter Pemilik (Pelanggan)</label>
                <select name="customer_id" id="customer_id" class="form-select">
                    <option value="">-- Semua Pelanggan --</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                @if ($search || $customerId)
                    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Kendaraan --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>No. Polisi</th>
                        <th>Merek & Model</th>
                        <th>Tahun</th>
                        <th>Pemilik (Pelanggan)</th>
                        <th>Kontak Pemilik</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $index => $vehicle)
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ $vehicles->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="badge bg-dark fs-6 text-decoration-none px-2 py-1">
                                    {{ $vehicle->plate_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $vehicle->brand }}</div>
                                <small class="text-muted">{{ $vehicle->model }}</small>
                            </td>
                            <td>
                                {{ $vehicle->year ?: '-' }}
                            </td>
                            <td>
                                @if ($vehicle->customer)
                                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="text-decoration-none fw-semibold">
                                        <i class="bi bi-person me-1"></i>{{ $vehicle->customer->name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                @if ($vehicle->customer)
                                    <i class="bi bi-telephone me-1"></i>{{ $vehicle->customer->phone }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-info me-1" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Kendaraan">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kendaraan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Kendaraan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-bicycle fs-1 d-block mb-2 text-secondary"></i>
                                @if ($search || $customerId)
                                    Tidak ditemukan kendaraan yang cocok dengan filter pencarian.
                                @else
                                    Belum ada data kendaraan yang terdaftar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($vehicles->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>
@endsection

