@extends('layouts.app')

@section('title', 'Detail Kendaraan: ' . $vehicle->plate_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Detail Kendaraan</h4>
        <small class="text-muted">Informasi spesifikasi kendaraan dan data pemilik</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Kendaraan
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Card Informasi Kendaraan --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Spesifikasi Kendaraan</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="badge bg-dark fs-3 px-4 py-2 rounded-3 shadow-sm letter-spacing-1">
                        {{ $vehicle->plate_number }}
                    </span>
                    <h5 class="fw-bold mt-3 mb-0">{{ $vehicle->brand }} {{ $vehicle->model }}</h5>
                    <p class="text-muted small">Sepeda Motor Terdaftar</p>
                </div>

                <ul class="list-group list-group-flush border-top border-bottom">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted"><i class="bi bi-tag me-2"></i>Merek</span>
                        <span class="fw-bold">{{ $vehicle->brand }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted"><i class="bi bi-gear me-2"></i>Model / Tipe</span>
                        <span class="fw-bold">{{ $vehicle->model }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Tahun Pembuatan</span>
                        <span class="fw-bold">{{ $vehicle->year ?: '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                        <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Tanggal Pendaftaran</span>
                        <span class="fw-bold">{{ $vehicle->created_at ? $vehicle->created_at->translatedFormat('d F Y, H:i') : '-' }} WIB</span>
                    </li>
                </ul>

                <div class="mt-4">
                    <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kendaraan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-1"></i> Hapus Kendaraan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Pemilik (Pelanggan) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Informasi Pemilik</h5>
                @if ($vehicle->customer)
                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Profil Pemilik
                    </a>
                @endif
            </div>
            <div class="card-body p-4">
                @if ($vehicle->customer)
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person fs-2"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $vehicle->customer->name }}</h5>
                            <p class="text-muted mb-0"><i class="bi bi-telephone me-1"></i>{{ $vehicle->customer->phone }}</p>
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-3 mb-0">
                        <h6 class="fw-bold"><i class="bi bi-card-checklist me-1 text-primary"></i> Status Kendaraan Pelanggan</h6>
                        <p class="small text-muted mb-2">
                            Pelanggan ini memiliki total <strong>{{ $vehicle->customer->vehicles()->count() }}</strong> kendaraan terdaftar di bengkel.
                        </p>
                        <a href="{{ route('customers.show', $vehicle->customer) }}" class="small text-decoration-none fw-semibold">
                            Lihat semua kendaraan milik {{ $vehicle->customer->name }} &rarr;
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                        Data pemilik tidak ditemukan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

