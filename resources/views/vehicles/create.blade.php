@extends('layouts.app')

@section('title', 'Tambah Kendaraan Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Kendaraan Baru</h4>
        <small class="text-muted">Daftarkan sepeda motor pelanggan ke sistem bengkel</small>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bicycle me-1 text-primary"></i> Formulir Data Kendaraan
            </div>
            <div class="card-body">
                <form action="{{ route('vehicles.store') }}" method="POST">
                    @csrf

                    {{-- Pemilik / Customer --}}
                    <div class="mb-3">
                        <label for="customer_id" class="form-label fw-semibold">
                            Pemilik Kendaraan (Pelanggan) <span class="text-danger">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $customerId) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — {{ $c->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Pelanggan belum terdaftar? <a href="{{ route('customers.create') }}" class="text-primary text-decoration-none fw-semibold">Tambah pelanggan baru di sini</a>.
                        </div>
                    </div>

                    {{-- Nomor Polisi --}}
                    <div class="mb-3">
                        <label for="plate_number" class="form-label fw-semibold">
                            Nomor Polisi (Plat Nomor) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="plate_number" id="plate_number"
                            class="form-control text-uppercase @error('plate_number') is-invalid @enderror"
                            value="{{ old('plate_number') }}"
                            placeholder="Contoh: B 1234 ABC"
                            required>
                        @error('plate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nomor polisi harus unik dan belum terdaftar pada kendaraan lain.</div>
                    </div>

                    <div class="row">
                        {{-- Merek --}}
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label fw-semibold">
                                Merek <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="brand" id="brand"
                                class="form-control @error('brand') is-invalid @enderror"
                                value="{{ old('brand') }}"
                                placeholder="Contoh: Honda, Yamaha, Suzuki"
                                required>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Model --}}
                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label fw-semibold">
                                Model / Tipe Motor <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="model" id="model"
                                class="form-control @error('model') is-invalid @enderror"
                                value="{{ old('model') }}"
                                placeholder="Contoh: Vario 160, Beat FI, NMAX"
                                required>
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Tahun --}}
                    <div class="mb-4">
                        <label for="year" class="form-label fw-semibold">
                            Tahun Pembuatan <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <input type="number" name="year" id="year"
                            class="form-control @error('year') is-invalid @enderror"
                            value="{{ old('year') }}"
                            min="1900" max="{{ date('Y') + 1 }}"
                            placeholder="Contoh: {{ date('Y') }}">
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Kendaraan
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1 text-primary"></i> Petunjuk Pengisian</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Satu pelanggan dapat memiliki lebih dari satu sepeda motor.</li>
                    <li class="mb-2">Nomor polisi akan otomatis disimpan dalam huruf besar (kapital).</li>
                    <li class="mb-2">Pastikan nomor polisi sesuai dengan plat fisik kendaraan untuk memudahkan pencarian di kasir saat transaksi servis.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

