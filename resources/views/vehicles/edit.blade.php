@extends('layouts.app')

@section('title', 'Edit Kendaraan: ' . $vehicle->plate_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Data Kendaraan</h4>
        <small class="text-muted">Perbarui data kendaraan plat {{ $vehicle->plate_number }}</small>
    </div>
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bicycle me-1 text-primary"></i> Formulir Edit Kendaraan
            </div>
            <div class="card-body">
                <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Pemilik / Customer --}}
                    <div class="mb-3">
                        <label for="customer_id" class="form-label fw-semibold">
                            Pemilik Kendaraan (Pelanggan) <span class="text-danger">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $vehicle->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — {{ $c->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nomor Polisi --}}
                    <div class="mb-3">
                        <label for="plate_number" class="form-label fw-semibold">
                            Nomor Polisi (Plat Nomor) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="plate_number" id="plate_number"
                            class="form-control text-uppercase @error('plate_number') is-invalid @enderror"
                            value="{{ old('plate_number', $vehicle->plate_number) }}"
                            placeholder="Contoh: B 1234 ABC"
                            required>
                        @error('plate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Merek --}}
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label fw-semibold">
                                Merek <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="brand" id="brand"
                                class="form-control @error('brand') is-invalid @enderror"
                                value="{{ old('brand', $vehicle->brand) }}"
                                placeholder="Contoh: Honda, Yamaha"
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
                                value="{{ old('model', $vehicle->model) }}"
                                placeholder="Contoh: Vario 160, Beat"
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
                            value="{{ old('year', $vehicle->year) }}"
                            min="1900" max="{{ date('Y') + 1 }}"
                            placeholder="Contoh: 2021">
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Perbarui Kendaraan
                        </button>
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

