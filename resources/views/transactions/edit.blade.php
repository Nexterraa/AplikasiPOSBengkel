@extends('layouts.app')

@section('title', 'Edit Transaksi ' . $transaction->no_transaksi)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-warning"></i> Edit Transaksi: {{ $transaction->no_transaksi }}</h4>
        <p class="text-muted mb-0 small">Ubah rincian jasa servis, sparepart, mekanik, atau keluhan pelanggan.</p>
    </div>
    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
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

<form action="{{ route('transactions.update', $transaction) }}" method="POST" id="trxEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Pelanggan & Kendaraan (Readonly) dan Mekanik (Editable) -->
        <div class="col-lg-5">
            <!-- Data Kendaraan & Pemilik (Terkunci) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-lock-fill me-2 text-muted"></i> Kendaraan & Pelanggan</span>
                    <span class="badge bg-light text-muted border">Terkunci</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i> Sesuai aturan, data pelanggan dan kendaraan tidak dapat diubah pada transaksi yang sudah dibuat.
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Nomor Polisi</label>
                        <div class="fs-5 fw-bold font-monospace text-primary">
                            {{ $transaction->vehicle->plate_number ?? '-' }}
                        </div>
                        <div class="small text-secondary">
                            {{ $transaction->vehicle->brand ?? '' }} {{ $transaction->vehicle->model ?? '' }} ({{ $transaction->vehicle->year ?? '-' }})
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Pemilik Kendaraan</label>
                        <div class="fw-bold">{{ $transaction->customer->name ?? '-' }}</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text-muted small mb-0">Nomor Telepon</label>
                        <div class="text-secondary">{{ $transaction->customer->phone ?? '-' }}</div>
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
                                <option value="{{ $m->id }}" {{ old('mekanik_id', $transaction->mekanik_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_mekanik }} ({{ $m->telepon ?? 'Mekanik' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            Mekanik wajib diisi jika ada jasa servis. Boleh kosong jika hanya sparepart.
                        </div>
                        @error('mekanik_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Keluhan / Catatan Kendaraan</label>
                        <textarea name="keluhan" rows="3" class="form-control @error('keluhan') is-invalid @enderror" placeholder="Catatan keluhan...">{{ old('keluhan', $transaction->keluhan) }}</textarea>
                        @error('keluhan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rincian Jasa & Sparepart yang Dapat Diedit -->
        <div class="col-lg-7">
            <!-- Jasa Servis -->
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
                    <div id="emptyJasaMessage" class="p-4 text-center text-muted small" style="display: none;">
                        Belum ada jasa servis yang ditambahkan.
                    </div>
                </div>
            </div>

            <!-- Sparepart -->
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
                    <div id="emptySparepartMessage" class="p-4 text-center text-muted small" style="display: none;">
                        Belum ada sparepart yang ditambahkan.
                    </div>
                </div>
            </div>

            <!-- Ringkasan Total & Submit -->
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Total Transaksi:</span>
                        <span class="fs-4 fw-bold text-primary" id="labelGrandTotal">Rp 0</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold shadow-sm" id="btnSubmitTrx">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    window.POS_SERVICES = @json($services);
    window.POS_SPAREPARTS = @json($spareparts);
    window.POS_INITIAL_JASA = @json($transaction->details->where('item_type', 'jasa')->values());
    window.POS_INITIAL_SPAREPARTS = @json($transaction->details->where('item_type', 'sparepart')->values());
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const services = window.POS_SERVICES || [];
    const spareparts = window.POS_SPAREPARTS || [];
    const initialJasa = window.POS_INITIAL_JASA || [];
    const initialSpareparts = window.POS_INITIAL_SPAREPARTS || [];

    // Jasa Servis
    const btnAddJasa = document.getElementById('btnAddJasa');
    const jasaContainer = document.getElementById('jasaRowsContainer');
    const emptyJasaMsg = document.getElementById('emptyJasaMessage');
    let jasaIndex = 0;

    function renderJasaOptions(selectedId = '') {
        let options = '<option value="">-- Pilih Jasa Servis --</option>';
        services.forEach(s => {
            const isSel = (s.id == selectedId) ? 'selected' : '';
            options += `<option value="${s.id}" data-harga="${s.harga}" ${isSel}>${s.nama_jasa} (Rp ${Number(s.harga).toLocaleString('id-ID')})</option>`;
        });
        return options;
    }

    function addJasaRow(selectedId = '', initialQty = 1) {
        emptyJasaMsg.style.display = 'none';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-3">
                <select name="jasa_items[${jasaIndex}][jasa_id]" class="form-select form-select-sm select-jasa" required>
                    ${renderJasaOptions(selectedId)}
                </select>
            </td>
            <td class="text-end">
                <span class="jasa-price-label text-muted">0</span>
            </td>
            <td class="text-center">
                <input type="number" name="jasa_items[${jasaIndex}][qty]" class="form-control form-control-sm text-center input-jasa-qty" value="${initialQty}" min="1">
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
    }

    btnAddJasa.addEventListener('click', function () {
        addJasaRow();
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

        updateRow();
    }

    // Sparepart
    const btnAddSparepart = document.getElementById('btnAddSparepart');
    const sparepartContainer = document.getElementById('sparepartRowsContainer');
    const emptySparepartMsg = document.getElementById('emptySparepartMessage');
    let sparepartIndex = 0;

    function renderSparepartOptions(selectedId = '') {
        let options = '<option value="">-- Pilih Sparepart --</option>';
        spareparts.forEach(p => {
            const isSel = (p.id == selectedId) ? 'selected' : '';
            options += `<option value="${p.id}" 
                data-satuan="${p.harga_jual}" 
                data-stok="${p.stok}"
                data-wholesale-price="${p.wholesale_price || ''}"
                data-wholesale-min="${p.wholesale_min_qty || ''}"
                data-promo-price="${p.promo_price || ''}"
                ${isSel}>
                ${p.nama_sparepart} (Stok: ${p.stok})
            </option>`;
        });
        return options;
    }

    function addSparepartRow(selectedId = '', initialPriceType = 'satuan', initialQty = 1) {
        emptySparepartMsg.style.display = 'none';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-3">
                <select name="sparepart_items[${sparepartIndex}][sparepart_id]" class="form-select form-select-sm select-sparepart" required>
                    ${renderSparepartOptions(selectedId)}
                </select>
                <div class="small text-muted stock-indicator mt-1"></div>
            </td>
            <td>
                <select name="sparepart_items[${sparepartIndex}][price_type]" class="form-select form-select-sm select-price-type">
                    <option value="satuan" ${initialPriceType === 'satuan' ? 'selected' : ''}>Satuan</option>
                    <option value="grosir" ${initialPriceType === 'grosir' ? 'selected' : ''} disabled>Grosir</option>
                    <option value="promo" ${initialPriceType === 'promo' ? 'selected' : ''} disabled>Promo</option>
                </select>
            </td>
            <td class="text-end">
                <span class="sparepart-price-label text-muted">0</span>
            </td>
            <td class="text-center">
                <input type="number" name="sparepart_items[${sparepartIndex}][qty]" class="form-control form-control-sm text-center input-sparepart-qty" value="${initialQty}" min="1">
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
        bindSparepartRowEvents(tr, initialPriceType);
        recalculateTotals();
    }

    btnAddSparepart.addEventListener('click', function () {
        addSparepartRow();
    });

    function bindSparepartRowEvents(row, initialType = 'satuan') {
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

        updateOptionsAndPrice();
    }

    // Load initial items
    if (initialJasa.length > 0) {
        initialJasa.forEach(item => {
            addJasaRow(item.jasa_servis_id, item.jumlah);
        });
    } else {
        emptyJasaMsg.style.display = 'block';
    }

    if (initialSpareparts.length > 0) {
        initialSpareparts.forEach(item => {
            addSparepartRow(item.sparepart_id, item.jenis_harga || 'satuan', item.jumlah);
        });
    } else {
        emptySparepartMsg.style.display = 'block';
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

