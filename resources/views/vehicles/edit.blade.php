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

                    {{-- Cari Nama Pemilik (Search Bar) --}}
                    <div class="mb-3 position-relative">
                        <label for="customer_search" class="form-label fw-semibold">
                            Cari Nama Pemilik <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="customer_search"
                                class="form-control @error('customer_id') is-invalid @enderror"
                                placeholder="Ketik nama pemilik untuk mencari..."
                                autocomplete="off"
                                value="{{ old('customer_name_display', $selectedCustomer ? $selectedCustomer->name : '') }}">
                            <button class="btn btn-outline-secondary {{ $selectedCustomer ? '' : 'd-none' }}" type="button" id="btn_clear_customer" title="Hapus pilihan">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        {{-- Hidden input customer_id for form submission --}}
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $vehicle->customer_id) }}">

                        {{-- Live Dropdown Search Results --}}
                        <div id="customer_results" class="list-group position-absolute w-100 shadow mt-1 d-none" style="z-index: 1050; max-height: 220px; overflow-y: auto;">
                            @foreach ($customers as $c)
                                <button type="button" class="list-group-item list-group-item-action py-2 customer-item text-start"
                                    data-id="{{ $c->id }}"
                                    data-name="{{ $c->name }}"
                                    data-phone="{{ $c->phone }}">
                                    <div class="fw-semibold text-dark">{{ $c->name }}</div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $c->phone }}</small>
                                </button>
                            @endforeach
                            <div id="customer_not_found" class="list-group-item py-3 text-center d-none">
                                <div class="text-danger fw-semibold mb-1">
                                    <i class="bi bi-exclamation-circle me-1"></i> Pemilik belum terdaftar
                                </div>
                                <div class="small text-muted mb-2">Nama "<span id="not_found_name"></span>" belum terdaftar di sistem.</div>
                                <a href="{{ route('customers.create') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-person-plus me-1"></i> Daftarkan Pelanggan Baru
                                </a>
                            </div>
                        </div>

                        {{-- Notifikasi Pemilik Belum Terdaftar --}}
                        <div id="customer_unregistered_alert" class="mt-2 d-none">
                            <div class="alert alert-warning border py-2 px-3 mb-0 small d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                    <span>Pemilik belum terdaftar: "<strong id="unregistered_query"></strong>"</span>
                                </div>
                                <a href="{{ route('customers.create') }}" class="btn btn-sm btn-warning text-dark fw-semibold" target="_blank">
                                    <i class="bi bi-person-plus me-1"></i> Daftarkan Pelanggan Baru
                                </a>
                            </div>
                        </div>

                        {{-- Status Terpilih --}}
                        <div id="selected_customer_info" class="mt-2 {{ $selectedCustomer ? '' : 'd-none' }}">
                            <div class="alert alert-success border-0 py-2 px-3 mb-0 small d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-check-circle-fill me-1"></i> Pemilik terpilih: <strong id="selected_name">{{ $selectedCustomer ? $selectedCustomer->name : '' }}</strong> <span id="selected_phone" class="text-muted">({{ $selectedCustomer ? $selectedCustomer->phone : '' }})</span>
                                </div>
                                <span class="badge bg-success">Siap Disimpan</span>
                            </div>
                        </div>

                        @error('customer_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('customer_search');
    const resultsBox = document.getElementById('customer_results');
    const hiddenInput = document.getElementById('customer_id');
    const selectedInfo = document.getElementById('selected_customer_info');
    const selectedName = document.getElementById('selected_name');
    const selectedPhone = document.getElementById('selected_phone');
    const clearBtn = document.getElementById('btn_clear_customer');
    const items = document.querySelectorAll('.customer-item');
    const notFound = document.getElementById('customer_not_found');
    const notFoundName = document.getElementById('not_found_name');
    const unregisteredAlert = document.getElementById('customer_unregistered_alert');
    const unregisteredQuery = document.getElementById('unregistered_query');

    function filterResults() {
        const rawQuery = searchInput.value.trim();
        const query = rawQuery.toLowerCase();
        if (query.length === 0) {
            resultsBox.classList.add('d-none');
            if (unregisteredAlert) unregisteredAlert.classList.add('d-none');
            return;
        }

        let matchCount = 0;
        items.forEach(item => {
            const name = item.dataset.name.toLowerCase();
            const phone = item.dataset.phone.toLowerCase();
            if (name.includes(query) || phone.includes(query)) {
                item.classList.remove('d-none');
                matchCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (matchCount > 0) {
            notFound.classList.add('d-none');
            resultsBox.classList.remove('d-none');
            if (unregisteredAlert) unregisteredAlert.classList.add('d-none');
        } else {
            if (notFoundName) notFoundName.textContent = rawQuery;
            notFound.classList.remove('d-none');
            resultsBox.classList.remove('d-none');
            if (unregisteredQuery) unregisteredQuery.textContent = rawQuery;
            if (unregisteredAlert) unregisteredAlert.classList.remove('d-none');
        }
    }

    searchInput.addEventListener('input', function () {
        if (hiddenInput.value) {
            hiddenInput.value = '';
            selectedInfo.classList.add('d-none');
            if (clearBtn) clearBtn.classList.add('d-none');
        }
        filterResults();
    });

    searchInput.addEventListener('focus', function () {
        if (this.value.trim().length > 0 && !hiddenInput.value) {
            filterResults();
        }
    });

    items.forEach(item => {
        item.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const phone = this.dataset.phone;

            hiddenInput.value = id;
            searchInput.value = name; // Tampilkan nama saja tanpa nomor telepon
            selectedName.textContent = name;
            selectedPhone.textContent = '(' + phone + ')';
            selectedInfo.classList.remove('d-none');
            resultsBox.classList.add('d-none');
            if (unregisteredAlert) unregisteredAlert.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            hiddenInput.value = '';
            searchInput.value = '';
            selectedInfo.classList.add('d-none');
            resultsBox.classList.add('d-none');
            if (unregisteredAlert) unregisteredAlert.classList.add('d-none');
            clearBtn.classList.add('d-none');
            searchInput.focus();
        });
    }

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.add('d-none');
        }
    });
});
</script>
@endpush

