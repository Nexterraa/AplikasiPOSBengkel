@extends('layouts.app')

@section('title', 'Pembayaran ' . $transaction->no_transaksi)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-credit-card me-2 text-primary"></i> Proses Pembayaran</h4>
        <p class="text-muted mb-0 small">Selesaikan pembayaran untuk transaksi <strong>{{ $transaction->no_transaksi }}</strong>.</p>
    </div>
    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</h6>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('transactions.pay', $transaction) }}" method="POST" id="formPayment">
    @csrf

    <div class="row g-4">
        <!-- Kolom Kiri: Ringkasan Tagihan & Rincian Item -->
        <div class="col-lg-6">
            <!-- Card Ringkasan Transaksi -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-receipt me-2 text-primary"></i> Tagihan Transaksi</span>
                    <span class="badge bg-warning text-dark">BELUM DIBAYAR</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">No. Invoice:</span>
                        <span class="fw-bold font-monospace">{{ $transaction->no_transaksi }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Pelanggan:</span>
                        <span class="fw-semibold">{{ $transaction->customer->name ?? '-' }} ({{ $transaction->customer->phone ?? '-' }})</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Kendaraan:</span>
                        <span class="font-monospace fw-bold">{{ $transaction->vehicle->plate_number ?? '-' }}</span>
                    </div>
                    @if ($transaction->mekanik)
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Mekanik:</span>
                            <span>{{ $transaction->mekanik->nama_mekanik }}</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5 fw-bold text-dark">Total Tagihan:</span>
                        <span class="fs-4 fw-bold text-primary">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Rincian Item -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="bi bi-list-check me-2 text-primary"></i> Rincian Item ({{ $transaction->details->count() }})
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">Item</th>
                                    <th class="text-end">Tarif/Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach ($transaction->details as $d)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ $d->nama_item }}</div>
                                            <span class="badge {{ $d->isJasa() ? 'bg-info text-dark' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                                {{ $d->isJasa() ? 'Jasa' : 'Sparepart' }}
                                                @if ($d->jenis_harga)
                                                    • {{ ucfirst($d->jenis_harga) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ $d->jumlah }}</td>
                                        <td class="text-end pe-3 fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Formulir Pembayaran -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="bi bi-cash-stack me-2 text-primary"></i> Metode & Nominal Pembayaran
                </div>
                <div class="card-body p-4">
                    <!-- Pilihan Metode Pembayaran -->
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Pilih Metode Pembayaran <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="metode" id="metodeCash" value="cash" autocomplete="off" {{ old('metode', 'cash') === 'cash' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary w-100 py-3 text-center rounded-3 d-flex flex-column align-items-center gap-1" for="metodeCash">
                                <i class="bi bi-cash-coin fs-3"></i>
                                <span class="fw-bold">Cash (Tunai)</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="metode" id="metodeQris" value="qris" autocomplete="off" {{ old('metode') === 'qris' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary w-100 py-3 text-center rounded-3 d-flex flex-column align-items-center gap-1" for="metodeQris">
                                <i class="bi bi-qr-code-scan fs-3"></i>
                                <span class="fw-bold">QRIS Manual</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bagian Input Pembayaran Tunai (Cash) -->
                    <div id="sectionCash" style="{{ old('metode', 'cash') === 'cash' ? 'display: block;' : 'display: none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Uang Diterima dari Pelanggan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="jumlah_diterima" id="inputJumlahDiterima" class="form-control fw-bold fs-4 text-end @error('jumlah_diterima') is-invalid @enderror" placeholder="0" min="{{ (int)$transaction->total }}" step="1000" value="{{ old('jumlah_diterima', (int)$transaction->total) }}">
                            </div>
                            @error('jumlah_diterima')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tombol Cepat Nominal Uang -->
                        <div class="mb-4">
                            <label class="form-label small text-muted mb-1">Nominal Cepat:</label>
                            <div class="d-flex flex-wrap gap-2" id="quickAmountButtons">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-amount="{{ (int)$transaction->total }}">
                                    Uang Pas (Rp {{ number_format($transaction->total, 0, ',', '.') }})
                                </button>
                                @php
                                    $quickAdds = [10000, 20000, 50000, 100000];
                                    $ceilAmounts = [50000, 100000, 200000, 500000];
                                @endphp
                                @foreach ($ceilAmounts as $ca)
                                    @if ($ca > $transaction->total)
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-amount="{{ $ca }}">
                                            Rp {{ number_format($ca, 0, ',', '.') }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Kotak Informasi Kembalian -->
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted fw-semibold">Uang Kembalian:</div>
                                    <div class="small text-secondary" id="labelChangeStatus">Sisa uang yang harus dikembalikan</div>
                                </div>
                                <div class="fs-4 fw-bold text-success" id="labelKembalian">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Konfirmasi QRIS Manual -->
                    <div id="sectionQris" style="{{ old('metode') === 'qris' ? 'display: block;' : 'display: none;' }}" class="mb-4">
                        <div class="alert alert-info p-3 mb-0 rounded-3">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Instruksi QRIS Manual</h6>
                                    <p class="small mb-1">Tunjukkan barcode QRIS bengkel ke pelanggan dengan total tagihan <strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong>.</p>
                                    <p class="small mb-0 text-muted">Pastikan Anda telah memverifikasi mutasi/notifikasi masuk pada aplikasi merchant sebelum menyelesaikan pembayaran ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Catatan Pembayaran (Opsional)</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Contoh: Dibayar oleh istri pelanggan, QRIS BCA, dll." value="{{ old('catatan') }}">
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm" id="btnSubmitPayment">
                            <i class="bi bi-check-circle-fill me-1"></i> Selesaikan Pembayaran
                        </button>
                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">
                            Batalkan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalTagihan = {{ (int) $transaction->total }};
    const radioCash = document.getElementById('metodeCash');
    const radioQris = document.getElementById('metodeQris');
    const sectionCash = document.getElementById('sectionCash');
    const sectionQris = document.getElementById('sectionQris');
    const inputJumlahDiterima = document.getElementById('inputJumlahDiterima');
    const labelKembalian = document.getElementById('labelKembalian');
    const labelChangeStatus = document.getElementById('labelChangeStatus');
    const btnSubmit = document.getElementById('btnSubmitPayment');

    function updateMetodeDisplay() {
        if (radioCash.checked) {
            sectionCash.style.display = 'block';
            sectionQris.style.display = 'none';
            inputJumlahDiterima.required = true;
            calculateChange();
        } else {
            sectionCash.style.display = 'none';
            sectionQris.style.display = 'block';
            inputJumlahDiterima.required = false;
            btnSubmit.disabled = false;
        }
    }

    function calculateChange() {
        const received = parseFloat(inputJumlahDiterima.value) || 0;
        const change = received - totalTagihan;

        if (change < 0) {
            labelKembalian.className = 'fs-4 fw-bold text-danger';
            labelKembalian.textContent = '- Rp ' + Math.abs(change).toLocaleString('id-ID');
            labelChangeStatus.textContent = 'Uang yang diterima masih kurang!';
            btnSubmit.disabled = true;
        } else {
            labelKembalian.className = 'fs-4 fw-bold text-success';
            labelKembalian.textContent = 'Rp ' + change.toLocaleString('id-ID');
            labelChangeStatus.textContent = change === 0 ? 'Uang pas (tanpa kembalian)' : 'Sisa uang yang harus dikembalikan';
            btnSubmit.disabled = false;
        }
    }

    radioCash.addEventListener('change', updateMetodeDisplay);
    radioQris.addEventListener('change', updateMetodeDisplay);
    inputJumlahDiterima.addEventListener('input', calculateChange);

    // Quick cash buttons
    document.querySelectorAll('.btn-quick-cash').forEach(button => {
        button.addEventListener('click', function () {
            const amount = this.getAttribute('data-amount');
            inputJumlahDiterima.value = amount;
            calculateChange();
        });
    });

    updateMetodeDisplay();
});
</script>
@endpush

