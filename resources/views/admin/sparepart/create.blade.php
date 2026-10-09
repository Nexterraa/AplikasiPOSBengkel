@extends('layouts.app')

@section('title', 'Tambah Sparepart Baru')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h4 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Tambah Sparepart Baru</h4>
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

        <form action="{{ route('admin.spareparts.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="kode_sparepart" class="form-label fw-semibold">Kode Sparepart / SKU <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_sparepart" name="kode_sparepart" value="{{ old('kode_sparepart') }}" placeholder="Contoh: SPR-001" required>
                </div>

                <div class="col-md-5">
                    <label for="nama_sparepart" class="form-label fw-semibold">Nama Sparepart <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_sparepart" name="nama_sparepart" value="{{ old('nama_sparepart') }}" placeholder="Contoh: Oli Yamalube Matic 0.8L" required>
                </div>

                <div class="col-md-3">
                    <label for="merek" class="form-label fw-semibold">Merek</label>
                    <input type="text" class="form-control" id="merek" name="merek" value="{{ old('merek') }}" placeholder="Contoh: Yamalube, Honda, FDR">
                </div>

                <div class="col-md-4">
                    <label for="kategori_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" id="kategori_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="harga_beli" class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="harga_beli" name="harga_beli" value="{{ old('harga_beli', 0) }}" required>
                </div>

                <div class="col-md-4">
                    <label for="harga_jual" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', 0) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="stok" class="form-label fw-semibold">Stok Awal <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" value="{{ old('stok', 0) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="stok_minimum" class="form-label fw-semibold">Stok Minimum (Batas Peringatan) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', 5) }}" required>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Sparepart
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

