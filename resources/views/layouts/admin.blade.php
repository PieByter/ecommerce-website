<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — Sparepart Motor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin-layout.css') }}" rel="stylesheet">
    @vite(['resources/js/app.js'])
    @livewireStyles

    <style>
        @yield('styles')
    </style>
</head>

<body>

    {{-- ═══ SIDEBAR ═════════════════════════════════════════════════════════ --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside id="sidebar">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-gear-fill brand-icon"></i>
            <div>
                Sparepart Motor
                <small>Admin Panel</small>
            </div>
        </a>

        {{-- Main Menu --}}
        <div class="sidebar-section-label">Menu Utama</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
        </ul>

        {{-- Master Data --}}
        <div class="sidebar-section-label">Master Data</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}"
                    class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i> Kategori
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.suppliers.index') }}"
                    class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Supplier
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}"
                    class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Produk
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.customers.index') }}"
                    class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Customer
                </a>
            </li>
        </ul>

        {{-- Transaksi --}}
        <div class="sidebar-section-label">Transaksi</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}"
                    class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.purchase-orders.index') }}"
                    class="nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Purchase Order
                </a>
            </li>
        </ul>

        {{-- Pengaturan --}}
        <div class="sidebar-section-label">Pengaturan</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}"
                    class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Data Admin
                </a>
            </li>
        </ul>

        {{-- Sidebar Footer / Logout --}}
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div class="text-white small fw-600">{{ auth()->user()->name }}</div>
                    <div style="color:rgba(255,255,255,.4);font-size:.7rem;">Administrator</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 text-white border-secondary">
                    <i class="bi bi-box-arrow-left me-1"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══ MAIN WRAPPER ════════════════════════════════════════════════════ --}}
    <div id="main-wrapper">

        {{-- ─── Topbar ─── --}}
        <div id="topbar">
            {{-- Mobile Toggle --}}
            <button class="btn btn-sm btn-light me-3 d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>

            {{-- Breadcrumb / Page Title --}}
            <h6 class="topbar-title">@yield('page_title', 'Dashboard')</h6>

            {{-- Topbar Right --}}
            <div class="topbar-right">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                    class="topbar-notification-link topbar-notification-orders" title="Pesanan baru"
                    aria-label="Pesanan baru">
                    <i class="bi bi-cart3"></i>
                    @if (($adminNotifications['newOrdersCount'] ?? 0) > 0)
                        <span class="topbar-notification-badge">{{ $adminNotifications['newOrdersCount'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.products.index', ['stock' => 'empty']) }}"
                    class="topbar-notification-link topbar-notification-stock" title="Produk stok kosong"
                    aria-label="Produk stok kosong">
                    <i class="bi bi-bell"></i>
                    @if (($adminNotifications['outOfStockCount'] ?? 0) > 0)
                        <span class="topbar-notification-badge">{{ $adminNotifications['outOfStockCount'] }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- ─── Flash Messages ─── --}}
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
        </div>

        {{-- ─── Page Content ─── --}}
        <div id="content">
            @yield('content')
        </div>

    </div>{{-- end #main-wrapper --}}

    {{-- Bootstrap 5 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScriptConfig

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        // Auto-dismiss flash messages
        setTimeout(() => {
            document.querySelectorAll('.flash-container .alert').forEach(el => {
                bootstrap.Alert.getOrCreateInstance(el).close();
            });
        }, 4000);
    </script>

    @yield('scripts')
</body>

</html>
