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

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Terjadi Kesalahan Input:</h6>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
                <button type="button" class="btn btn-sm btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKendaraan">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Kendaraan
                </button>
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
                                        <span class="badge bg-dark fs-6 px-2 py-1">
                                            {{ $vehicle->plate_number }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $vehicle->brand }}</div>
                                        <small class="text-muted">{{ $vehicle->model }}</small>
                                    </td>
                                    <td>
                                        {{ $vehicle->year ?: '-' }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditKendaraan{{ $vehicle->id }}" title="Edit Kendaraan">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kendaraan dengan plat {{ $vehicle->plate_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="redirect_to" value="customer">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Kendaraan">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Edit Kendaraan --}}
                                <div class="modal fade" id="modalEditKendaraan{{ $vehicle->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                                <input type="hidden" name="redirect_to" value="customer">

                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Kendaraan - {{ $vehicle->plate_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nomor Polisi / Plat <span class="text-danger">*</span></label>
                                                        <input type="text" name="plate_number" class="form-control text-uppercase" value="{{ old('plate_number', $vehicle->plate_number) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Merek Kendaraan <span class="text-danger">*</span></label>
                                                        <input type="text" name="brand" class="form-control" value="{{ old('brand', $vehicle->brand) }}" placeholder="Contoh: Honda, Yamaha" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Model / Tipe Kendaraan <span class="text-danger">*</span></label>
                                                        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}" placeholder="Contoh: Vario 150, Beat" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Tahun Pembuatan</label>
                                                        <input type="number" name="year" class="form-control" value="{{ old('year', $vehicle->year) }}" placeholder="Contoh: 2021">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">
                                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-bicycle fs-2 d-block mb-2 text-secondary"></i>
                                        Pelanggan ini belum memiliki kendaraan terdaftar.<br>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKendaraan">
                                            <i class="bi bi-plus-lg me-1"></i> Daftarkan Kendaraan Sekarang
                                        </button>
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

{{-- Modal Tambah Kendaraan --}}
<div class="modal fade" id="modalTambahKendaraan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="redirect_to" value="customer">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kendaraan untuk {{ $customer->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label for="plate_number" class="form-label fw-semibold">Nomor Polisi / Plat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="plate_number" name="plate_number" placeholder="Contoh: B 1234 XYZ" value="{{ old('plate_number') }}" required>
                        <div class="form-text text-muted small">Huruf akan otomatis dikonversi menjadi huruf kapital.</div>
                    </div>
                    <div class="mb-3">
                        <label for="brand" class="form-label fw-semibold">Merek Kendaraan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="brand" name="brand" placeholder="Contoh: Honda, Yamaha, Suzuki" value="{{ old('brand') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="model" class="form-label fw-semibold">Model / Tipe Kendaraan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="model" name="model" placeholder="Contoh: Vario 160, Beat, NMAX" value="{{ old('model') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="year" class="form-label fw-semibold">Tahun Pembuatan (Opsional)</label>
                        <input type="number" class="form-control" id="year" name="year" placeholder="Contoh: 2022" value="{{ old('year') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Kendaraan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
