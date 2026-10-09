@extends('layouts.app')

@section('title', 'Edit Sparepart')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Sparepart</h4>
        <a href="{{ route('admin.spareparts.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.spareparts.update', $sparepart->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="kode_sparepart" class="form-label fw-semibold">Kode Sparepart / SKU <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_sparepart" name="kode_sparepart" value="{{ old('kode_sparepart', $sparepart->kode_sparepart) }}" required>
                </div>

                <div class="col-md-5">
                    <label for="nama_sparepart" class="form-label fw-semibold">Nama Sparepart <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_sparepart" name="nama_sparepart" value="{{ old('nama_sparepart', $sparepart->nama_sparepart) }}" required>
                </div>

                <div class="col-md-3">
                    <label for="merek" class="form-label fw-semibold">Merek</label>
                    <input type="text" class="form-control" id="merek" name="merek" value="{{ old('merek', $sparepart->merek) }}">
                </div>

                <div class="col-md-4">
                    <label for="kategori_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" id="kategori_id" class="form-select" required>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id', $sparepart->kategori_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="harga_beli" class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="harga_beli" name="harga_beli" value="{{ old('harga_beli', $sparepart->harga_beli) }}" required>
                </div>

                <div class="col-md-4">
                    <label for="harga_jual" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', $sparepart->harga_jual) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok saat ini</label>
                    <input type="text" class="form-control bg-light" value="{{ $sparepart->stok }} (Tercatat)" readonly disabled>
                    <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i> Perubahan stok mengikuti mekanisme manajemen stok (Tahap 5).</div>
                </div>

                <div class="col-md-4">
                    <label for="stok_minimum" class="form-label fw-semibold">Stok Minimum <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', $sparepart->stok_minimum) }}" required>
                </div>

                <div class="col-md-4">
                    <label for="is_active" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="is_active" id="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $sparepart->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $sparepart->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

