<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Toko sparepart motor terlengkap, harga terbaik, pengiriman cepat ke seluruh Indonesia.')">
    <title>@yield('title', 'Toko Sparepart Motor')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app-layout.css') }}" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <style>
        /* ─── Page-specific overrides ─── */
        @yield('styles')
    </style>
</head>

<body>

    {{-- ═══ MAIN NAVBAR ═════════════════════════════════════════════════════ --}}
    <nav class="navbar navbar-main navbar-expand-lg sticky-top">
        <div class="container">
            {{-- Logo --}}
            <a class="navbar-brand fw-700 fs-5" href="{{ route('home') }}">
                <i class="bi bi-gear-fill text-danger me-1"></i>
                <span>Sparepart</span>Motor
            </a>

            {{-- Mobile Toggle --}}
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarMain">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                {{-- Search Bar (center) --}}
                <form class="mx-auto search-bar d-flex" style="width:100%;max-width:420px;"
                    action="{{ route('products.index') }}" method="GET">
                    <input class="form-control" type="search" name="q" placeholder="Cari sparepart motor..."
                        value="{{ request('q') }}">
                    <button class="btn-search" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                {{-- Nav Links --}}
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                            href="{{ route('products.index') }}">Produk</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}"
                                href="{{ route('customer.orders.index') }}">Pesanan Saya</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.reviews.*') ? 'active' : '' }}"
                                href="{{ route('customer.reviews.index') }}">Review Saya</a>
                        </li>
                    @endauth

                    {{-- Cart Button --}}
                    <li class="nav-item">
                        <a href="{{ route('cart.index') }}" class="btn-cart position-relative ms-2">
                            <i class="bi bi-cart3 text-danger"></i>
                            @auth
                                @php $cartCount = (int) auth()->user()->carts()->sum('quantity'); @endphp
                                @if ($cartCount > 0)
                                    <span class="badge rounded-pill cart-badge position-absolute">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            @endauth
                        </a>
                    </li>

                    @auth
                        <li class="nav-item d-lg-none">
                            <a class="nav-link" href="{{ route('customer.orders.index') }}">
                                <i class="bi bi-box-seam me-1"></i>Pesanan Saya
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="Menu profil pengguna">
                                <i class="bi bi-person-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                aria-labelledby="userMenuDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person-gear me-2"></i>Edit Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.reviews.index') }}">
                                        <i class="bi bi-star me-2"></i>Review Saya
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Daftar
                            </a>
                        </li>
                    @endguest


                </ul>
            </div>
        </div>
    </nav>

    {{-- ═══ FLASH MESSAGES ══════════════════════════════════════════════════ --}}
    <div class="flash-container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible shadow fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible shadow fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible shadow fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- ═══ MAIN CONTENT ════════════════════════════════════════════════════ --}}
    <main class="py-4">
        @yield('content')
    </main>

    {{-- ═══ FOOTER ══════════════════════════════════════════════════════════ --}}
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h6><i class="bi bi-gear-fill text-danger me-2"></i>Sparepart Motor</h6>
                    <p class="small">
                        Toko sparepart motor terpercaya. Tersedia ribuan
                        suku cadang original & KW super untuk semua merek motor.
                    </p>
                </div>
                <div class="col-md-2">
                    <h6>Produk</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#">Mesin</a></li>
                        <li><a href="#">Rem &amp; Kopling</a></li>
                        <li><a href="#">Kelistrikan</a></li>
                        <li><a href="#">Aksesoris</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Layanan</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('customer.orders.index') }}">Cek Pesanan</a></li>
                        <li><a href="#">Cara Pembayaran</a></li>
                        <li><a href="#">Kebijakan Retur</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Kontak</h6>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-telephone me-2"></i>0812-3456-7890</li>
                        <li><i class="bi bi-clock me-2"></i>Senin–Sabtu 08.00–17.00 WIB</li>
                        <li><i class="bi bi-envelope me-2"></i>info@sparepart-motor.id</li>
                        <li><i class="bi bi-geo-alt me-2"></i>Medan, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center text-white-50">
                &copy; {{ date('Y') }} Toko Sparepart Motor. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Bootstrap 5 JS Bundle (Popper included) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Auto-dismiss flash messages after 4s --}}
    <script>
        setTimeout(() => {
            document.querySelectorAll('.flash-container .alert').forEach(el => {
                bootstrap.Alert.getOrCreateInstance(el).close();
            });
        }, 4000);
    </script>

    @yield('scripts')
</body>

</html>
