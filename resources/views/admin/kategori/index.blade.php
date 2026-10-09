@extends('layouts.app')

@section('title', 'Kategori Sparepart')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-tags text-primary me-2"></i>Kategori Sparepart</h3>
        <p class="text-muted mb-0">Kelola kategori untuk mengelompokkan produk & sparepart</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <form action="{{ route('admin.kategori.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    @if($search)
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Reset</a>
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
                        <th scope="col">Nama Kategori</th>
                        <th scope="col">Jumlah Sparepart</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $index => $kat)
                        <tr>
                            <td class="ps-3 text-muted">{{ $kategoris->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $kat->nama_kategori }}</td>
                            <td><span class="badge bg-info text-dark">{{ $kat->spareparts_count }} Produk</span></td>
                            <td>
                                @if($kat->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $kat->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('admin.kategori.destroy', $kat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Kategori -->
                        <div class="modal fade" id="modalEditKategori{{ $kat->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.kategori.update', $kat->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3 text-start">
                                                <label class="form-label fw-semibold">Nama Kategori</label>
                                                <input type="text" name="nama_kategori" class="form-control" value="{{ old('nama_kategori', $kat->nama_kategori) }}" required>
                                            </div>
                                            <div class="mb-3 text-start">
                                                <label class="form-label fw-semibold">Status Kategori</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1" {{ $kat->is_active ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$kat->is_active ? 'selected' : '' }}>Nonaktif</option>
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
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($kategoris->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $kategoris->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.kategori.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Oli Mesin, Ban, Kampas Rem" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

