@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Stok Masuk</h4>
        <small class="text-muted">Tambah stok sparepart ke inventaris</small>
    </div>
    <a href="{{ route('admin.stok.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clock-history me-1"></i> Riwayat Stok
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-plus-circle me-1 text-success"></i> Form Stok Masuk
            </div>
            <div class="card-body">
                <form action="{{ route('admin.stok.store') }}" method="POST">
                    @csrf

                    {{-- Pilih Sparepart --}}
                    <div class="mb-3">
                        <label for="sparepart_id" class="form-label fw-semibold">
                            Sparepart <span class="text-danger">*</span>
                        </label>
                        <select name="sparepart_id" id="sparepart_id"
                            class="form-select @error('sparepart_id') is-invalid @enderror"
                            onchange="updateStokInfo(this)">
                            <option value="">-- Pilih Sparepart --</option>
                            @foreach ($spareparts as $sp)
                                <option value="{{ $sp->id }}"
                                    data-stok="{{ $sp->stok }}"
                                    data-stok-min="{{ $sp->stok_minimum }}"
                                    {{ old('sparepart_id') == $sp->id ? 'selected' : '' }}>
                                    {{ $sp->nama_sparepart }}
                                    @if ($sp->kode_sparepart) ({{ $sp->kode_sparepart }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('sparepart_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Info Stok Saat Ini --}}
                    <div id="stok-info" class="alert alert-light border mb-3 d-none">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="small text-muted">Stok Saat Ini</div>
                                <div class="fw-bold fs-5" id="info-stok-sekarang">-</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Stok Minimum</div>
                                <div class="fw-bold fs-5" id="info-stok-min">-</div>
                            </div>
                        </div>
                    </div>

                    {{-- Jumlah Stok Masuk --}}
                    <div class="mb-3">
                        <label for="jumlah" class="form-label fw-semibold">
                            Jumlah Stok Masuk <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="jumlah" id="jumlah"
                            class="form-control @error('jumlah') is-invalid @enderror"
                            value="{{ old('jumlah') }}"
                            min="1"
                            placeholder="Masukkan jumlah stok masuk..."
                            oninput="updatePreview()">
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Preview Stok Sesudah --}}
                    <div id="stok-preview" class="alert alert-success border-0 mb-3 d-none">
                        <i class="bi bi-arrow-up-circle me-1"></i>
                        Stok setelah masuk: <strong id="preview-sesudah">-</strong>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" id="catatan"
                            class="form-control @error('catatan') is-invalid @enderror"
                            rows="3"
                            maxlength="500"
                            placeholder="Catatan stok masuk (opsional)...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Simpan Stok Masuk
                        </button>
                        <a href="{{ route('admin.stok.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panduan --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1 text-primary"></i> Panduan Stok Masuk</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Pilih sparepart yang stoknya akan ditambah.</li>
                    <li class="mb-2">Hanya sparepart dengan status <strong>Aktif</strong> yang bisa dipilih.</li>
                    <li class="mb-2">Masukkan jumlah stok masuk dalam bilangan bulat positif.</li>
                    <li class="mb-2">Stok akan langsung bertambah ke stok sebelumnya.</li>
                    <li>Setiap penambahan stok dicatat otomatis di riwayat stok.</li>
                </ul>
                <hr>
                <div class="small">
                    <span class="badge bg-success me-1">Aman</span> Stok &gt; stok minimum<br>
                    <span class="badge bg-warning text-dark me-1 mt-1">Menipis</span> Stok &le; stok minimum (tapi &gt; 0)<br>
                    <span class="badge bg-danger me-1 mt-1">Habis</span> Stok = 0
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const select = document.getElementById('sparepart_id');
    const jumlahInput = document.getElementById('jumlah');
    const stokInfo = document.getElementById('stok-info');
    const stokPreview = document.getElementById('stok-preview');

    function updateStokInfo(el) {
        const opt = el.options[el.selectedIndex];
        if (!opt.value) {
            stokInfo.classList.add('d-none');
            stokPreview.classList.add('d-none');
            return;
        }
        document.getElementById('info-stok-sekarang').textContent = opt.dataset.stok;
        document.getElementById('info-stok-min').textContent = opt.dataset.stokMin;
        stokInfo.classList.remove('d-none');
        updatePreview();
    }

    function updatePreview() {
        const opt = select.options[select.selectedIndex];
        const jumlah = parseInt(jumlahInput.value);
        if (!opt || !opt.value || isNaN(jumlah) || jumlah < 1) {
            stokPreview.classList.add('d-none');
            return;
        }
        const sesudah = parseInt(opt.dataset.stok) + jumlah;
        document.getElementById('preview-sesudah').textContent = sesudah;
        stokPreview.classList.remove('d-none');
    }

    // Restore state jika ada old input (validasi gagal)
    if (select.value) updateStokInfo(select);
</script>
@endpush
@endsection

