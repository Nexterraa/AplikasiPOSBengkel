@extends('layouts.app')

@section('title', 'Dashboard Kasir')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="fw-bold mb-1">Dashboard Kasir</h3>
                <p class="text-muted mb-0">Selamat datang, <strong>{{ Auth::user()->name }}</strong> (Hak Akses: Kasir)</p>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2 text-uppercase">Role: Kasir</span>
        </div>
        <hr>
        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Tahap 2 Berhasil Diselesaikan!</strong><br>
                Sistem autentikasi Kasir dan hak akses area Kasir telah siap.
            </div>
        </div>
    </div>
</div>
@endsection

