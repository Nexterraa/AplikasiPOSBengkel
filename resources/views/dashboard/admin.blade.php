@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="fw-bold mb-1">Dashboard Admin</h3>
                <p class="text-muted mb-0">Selamat datang, <strong>{{ Auth::user()->name }}</strong> (Hak Akses: Administrator)</p>
            </div>
            <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase">Role: Admin</span>
        </div>
        <hr>
        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Tahap 2 Berhasil Diselesaikan!</strong><br>
                Sistem autentikasi, role Admin & Kasir, middleware hak akses, dan layout utama responsif telah siap.
            </div>
        </div>
    </div>
</div>
@endsection

