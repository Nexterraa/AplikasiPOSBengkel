@extends('layouts.app')

@section('title', 'Transaksi Baru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cart-plus me-2 text-primary"></i> Transaksi Baru</h4>
        <p class="text-muted mb-0 small">Catat servis kendaraan dan penjualan sparepart.</p>
    </div>
    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan Validasi:</h6>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('transactions.store') }}" method="POST" id="trxForm">
    @csrf

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Kendaraan, Pemilik, dan Mekanik -->
        <div class="col-lg-5">
            <!-- Card Data Kendaraan & Pemilik -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bicycle me-2 text-primary"></i> Kendaraan & Pelanggan</span>
                    <div class="form-check form-switch mb-0 small">
                        <input class="form-check-input" type="checkbox" id="toggleNewVehicle" name="is_new_vehicle" value="1" {{ old('is_new_vehicle') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="toggleNewVehicle">Kendaraan Baru</label>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Mode 1: Pilih Kendaraan yang Sudah Terdaftar -->
                    <div id="sectionExistingVehicle" style="{{ old('is_new_vehicle') ? 'display: none;' : '' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pilih Kendaraan Terdaftar <span class="text-danger">*</span></label>
                            <select name="vehicle_id" id="vehicleSelect" class="form-select @error('vehicle_id') is-invalid @enderror">
                                <option value="">-- Cari / Pilih Plat Nomor Kendaraan --</option>
                                @foreach ($vehicles as $v)
                                    <option value="{{ $v->id }}" 
                                        data-customer-id="{{ $v->customer_id }}"
                                        data-customer-name="{{ $v->customer->name ?? '-' }}"
                                        data-customer-phone="{{ $v->customer->phone ?? '-' }}"
                                        data-brand="{{ $v->brand }}"
                                        data-model="{{ $v->model }}"
                                        data-year="{{ $v->year }}"
                                        {{ (old('vehicle_id', $selectedVehicleId) == $v->id) ? 'selected' : '' }}>
                                        {{ $v->plate_number }} ({{ $v->brand }} {{ $v->model }}) — {{ $v->customer->name ?? 'Tanpa Pemilik' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Info Ringkas Pemilik dari Kendaraan Terpilih -->
                        <div id="existingCustomerInfo" class="p-3 bg-light rounded-3 border mb-3" style="display: none;">
                            <div class="small text-muted mb-1"><i class="bi bi-person-circle me-1"></i> Data Pemilik Kendaraan:</div>
                            <div class="fw-bold" id="infoCustomerName">-</div>
                            <div class="small text-muted" id="infoCustomerPhone">-</div>
                            <div class="small text-secondary mt-1" id="infoVehicleDetail">-</div>
                            <input type="hidden" name="customer_id" id="hiddenCustomerId" value="{{ old('customer_id') }}">
                        </div>
                    </div>

                    <!-- Mode 2: Input Kendaraan Baru & Pemilik Baru/Lama -->
                    <div id="sectionNewVehicle" style="{{ old('is_new_vehicle') ? '' : 'display: none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor Polisi (Plat Nomor) <span class="text-danger">*</span></label>
                            <input type="text" name="plate_number" id="inputPlateNumber" class="form-control text-uppercase font-monospace @error('plate_number') is-invalid @enderror" placeholder="Contoh: B 1234 ABC" value="{{ old('plate_number') }}">
                            @error('plate_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Merek <span class="text-danger">*</span></label>
                                <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" placeholder="Honda, Yamaha..." value="{{ old('brand') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Model/Tipe <span class="text-danger">*</span></label>
                                <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" placeholder="Vario 150, NMAX..." value="{{ old('model') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tahun Pembuatan</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" placeholder="{{ date('Y') }}" value="{{ old('year') }}">
                        </div>

                        <hr class="my-3">

                        <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="bi bi-person-fill me-1"></i> Pemilik Kendaraan Baru</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Pelanggan / Pemilik <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" placeholder="Nama lengkap pelanggan" value="{{ old('customer_name') }}">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('customer_phone') }}">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Mekanik & Keluhan -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="bi bi-person-workspace me-2 text-primary"></i> Mekanik & Keluhan Servis
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Pilih Mekanik <span id="mekanikRequiredAsterisk" class="text-danger" style="display: none;">*</span>
                        </label>
                        <select name="mekanik_id" id="mekanikSelect" class="form-select @error('mekanik_id') is-invalid @enderror">
                            <option value="">-- Pilih Mekanik (Wajib bila ada jasa servis) --</option>
                            @foreach ($mechanics as $m)
                                <option value="{{ $m->id }}" {{ old('mekanik_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_mekanik }} ({{ $m->telepon ?? 'Mekanik' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            Mekanik wajib diisi jika ada jasa servis. Boleh kosong jika hanya pembelian sparepart.
                        </div>
                        @error('mekanik_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Keluhan / Catatan Kendaraan</label>
                        <textarea name="keluhan" rows="3" class="form-control @error('keluhan') is-invalid @enderror" placeholder="Misal: Rem belakang bunyi, oli minta ganti, tarikan berat...">{{ old('keluhan') }}</textarea>
                        @error('keluhan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Jasa Servis, Sparepart, dan Ringkasan Total -->
        <div class="col-lg-7">
            <!-- Card 1: Jasa Servis -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tools me-2 text-primary"></i> Jasa Servis</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddJasa">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Jasa
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableJasa">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 45%;">Nama Jasa Servis</th>
                                    <th class="text-end" style="width: 25%;">Tarif (Rp)</th>
                                    <th class="text-center" style="width: 15%;">Qty</th>
                                    <th class="text-end" style="width: 25%;">Subtotal (Rp)</th>
                                    <th class="text-center" style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="jasaRowsContainer">
                                <!-- Baris Jasa Dinamis -->
                            </tbody>
                        </table>
                    </div>
                    <div id="emptyJasaMessage" class="p-4 text-center text-muted small">
                        Belum ada jasa servis yang ditambahkan. Klik <strong>+ Tambah Jasa</strong> di atas jika kendaraan diservis.
                    </div>
                </div>
            </div>

            <!-- Card 2: Sparepart & Produk -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-box-seam me-2 text-primary"></i> Sparepart & Produk</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddSparepart">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Sparepart
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableSparepart">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 35%;">Sparepart</th>
                                    <th style="width: 20%;">Jenis Harga</th>
                                    <th class="text-end" style="width: 20%;">Harga (Rp)</th>
                                    <th class="text-center" style="width: 10%;">Qty</th>
                                    <th class="text-end" style="width: 20%;">Subtotal (Rp)</th>
                                    <th class="text-center" style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="sparepartRowsContainer">
                                <!-- Baris Sparepart Dinamis -->
                            </tbody>
                        </table>
                    </div>
                    <div id="emptySparepartMessage" class="p-4 text-center text-muted small">
                        Belum ada sparepart yang ditambahkan. Klik <strong>+ Tambah Sparepart</strong> di atas jika ada penggantian part.
                    </div>
                </div>
            </div>

            <!-- Card 3: Total & Konfirmasi -->
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Subtotal Jasa Servis:</span>
                        <span class="fw-semibold" id="labelTotalJasa">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Subtotal Sparepart:</span>
                        <span class="fw-semibold" id="labelTotalSparepart">Rp 0</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-5 fw-bold text-dark">Total Transaksi:</span>
                        <span class="fs-4 fw-bold text-primary" id="labelGrandTotal">Rp 0</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-clock-history me-1"></i> Status Awal: BELUM DIBAYAR
                        </span>
                        <span class="text-muted small">Nomor invoice otomatis digenerate (format: INV-YYYYMMDD-XXXX)</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm" id="btnSubmitTrx">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Data JSON untuk JavaScript -->
<script>
    window.POS_SERVICES = @json($services);
    window.POS_SPAREPARTS = @json($spareparts);
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const services = window.POS_SERVICES || [];
    const spareparts = window.POS_SPAREPARTS || [];

    // Toggle Mode Kendaraan
    const toggleNewVehicle = document.getElementById('toggleNewVehicle');
    const sectionExisting = document.getElementById('sectionExistingVehicle');
    const sectionNew = document.getElementById('sectionNewVehicle');
    const vehicleSelect = document.getElementById('vehicleSelect');
    const existingCustomerInfo = document.getElementById('existingCustomerInfo');
    const infoCustomerName = document.getElementById('infoCustomerName');
    const infoCustomerPhone = document.getElementById('infoCustomerPhone');
    const infoVehicleDetail = document.getElementById('infoVehicleDetail');
    const hiddenCustomerId = document.getElementById('hiddenCustomerId');

    function updateVehicleMode() {
        if (toggleNewVehicle.checked) {
            sectionExisting.style.display = 'none';
            sectionNew.style.display = 'block';
            vehicleSelect.value = '';
            existingCustomerInfo.style.display = 'none';
            hiddenCustomerId.value = '';
        } else {
            sectionExisting.style.display = 'block';
            sectionNew.style.display = 'none';
            handleVehicleChange();
        }
    }

    function handleVehicleChange() {
        const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selected && selected.value) {
            infoCustomerName.textContent = selected.getAttribute('data-customer-name') || '-';
            infoCustomerPhone.textContent = selected.getAttribute('data-customer-phone') || '-';
            infoVehicleDetail.textContent = (selected.getAttribute('data-brand') || '') + ' ' + (selected.getAttribute('data-model') || '') + ' (' + (selected.getAttribute('data-year') || '-') + ')';
            hiddenCustomerId.value = selected.getAttribute('data-customer-id') || '';
            existingCustomerInfo.style.display = 'block';
        } else {
            existingCustomerInfo.style.display = 'none';
            hiddenCustomerId.value = '';
        }
    }

    toggleNewVehicle.addEventListener('change', updateVehicleMode);
    vehicleSelect.addEventListener('change', handleVehicleChange);
    handleVehicleChange();

    // Dinamis Jasa Servis
    const btnAddJasa = document.getElementById('btnAddJasa');
    const jasaContainer = document.getElementById('jasaRowsContainer');
    const emptyJasaMsg = document.getElementById('emptyJasaMessage');
    let jasaIndex = 0;

    function renderJasaOptions() {
        let options = '<option value="">-- Pilih Jasa Servis --</option>';
        services.forEach(s => {
            options += `<option value="${s.id}" data-harga="${s.harga}">${s.nama_jasa} (Rp ${Number(s.harga).toLocaleString('id-ID')})</option>`;
        });
        return options;
    }

    btnAddJasa.addEventListener('click', function () {
        emptyJasaMsg.style.display = 'none';
        const rowId = `jasa_row_${jasaIndex}`;
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td class="ps-3">
                <select name="jasa_items[${jasaIndex}][jasa_id]" class="form-select form-select-sm select-jasa" required>
                    ${renderJasaOptions()}
                </select>
            </td>
            <td class="text-end">
                <span class="jasa-price-label text-muted">0</span>
            </td>
            <td class="text-center">
                <input type="number" name="jasa_items[${jasaIndex}][qty]" class="form-control form-control-sm text-center input-jasa-qty" value="1" min="1">
            </td>
            <td class="text-end fw-bold">
                <span class="jasa-subtotal-label">0</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus"><i class="bi bi-trash"></i></button>
            </td>
        `;
        jasaContainer.appendChild(tr);
        jasaIndex++;
        bindJasaRowEvents(tr);
        recalculateTotals();
    });

    function bindJasaRowEvents(row) {
        const select = row.querySelector('.select-jasa');
        const qtyInput = row.querySelector('.input-jasa-qty');
        const priceLabel = row.querySelector('.jasa-price-label');
        const subtotalLabel = row.querySelector('.jasa-subtotal-label');
        const btnRemove = row.querySelector('.btn-remove-row');

        function updateRow() {
            const opt = select.options[select.selectedIndex];
            const price = opt && opt.value ? parseFloat(opt.getAttribute('data-harga')) || 0 : 0;
            const qty = Math.max(1, parseInt(qtyInput.value) || 1);
            const subtotal = price * qty;

            priceLabel.textContent = price.toLocaleString('id-ID');
            subtotalLabel.textContent = subtotal.toLocaleString('id-ID');
            recalculateTotals();
        }

        select.addEventListener('change', updateRow);
        qtyInput.addEventListener('input', updateRow);
        btnRemove.addEventListener('click', function () {
            row.remove();
            if (jasaContainer.children.length === 0) {
                emptyJasaMsg.style.display = 'block';
            }
            recalculateTotals();
        });
    }

    // Dinamis Sparepart
    const btnAddSparepart = document.getElementById('btnAddSparepart');
    const sparepartContainer = document.getElementById('sparepartRowsContainer');
    const emptySparepartMsg = document.getElementById('emptySparepartMessage');
    let sparepartIndex = 0;

    function renderSparepartOptions() {
        let options = '<option value="">-- Pilih Sparepart --</option>';
        spareparts.forEach(p => {
            options += `<option value="${p.id}" 
                data-satuan="${p.harga_jual}" 
                data-stok="${p.stok}"
                data-wholesale-price="${p.wholesale_price || ''}"
                data-wholesale-min="${p.wholesale_min_qty || ''}"
                data-promo-price="${p.promo_price || ''}">
                ${p.nama_sparepart} (Stok: ${p.stok})
            </option>`;
        });
        return options;
    }

    btnAddSparepart.addEventListener('click', function () {
        emptySparepartMsg.style.display = 'none';
        const rowId = `sparepart_row_${sparepartIndex}`;
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td class="ps-3">
                <select name="sparepart_items[${sparepartIndex}][sparepart_id]" class="form-select form-select-sm select-sparepart" required>
                    ${renderSparepartOptions()}
                </select>
                <div class="small text-muted stock-indicator mt-1"></div>
            </td>
            <td>
                <select name="sparepart_items[${sparepartIndex}][price_type]" class="form-select form-select-sm select-price-type">
                    <option value="satuan">Satuan</option>
                    <option value="grosir" disabled>Grosir</option>
                    <option value="promo" disabled>Promo</option>
                </select>
            </td>
            <td class="text-end">
                <span class="sparepart-price-label text-muted">0</span>
            </td>
            <td class="text-center">
                <input type="number" name="sparepart_items[${sparepartIndex}][qty]" class="form-control form-control-sm text-center input-sparepart-qty" value="1" min="1">
            </td>
            <td class="text-end fw-bold">
                <span class="sparepart-subtotal-label">0</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus"><i class="bi bi-trash"></i></button>
            </td>
        `;
        sparepartContainer.appendChild(tr);
        sparepartIndex++;
        bindSparepartRowEvents(tr);
        recalculateTotals();
    });

    function bindSparepartRowEvents(row) {
        const selectPart = row.querySelector('.select-sparepart');
        const selectPriceType = row.querySelector('.select-price-type');
        const qtyInput = row.querySelector('.input-sparepart-qty');
        const priceLabel = row.querySelector('.sparepart-price-label');
        const subtotalLabel = row.querySelector('.sparepart-subtotal-label');
        const stockIndicator = row.querySelector('.stock-indicator');
        const btnRemove = row.querySelector('.btn-remove-row');

        function updateOptionsAndPrice() {
            const opt = selectPart.options[selectPart.selectedIndex];
            if (!opt || !opt.value) {
                priceLabel.textContent = '0';
                subtotalLabel.textContent = '0';
                stockIndicator.textContent = '';
                recalculateTotals();
                return;
            }

            const stock = parseInt(opt.getAttribute('data-stok')) || 0;
            const satuanPrice = parseFloat(opt.getAttribute('data-satuan')) || 0;
            const wholesalePrice = parseFloat(opt.getAttribute('data-wholesale-price')) || null;
            const wholesaleMin = parseInt(opt.getAttribute('data-wholesale-min')) || null;
            const promoPrice = parseFloat(opt.getAttribute('data-promo-price')) || null;

            stockIndicator.textContent = `Tersedia: ${stock} unit`;
            qtyInput.max = stock;

            // Update enable/disable option grosir & promo
            const optGrosir = selectPriceType.querySelector('option[value="grosir"]');
            const optPromo = selectPriceType.querySelector('option[value="promo"]');

            if (wholesalePrice && wholesaleMin) {
                optGrosir.disabled = false;
                optGrosir.textContent = `Grosir (Min ${wholesaleMin})`;
            } else {
                optGrosir.disabled = true;
                optGrosir.textContent = 'Grosir (N/A)';
                if (selectPriceType.value === 'grosir') selectPriceType.value = 'satuan';
            }

            if (promoPrice && promoPrice > 0) {
                optPromo.disabled = false;
                optPromo.textContent = 'Promo';
            } else {
                optPromo.disabled = true;
                optPromo.textContent = 'Promo (N/A)';
                if (selectPriceType.value === 'promo') selectPriceType.value = 'satuan';
            }

            let qty = Math.max(1, parseInt(qtyInput.value) || 1);
            if (qty > stock) {
                qty = stock;
                qtyInput.value = qty;
            }

            let price = satuanPrice;
            if (selectPriceType.value === 'grosir' && wholesalePrice) {
                price = wholesalePrice;
            } else if (selectPriceType.value === 'promo' && promoPrice) {
                price = promoPrice;
            }

            const subtotal = price * qty;
            priceLabel.textContent = price.toLocaleString('id-ID');
            subtotalLabel.textContent = subtotal.toLocaleString('id-ID');
            recalculateTotals();
        }

        selectPart.addEventListener('change', updateOptionsAndPrice);
        selectPriceType.addEventListener('change', updateOptionsAndPrice);
        qtyInput.addEventListener('input', updateOptionsAndPrice);

        btnRemove.addEventListener('click', function () {
            row.remove();
            if (sparepartContainer.children.length === 0) {
                emptySparepartMsg.style.display = 'block';
            }
            recalculateTotals();
        });
    }

    // Perhitungan Total
    const labelTotalJasa = document.getElementById('labelTotalJasa');
    const labelTotalSparepart = document.getElementById('labelTotalSparepart');
    const labelGrandTotal = document.getElementById('labelGrandTotal');
    const mekanikAsterisk = document.getElementById('mekanikRequiredAsterisk');
    const mekanikSelect = document.getElementById('mekanikSelect');

    function recalculateTotals() {
        let totalJasa = 0;
        let hasJasa = false;

        document.querySelectorAll('#jasaRowsContainer tr').forEach(row => {
            const sel = row.querySelector('.select-jasa');
            if (sel && sel.value) {
                hasJasa = true;
                const opt = sel.options[sel.selectedIndex];
                const price = opt ? parseFloat(opt.getAttribute('data-harga')) || 0 : 0;
                const qty = parseInt(row.querySelector('.input-jasa-qty').value) || 1;
                totalJasa += price * qty;
            }
        });

        let totalSparepart = 0;
        document.querySelectorAll('#sparepartRowsContainer tr').forEach(row => {
            const sel = row.querySelector('.select-sparepart');
            if (sel && sel.value) {
                const opt = sel.options[sel.selectedIndex];
                const priceType = row.querySelector('.select-price-type').value;
                let price = parseFloat(opt.getAttribute('data-satuan')) || 0;
                if (priceType === 'grosir' && opt.getAttribute('data-wholesale-price')) {
                    price = parseFloat(opt.getAttribute('data-wholesale-price'));
                } else if (priceType === 'promo' && opt.getAttribute('data-promo-price')) {
                    price = parseFloat(opt.getAttribute('data-promo-price'));
                }
                const qty = parseInt(row.querySelector('.input-sparepart-qty').value) || 1;
                totalSparepart += price * qty;
            }
        });

        const grandTotal = totalJasa + totalSparepart;

        labelTotalJasa.textContent = 'Rp ' + totalJasa.toLocaleString('id-ID');
        labelTotalSparepart.textContent = 'Rp ' + totalSparepart.toLocaleString('id-ID');
        labelGrandTotal.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

        if (hasJasa) {
            mekanikAsterisk.style.display = 'inline';
            mekanikSelect.required = true;
        } else {
            mekanikAsterisk.style.display = 'none';
            mekanikSelect.required = false;
        }
    }
});
</script>
@endpush

