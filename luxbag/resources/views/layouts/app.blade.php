<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', setting('site_tagline', 'Bloom & Bold - Túi Xách Cao Cấp, Phong Cách Đẳng Cấp. Mua sắm túi xách da thật, ba lô, ví da chính hãng.'))">
    <meta name="keywords" content="@yield('meta_keywords', 'túi xách, túi xách nữ, túi xách nam, ba lô, ví da, túi công sở, Bloom & Bold')">
    <meta property="og:title" content="@yield('title', setting('site_name', 'Bloom & Bold')) - {{ setting('site_tagline') }}">
    <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">
    <meta property="og:type" content="website">
    <title>@yield('title', setting('site_name', 'Bloom & Bold')) - {{ setting('site_tagline', 'Túi Xách Cao Cấp') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

    {{-- Google Fonts Preconnect --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">

    @yield('head')
</head>
<body>

{{-- Page Loader --}}
<div class="page-loader" id="pageLoader">
    <div class="spinner-gold"></div>
</div>

{{-- Top Bar --}}
<div class="topbar d-none d-md-block">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span><i class="fas fa-phone me-1"></i> {{ setting('site_phone', '+84 377277542') }}</span>
                <span class="mx-3">|</span>
                <span><i class="fas fa-envelope me-1"></i> {{ setting('site_email', 'contact@bloomandbold.vn') }}</span>
            </div>
            <div class="col-md-6 text-end">
                <span class="me-3">
                    <i class="fas fa-shipping-fast me-1 text-gold"></i>
                    Miễn phí vận chuyển đơn từ {{ vnd(setting('free_shipping_threshold', 500000)) }}
                </span>
                @guest
                    <a href="{{ route('login') }}" class="me-2">Đăng nhập</a>
                    <a href="{{ route('register') }}">Đăng ký</a>
                @endguest
                @auth
                    <span>Xin chào, <strong>{{ auth()->user()->name }}</strong></span>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- Main Navbar --}}
<nav class="navbar navbar-luxbag navbar-expand-lg" id="mainNav">
    <div class="container">
        {{-- Brand --}}
        <a class="navbar-brand" href="{{ route('home') }}">
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name', 'Bold&amp;Bloom') }}" class="navbar-logo-img">
            @else
                @php $siteName = setting('site_name', 'Bold&Bloom'); @endphp
                @if(str_contains($siteName, '&'))
                    {!! str_replace('&', '<span style="color:var(--gold)">&amp;</span>', e($siteName)) !!}
                @else
                    {{ $siteName }}
                @endif
            @endif
        </a>

        {{-- Mobile Toggle --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Nav --}}
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang Chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') ? 'active' : '' }}"
                       href="{{ route('products.index') }}" id="productsDropdown" role="button"
                       data-bs-toggle="dropdown"
                       onclick="if(window.innerWidth >= 992) { window.location.href = this.href; return false; }">
                        Sản Phẩm
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                        <li><a class="dropdown-item" href="{{ route('products.index') }}"><i class="fas fa-th me-2"></i>Tất cả sản phẩm</a></li>
                        <li><hr class="dropdown-divider" style="border-color:rgba(138,111,214,.18)"></li>
                        @php
                            $navCategories = App\Models\Category::active()->parents()->orderBy('sort_order')->take(6)->get();
                        @endphp
                        @foreach($navCategories as $cat)
                            <li>
                                <a class="dropdown-item" href="{{ route('products.category', $cat->slug) }}">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('products.index') }}?flash=1">Flash Sale 🔥</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.about') ? 'active' : '' }}" href="{{ route('pages.about') }}">Giới Thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.contact') ? 'active' : '' }}" href="{{ route('pages.contact') }}">Liên Hệ</a>
                </li>
            </ul>

            {{-- Search --}}
            <form class="search-bar d-none d-lg-block me-3" action="{{ route('products.search') }}" method="GET">
                <input type="text" name="q" placeholder="Tìm kiếm sản phẩm..." value="{{ request('q') }}" autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            {{-- Icons --}}
            <div class="d-flex align-items-center gap-1">
                {{-- Wishlist --}}
                @auth
                <a href="{{ route('wishlist.index') }}" class="navbar-icon-btn position-relative" title="Yêu thích">
                    <i class="fas fa-heart"></i>
                    <span class="cart-badge" id="wishlistCount" style="{{ wishlist_count() == 0 ? 'display:none' : '' }}">
                        {{ wishlist_count() }}
                    </span>
                </a>
                @endauth

                {{-- Cart --}}
                <a href="{{ route('cart.index') }}" class="navbar-icon-btn position-relative" title="Giỏ hàng">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge" id="cartCount" style="{{ cart_count() == 0 ? 'display:none' : '' }}">
                        {{ cart_count() }}
                    </span>
                </a>

                {{-- User --}}
                @guest
                <a href="{{ route('login') }}" class="navbar-icon-btn" title="Đăng nhập">
                    <i class="fas fa-user"></i>
                </a>
                @endguest
                @auth
                <div class="dropdown">
                    <button class="navbar-icon-btn dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" alt="Avatar"
                             style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--gold);">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user-circle me-2"></i>Tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('orders.history') }}">
                                <i class="fas fa-box me-2"></i>Đơn hàng của tôi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('wishlist.index') }}">
                                <i class="fas fa-heart me-2"></i>Yêu thích
                            </a>
                        </li>
                        @if(auth()->user()->isStaff())
                        <li><hr class="dropdown-divider" style="border-color:rgba(138,111,214,.18)"></li>
                        <li>
                            <a class="dropdown-item text-gold" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-cog me-2"></i>Quản trị
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider" style="border-color:rgba(138,111,214,.18)"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Flash Messages --}}
@if(session('success') || session('error') || session('info') || session('warning'))
<div class="toast-container position-fixed top-0 end-0 p-3">
    @foreach(['success' => 'check-circle', 'error' => 'exclamation-circle', 'info' => 'info-circle', 'warning' => 'exclamation-triangle'] as $type => $icon)
        @if(session($type))
        <div class="toast toast-luxbag {{ $type }} align-items-center show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-{{ $icon }} me-2" style="color: var(--{{ $type === 'error' ? 'red' : ($type === 'success' ? 'green' : ($type === 'warning' ? 'orange' : 'blue')) }})"></i>
                    {{ session($type) }}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
    @endforeach
