@extends('layouts.app')

@section('title', 'Kelola Akun Kasir')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>Kelola Akun Kasir</h3>
        <p class="text-muted mb-0">Kelola akun pengguna dengan role Kasir (Khusus Admin)</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKasir">
        <i class="bi bi-person-plus me-1"></i> Buat Akun Kasir
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <form action="{{ route('admin.kasir.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if($search)
                        <a href="{{ route('admin.kasir.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Reset</a>
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
                        <th scope="col">Nama Kasir</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status Akun</th>
                        <th scope="col" class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kasirs as $index => $ksr)
                        <tr>
                            <td class="ps-3 text-muted">{{ $kasirs->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $ksr->name }}</td>
                            <td>{{ $ksr->email }}</td>
                            <td><span class="badge bg-primary text-uppercase">{{ $ksr->role }}</span></td>
                            <td>
                                @if($ksr->is_active)
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditKasir{{ $ksr->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('admin.kasir.toggleStatus', $ksr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif akun kasir ini?')">
                                    @csrf
                                    @method('PATCH')
                                    @if($ksr->is_active)
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-x me-1"></i> Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-person-check me-1"></i> Aktifkan
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Kasir -->
                        <div class="modal fade" id="modalEditKasir{{ $ksr->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.kasir.update', $ksr->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Akun Kasir</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Kasir</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $ksr->name) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alamat Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $ksr->email) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Password Baru (Opsional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Status Akun</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1" {{ $ksr->is_active ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$ksr->is_active ? 'selected' : '' }}>Nonaktif</option>
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
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada akun kasir terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($kasirs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $kasirs->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Kasir -->
<div class="modal fade" id="modalTambahKasir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.kasir.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Buat Akun Kasir Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap Kasir</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Siska Rahma" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="siska@bengkel.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password Default</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 8 karakter" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Akun Kasir</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

