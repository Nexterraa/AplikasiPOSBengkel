<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - POS Bengkel Motor</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 250px; }
        @media (max-width: 767.98px) {
            .sidebar { width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Navbar Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard') }}">
                <i class="bi bi-wrench-adjustable text-warning"></i> POS Bengkel Motor
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('*.dashboard') ? 'active fw-bold' : '' }}" href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3 text-white">
                    <span class="badge bg-{{ Auth::user()->isAdmin() ? 'danger' : 'primary' }} px-2 py-1 text-uppercase">
                        <i class="bi bi-shield-lock me-1"></i> {{ Auth::user()->role }}
                    </span>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-gear me-2"></i> Ubah Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container Layout -->
    <div class="container-fluid my-4">
        <div class="row">
            <!-- Sidebar Nav -->
            <div class="col-md-3 col-lg-2 mb-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-bold border-bottom">
                        <i class="bi bi-list-task me-1"></i> Menu {{ ucfirst(Auth::user()->role) }}
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('kasir.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                        @if (Auth::user()->isAdmin())
                            <div class="list-group-item bg-light text-muted small fw-bold text-uppercase">Data Master</div>
                            <a href="{{ route('admin.mekanik.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.mekanik.*') ? 'active' : '' }}">
                                <i class="bi bi-person-workspace me-2"></i> Data Mekanik
                            </a>
                            <a href="{{ route('admin.kategori.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
                                <i class="bi bi-tags me-2"></i> Kategori Sparepart
                            </a>
                            <a href="{{ route('admin.jasa.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.jasa.*') ? 'active' : '' }}">
                                <i class="bi bi-tools me-2"></i> Jasa Servis
                            </a>
                            <a href="{{ route('admin.spareparts.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.spareparts.*') ? 'active' : '' }}">
                                <i class="bi bi-box-seam me-2"></i> Sparepart & Produk
                            </a>
                            <a href="{{ route('admin.kasir.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.kasir.*') ? 'active' : '' }}">
                                <i class="bi bi-people me-2"></i> Akun Kasir
                            </a>
                            <div class="list-group-item bg-light text-muted small fw-bold text-uppercase">Manajemen Stok</div>
                            <a href="{{ route('admin.stok.create') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.stok.create') ? 'active' : '' }}">
                                <i class="bi bi-box-arrow-in-down me-2"></i> Stok Masuk
                            </a>
                            <a href="{{ route('admin.stok.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.stok.index') ? 'active' : '' }}">
                                <i class="bi bi-clock-history me-2"></i> Riwayat Stok
                            </a>
                        @endif

                        <div class="list-group-item bg-light text-muted small fw-bold text-uppercase">Transaksi POS</div>
                        <span class="list-group-item text-muted disabled"><i class="bi bi-lock me-2"></i> Transaksi Baru</span>
                        <span class="list-group-item text-muted disabled"><i class="bi bi-lock me-2"></i> Riwayat Transaksi</span>

                        <div class="list-group-item bg-light text-muted small fw-bold text-uppercase">Pengaturan</div>
                        <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                            <i class="bi bi-person-gear me-2"></i> Ubah Profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9 col-lg-10">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-white border-top text-center text-muted small">
        <div class="container">
            &copy; {{ date('Y') }} POS Bengkel Motor — Aplikasi Kasir Bengkel Motor
        </div>
    </footer>
    @stack('scripts')
</body>
</html>

