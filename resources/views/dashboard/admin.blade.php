@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Admin</h3>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase shadow-sm">
        <i class="bi bi-shield-lock me-1"></i> Administrator
    </span>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Total Omzet -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Omzet Lunas</span>
                        <h4 class="fw-bold text-success my-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Hanya Transaksi LUNAS</span>
                    </div>
                    <div class="bg-success text-white rounded-3 p-3" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Lunas -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Transaksi Lunas</span>
                        <h4 class="fw-bold text-primary my-1">{{ number_format($totalTransaksiLunas) }}</h4>
                        <span class="text-muted small">Total transaksi selesai</span>
                    </div>
                    <div class="bg-primary text-white rounded-3 p-3" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Belum Dibayar -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Belum Dibayar</span>
                        <h4 class="fw-bold text-warning my-1">{{ number_format($totalTransaksiBelumBayar) }}</h4>
                        <span class="text-muted small">Dalam antrean/proses</span>
                    </div>
                    <div class="bg-warning text-dark rounded-3 p-3" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Menipis/Habis -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Stok Menipis/Habis</span>
                        <h4 class="fw-bold text-danger my-1">{{ number_format($stokMenipisCount) }}</h4>
                        <a href="{{ route('admin.stok.index') }}" class="small text-danger text-decoration-none">Lihat daftar &rarr;</a>
                    </div>
                    <div class="bg-danger text-white rounded-3 p-3" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-box-seam-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik + Stok Menipis -->
<div class="row g-4 mb-4">
    <!-- Grafik Penjualan 7 Hari Terakhir -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Omzet 7 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <canvas id="grafikPenjualan" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Daftar Stok Menipis / Habis -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Stok Perlu Perhatian</h5>
                @if ($stokMenipisCount > 0)
                    <span class="badge bg-danger">{{ $stokMenipisCount }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse ($sparepartMenipis as $sp)
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $sp->nama_sparepart }}</div>
                            <div class="text-muted" style="font-size:0.75rem">Min: {{ $sp->stok_minimum }}</div>
                        </div>
                        @if ($sp->stok == 0)
                            <span class="badge bg-danger">HABIS</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $sp->stok }} unit</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                        Semua stok dalam kondisi aman.
                    </div>
                @endforelse
            </div>
            @if ($stokMenipisCount > 0)
                <div class="card-footer bg-white border-0 text-center">
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Tambah Stok
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Transaksi Terbaru -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Transaksi Terbaru</h5>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksiTerbaru as $tx)
                        <tr>
                            <td class="ps-3 fw-semibold text-primary">
                                <a href="{{ route('admin.transactions.show', $tx) }}" class="text-decoration-none">
                                    {{ $tx->no_transaksi }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $tx->customer->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary font-monospace">{{ $tx->vehicle->plate_number ?? '-' }}</span>
                            </td>
                            <td class="text-end fw-bold">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($tx->isBelumDibayar())
                                    <span class="badge bg-warning text-dark">BELUM DIBAYAR</span>
                                @elseif ($tx->isLunas())
                                    <span class="badge bg-success">LUNAS</span>
                                @elseif ($tx->isDibatalkan())
                                    <span class="badge bg-danger">DIBATALKAN</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Chart.js sudah ada di CDN atau bisa include dari layout --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Data grafik dari PHP/Blade — dikirim ke JavaScript
const grafikLabels = @json(array_column($grafikData, 'label'));
const grafikTotals = @json(array_column($grafikData, 'total'));

// Buat grafik batang omzet 7 hari terakhir
const ctx = document.getElementById('grafikPenjualan').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: grafikLabels,
        datasets: [{
            label: 'Omzet (Rp)',
            data: grafikTotals,
            backgroundColor: 'rgba(13, 110, 253, 0.6)',  // warna biru Bootstrap
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    // Format angka rupiah di tooltip
                    label: function(ctx) {
                        return 'Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        // Singkat angka besar: 1.000.000 → 1Jt
                        if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                        if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'Rb';
                        return 'Rp ' + value;
                    }
                }
            }
        }
    }
});
</script>
@endpush
