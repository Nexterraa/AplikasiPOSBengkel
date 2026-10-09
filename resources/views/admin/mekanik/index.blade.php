@extends('layouts.app')

@section('title', 'Data Mekanik')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-person-workspace text-primary me-2"></i>Data Mekanik</h3>
        <p class="text-muted mb-0">Kelola data teknisi & mekanik bengkel (Data Master)</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahMekanik">
        <i class="bi bi-plus-lg me-1"></i> Tambah Mekanik
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <form action="{{ route('admin.mekanik.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau telepon..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if($search)
                        <a href="{{ route('admin.mekanik.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Reset</a>
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
                        <th scope="col">Nama Mekanik</th>
                        <th scope="col">No. Telepon / HP</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mekaniks as $index => $mek)
                        <tr>
                            <td class="ps-3 text-muted">{{ $mekaniks->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $mek->nama_mekanik }}</td>
                            <td>{{ $mek->telepon ?? '-' }}</td>
                            <td>
                                @if($mek->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditMekanik{{ $mek->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                @if($mek->is_active)
                                    <form action="{{ route('admin.mekanik.destroy', $mek->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan mekanik ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye-slash"></i> Nonaktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit Mekanik -->
                        <div class="modal fade" id="modalEditMekanik{{ $mek->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.mekanik.update', $mek->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Data Mekanik</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Mekanik</label>
                                                <input type="text" name="nama_mekanik" class="form-control" value="{{ old('nama_mekanik', $mek->nama_mekanik) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">No. Telepon / HP</label>
                                                <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $mek->telepon) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Status Mekanik</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1" {{ $mek->is_active ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$mek->is_active ? 'selected' : '' }}>Nonaktif</option>
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
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data mekanik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($mekaniks->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $mekaniks->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Mekanik -->
<div class="modal fade" id="modalTambahMekanik" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.mekanik.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Mekanik Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_mekanik" class="form-label fw-semibold">Nama Lengkap Mekanik</label>
                        <input type="text" class="form-control" id="nama_mekanik" name="nama_mekanik" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label fw-semibold">No. Telepon / HP</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" placeholder="Contoh: 081234567890">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Mekanik</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