</div>
@endif

{{-- Main Content --}}
<main id="main-content">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                @if(setting('site_logo_footer') || setting('site_logo'))
                    <a href="{{ route('home') }}" class="footer-brand-link">
                        <img src="{{ asset('storage/' . (setting('site_logo_footer') ?: setting('site_logo'))) }}" alt="{{ setting('site_name', 'Bloom & Bold') }}" class="footer-logo-img">
                    </a>
                @else
                    <span class="footer-brand">{{ setting('site_name', 'Bloom & Bold') }}</span>
                @endif
                <p style="font-size:14px;line-height:1.8;color:rgba(255,255,255,.6);">
                    Chúng tôi cung cấp các sản phẩm túi xách cao cấp với chất liệu thượng hạng và thiết kế tinh tế, mang đến phong cách đẳng cấp cho mọi người.
                </p>
                <div class="footer-social">
                    <a href="{{ setting('facebook_url', '#') }}" class="social-btn" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="{{ setting('instagram_url', '#') }}" class="social-btn" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-btn" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="social-btn" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Sản Phẩm</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('products.index') }}">Tất cả sản phẩm</a></li>
                    <li><a href="{{ route('products.index') }}?is_featured=1">Sản phẩm nổi bật</a></li>
                    <li><a href="{{ route('products.index') }}?is_new=1">Hàng mới về</a></li>
                    <li><a href="{{ route('products.index') }}?flash=1">Flash Sale</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Hỗ Trợ</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('pages.faq') }}">Câu hỏi thường gặp</a></li>
                    <li><a href="{{ route('pages.policy') }}">Chính sách đổi trả</a></li>
                    <li><a href="{{ route('pages.contact') }}">Liên hệ</a></li>
                    <li><a href="{{ route('pages.about') }}">Về chúng tôi</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="footer-title">Đăng Ký Nhận Ưu Đãi</h6>
                <p style="font-size:13px;color:rgba(255,255,255,.6);margin-bottom:16px;">
                    Nhận thông báo về khuyến mãi và sản phẩm mới nhất.
                </p>
                <form class="d-flex footer-newsletter" action="#" method="POST">
                    @csrf
                    <input type="email" placeholder="Email của bạn..." required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <div class="mt-4">
                    <p style="font-size:13px;color:rgba(255,255,255,.6);">
                        <i class="fas fa-map-marker-alt text-gold me-2"></i>
                        {{ setting('site_address', '123 Đường Lê Lợi, Q.1, TP.HCM') }}
                    </p>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);">
                        <i class="fas fa-phone text-gold me-2"></i>
                        {{ setting('site_phone', '+84 377277542') }}
                    </p>
                    <p style="font-size:13px;color:rgba(255,255,255,.6);">
                        <i class="fas fa-clock text-gold me-2"></i>
                        Thứ 2 - Thứ 7: 8:00 - 21:00
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    &copy; {{ date('Y') }} <strong style="color:var(--gold)">{{ setting('site_name', 'Bloom & Bold') }}</strong>. Tất cả quyền được bảo lưu.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="{{ asset('assets/images/payment-methods.png') }}" alt="Thanh toán" height="24" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</footer>

{{-- Back to Top --}}
<a href="#" class="back-to-top" id="backToTop" title="Lên đầu trang">
    <i class="fas fa-chevron-up"></i>
</a>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
{{-- Main JS --}}
<script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>

@yield('scripts')

<script>
// Initialize toasts
document.querySelectorAll('.toast').forEach(el => {
    const t = new bootstrap.Toast(el, {delay: 4000});
    t.show();
});
</script>
{{-- Chatbot AI Tư Vấn --}}
@include('partials.chatbot')

</body>
</html>
