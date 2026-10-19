@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Pelanggan Baru</h4>
        <small class="text-muted">Daftarkan pelanggan baru ke dalam sistem</small>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-card-checklist me-1 text-primary"></i> Formulir Data Pelanggan
            </div>
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Nama Pelanggan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Budi Santoso"
                            autofocus required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label fw-semibold">
                            Nomor Telepon / WhatsApp <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="phone" id="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}"
                            placeholder="Contoh: 081234567890"
                            required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nomor telepon akan digunakan untuk riwayat transaksi dan identifikasi pelanggan.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Pelanggan
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
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
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1 text-primary"></i> Informasi & Petunjuk</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Nama pelanggan dan nomor telepon wajib diisi.</li>
                    <li class="mb-2">Setelah pelanggan tersimpan, Anda dapat mendaftarkan satu atau lebih kendaraan milik pelanggan tersebut.</li>
                    <li class="mb-2">Data pelanggan ini dapat langsung dipilih saat proses transaksi kasir di bengkel.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

