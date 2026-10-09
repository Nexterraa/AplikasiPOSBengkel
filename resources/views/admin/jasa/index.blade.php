@extends('layouts.app')

@section('title', 'Jasa Servis')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-tools text-primary me-2"></i>Jasa Servis</h3>
        <p class="text-muted mb-0">Kelola daftar layanan servis motor dan tarif biaya</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahJasa">
        <i class="bi bi-plus-lg me-1"></i> Tambah Jasa Servis
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <form action="{{ route('admin.jasa.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama jasa..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if($search)
                        <a href="{{ route('admin.jasa.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3" style="width: 60px;">#</th>
                        <th scope="col">Kode Jasa</th>
                        <th scope="col">Nama Jasa Servis</th>
                        <th scope="col">Tarif Harga</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jasaServis as $index => $jasa)
                        <tr>
                            <td class="ps-3 text-muted">{{ $jasaServis->firstItem() + $index }}</td>
                            <td class="fw-bold text-primary">{{ $jasa->kode_jasa }}</td>
                            <td class="fw-semibold">{{ $jasa->nama_jasa }}</td>
                            <td class="fw-bold text-success">Rp {{ number_format($jasa->harga, 0, ',', '.') }}</td>
                            <td>
                                @if($jasa->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditJasa{{ $jasa->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                @if($jasa->is_active)
                                    <form action="{{ route('admin.jasa.destroy', $jasa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan jasa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye-slash"></i> Nonaktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit Jasa -->
                        <div class="modal fade" id="modalEditJasa{{ $jasa->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.jasa.update', $jasa->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Jasa Servis</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kode Jasa</label>
                                                <input type="text" name="kode_jasa" class="form-control" value="{{ old('kode_jasa', $jasa->kode_jasa) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Jasa Servis</label>
                                                <input type="text" name="nama_jasa" class="form-control" value="{{ old('nama_jasa', $jasa->nama_jasa) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Tarif Harga (Rp)</label>
                                                <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga', $jasa->harga) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Status</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1" {{ $jasa->is_active ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$jasa->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data jasa servis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($jasaServis->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $jasaServis->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Jasa -->
<div class="modal fade" id="modalTambahJasa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.jasa.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Jasa Servis Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_jasa" class="form-label fw-semibold">Kode Jasa</label>
                        <input type="text" class="form-control" id="kode_jasa" name="kode_jasa" placeholder="Contoh: SRV-01" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_jasa" class="form-label fw-semibold">Nama Jasa Servis</label>
                        <input type="text" class="form-control" id="nama_jasa" name="nama_jasa" placeholder="Contoh: Servis Ringan / Ganti Oli" required>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label fw-semibold">Tarif Harga (Rp)</label>
                        <input type="number" step="0.01" class="form-control" id="harga" name="harga" placeholder="Contoh: 35000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jasa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

