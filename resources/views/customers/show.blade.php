@extends('layouts.app')

@section('title', 'Detail Pelanggan: ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Detail Pelanggan</h4>
        <small class="text-muted">Informasi data pelanggan dan daftar kendaraan yang terdaftar</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Pelanggan
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Card Profil Pelanggan --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center p-4">
                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person fs-1"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
                <p class="text-muted mb-3"><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</p>

                <div class="d-flex justify-content-around border-top border-bottom py-2 my-3 text-start">
                    <div>
                        <small class="text-muted d-block">Jumlah Kendaraan</small>
                        <span class="fw-bold fs-6">{{ $customer->vehicles->count() }} Unit</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Terdaftar Sejak</small>
                        <span class="fw-bold fs-6">{{ $customer->created_at ? $customer->created_at->translatedFormat('d M Y') : '-' }}</span>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-1"></i> Hapus Pelanggan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Kendaraan Pelanggan --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bicycle me-2 text-primary"></i>Kendaraan Terdaftar
                </h5>
                <a href="{{ route('vehicles.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Kendaraan
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th>No. Polisi</th>
                                <th>Merek & Model</th>
                                <th>Tahun</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->vehicles as $index => $vehicle)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
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
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-bicycle fs-2 d-block mb-2 text-secondary"></i>
                                        Pelanggan ini belum memiliki kendaraan terdaftar.<br>
                                        <a href="{{ route('vehicles.create', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> Daftarkan Kendaraan Sekarang
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

