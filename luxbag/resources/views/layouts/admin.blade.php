<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ setting('site_name', 'Bloom & Bold') }} Admin</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap">

    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ time() }}">

    @yield('head')
</head>
<body>

{{-- ═══ SIDEBAR ══════════════════════════════════════════════════ --}}
<aside class="admin-sidebar" id="adminSidebar">
    {{-- Brand --}}
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <div>
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name', 'Bloom & Bold') }}" style="height:36px;max-width:160px;object-fit:contain;">
            @else
                <div class="logo-text">{{ setting('site_name', 'Bloom & Bold') }}</div>
            @endif
            <span class="admin-badge">Admin Panel</span>
        </div>
    </a>

    {{-- User info --}}
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--sidebar-border); display:flex; align-items:center; gap:12px;">
        <img src="{{ auth()->user()->avatar_url }}" alt="" style="width:36px;height:36px;border-radius:50%;border:2px solid var(--gold);">
        <div>
            <div style="font-size:13px;font-weight:600;color:#fff;line-height:1.2;">{{ auth()->user()->name }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ auth()->user()->getRoleNames()->first() }}</div>
        </div>
    </div>

    {{-- Navigation --}}
    <ul class="sidebar-nav">
        <li class="sidebar-section-label">Tổng Quan</li>
        <li>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-chart-pie"></i></span>
                Dashboard
            </a>
        </li>

        <li class="sidebar-section-label">Cửa Hàng</li>
        <li>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-box"></i></span>
                Sản Phẩm
            </a>
        </li>
        <li>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-tags"></i></span>
                Danh Mục
            </a>
        </li>
        <li>
            <a href="{{ route('admin.brands.index') }}" class="sidebar-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-certificate"></i></span>
                Thương Hiệu
            </a>
        </li>

        <li class="sidebar-section-label">Bán Hàng</li>
        <li>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                Đơn Hàng
                @php $pendingOrders = App\Models\Order::where('status','pending')->count(); @endphp
                @if($pendingOrders > 0)
                <span class="badge">{{ $pendingOrders }}</span>
                @endif
            </a>
        </li>
        @can('manage-customers')
        <li>
            <a href="{{ route('admin.customers.index') }}" class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-users"></i></span>
                Khách Hàng
            </a>
        </li>
        @endcan
        <li>
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-star"></i></span>
                Đánh Giá
            </a>
        </li>

        @can('manage-coupons')
        <li class="sidebar-section-label">Khuyến Mãi</li>
        <li>
            <a href="{{ route('admin.coupons.index') }}" class="sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-ticket-alt"></i></span>
                Mã Giảm Giá
            </a>
        </li>
        <li>
            <a href="{{ route('admin.flash-sales.index') }}" class="sidebar-link {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-bolt"></i></span>
                Flash Sale
            </a>
        </li>
        <li>
            <a href="{{ route('admin.banners.index') }}" class="sidebar-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-image"></i></span>
                Banners
            </a>
        </li>
        @endcan

        @can('manage-reports')
        <li class="sidebar-section-label">Phân Tích</li>
        <li>
            <a href="{{ route('admin.reports.revenue') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                Báo Cáo
            </a>
        </li>
        @endcan

        <li class="sidebar-section-label">Hệ Thống</li>
        @can('manage-staff')
        <li>
            <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-users-cog"></i></span>
                Nhân Viên
            </a>
        </li>
        @endcan
        @can('manage-settings')
        <li>
            <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <span class="icon"><i class="fas fa-cog"></i></span>
                Cài Đặt
            </a>
        </li>
        @endcan
        <li>
            <a href="{{ route('home') }}" class="sidebar-link" target="_blank">
                <span class="icon"><i class="fas fa-external-link-alt"></i></span>
                Xem Website
            </a>
        </li>
        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-link w-100 text-danger" style="background:none;border:none;cursor:pointer;">
                    <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                    Đăng Xuất
                </button>
            </form>
        </li>
    </ul>
</aside>

{{-- ═══ TOPBAR ════════════════════════════════════════════════════ --}}
<header class="admin-topbar">
    {{-- Mobile toggle --}}
    <button class="d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')"
            style="background:none;border:1px solid var(--border);border-radius:6px;padding:6px 10px;color:var(--text)">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Page title --}}
    <div class="flex-1">
        <h6 class="mb-0 fw-600" style="font-size:15px;">@yield('page-title', 'Dashboard')</h6>
        <div class="admin-breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="separator">/</span>
            @yield('breadcrumb', 'Dashboard')
        </div>
    </div>

    <div class="ms-auto d-flex align-items-center gap-3">
        {{-- Notifications --}}
        <div class="position-relative">
            <button style="background:none;border:1px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);cursor:pointer;font-size:14px;">
                <i class="fas fa-bell"></i>
                @php $pendingCount = App\Models\Order::where('status','pending')->count(); @endphp
                @if($pendingCount > 0)
                <span style="position:absolute;top:-4px;right:-4px;background:var(--red);color:#fff;font-size:10px;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
                    {{ $pendingCount }}
                </span>
                @endif
            </button>
        </div>

        {{-- User --}}
        <div class="dropdown">
            <button style="background:none;border:none;display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;"
                    data-bs-toggle="dropdown">
                <img src="{{ auth()->user()->avatar_url }}" alt="" style="width:34px;height:34px;border-radius:50%;border:2px solid var(--gold);">
                <span class="d-none d-md-inline fw-600">{{ auth()->user()->name }}</span>
                <i class="fas fa-chevron-down" style="font-size:10px;color:var(--text-muted);"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.1);border:1px solid var(--border);padding:8px;">
                <li><a class="dropdown-item" href="{{ route('profile.index') }}" style="font-size:13px;border-radius:6px;"><i class="fas fa-user me-2"></i>Tài khoản</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger" style="font-size:13px;border-radius:6px;">
                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ═══ MAIN CONTENT ══════════════════════════════════════════════ --}}
<main class="admin-content">
    {{-- Flash Messages --}}
    @foreach(['success' => 'check-circle text-success', 'error' => 'exclamation-circle text-danger', 'warning' => 'exclamation-triangle text-warning', 'info' => 'info-circle text-info'] as $type => $icon)
        @if(session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} d-flex align-items-center gap-2 mb-4 animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-{{ $icon }}"></i>
            {{ session($type) }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endforeach

    @yield('content')
</main>

{{-- Bootstrap + jQuery --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Confirm delete
$(document).on('click', '[data-confirm]', function(e) {
    if (!confirm($(this).data('confirm') || 'Bạn có chắc chắn không?')) {
        e.preventDefault();
    }
});

// Toggle switch AJAX
$(document).on('change', '[data-ajax-toggle]', function() {
    const el = $(this);
    const url = el.data('ajax-toggle');
    $.ajax({ url, method: 'PATCH' })
    .done(res => {
        if (!res.success) el.prop('checked', !el.prop('checked'));
    })
    .fail(() => {
        el.prop('checked', !el.prop('checked'));
        alert('Cập nhật thất bại!');
    });
});
</script>

@yield('scripts')
</body>
</html>

