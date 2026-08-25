@extends('layouts.app')

@section('title', 'Trang Chủ')
@section('meta_description', 'Bloom & Bold - Thiên đường túi xách cao cấp. Khám phá bộ sưu tập túi da thật, ba lô, ví da chính hãng với giá ưu đãi.')

@section('content')

{{-- ═══ HERO CAROUSEL (Slide ngang) ════════════════════════════════════ --}}
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-touch="true">

        <div class="carousel-inner">
            @if($banners->isNotEmpty())
                @foreach($banners as $i => $banner)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    <div class="hero-slide" style="background-image: url('{{ $banner->image_url }}')">
                        <div class="container">
                            <div class="hero-content animate-fade-up">
                                <span class="eyebrow">{{ $banner->subtitle ?? 'Bộ Sưu Tập Mới' }}</span>
                                <h1>{{ $banner->title ?? 'Túi Xách Cao Cấp' }}</h1>
                                <p>Khám phá thế giới túi xách đẳng cấp — nơi phong cách gặp gỡ sự hoàn hảo.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="{{ $banner->link ?? route('products.index') }}" class="btn-gold">
                                        <i class="fas fa-shopping-bag"></i> Khám Phá Ngay
                                    </a>
                                    <a href="{{ route('pages.about') }}" class="btn-outline-gold">
                                        Tìm Hiểu Thêm
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            {{-- Default hero if no banners --}}
            <div class="carousel-item active">
                <div class="hero-slide" style="background: linear-gradient(135deg, #2D1F47 0%, #4D337A 50%, #201435 100%); min-height: 620px;">
                    <div class="container">
                        <div class="hero-content animate-fade-up">
                            <span class="eyebrow">✦ Bộ Sưu Tập 2026</span>
                            <h1>Phong Cách <br>Không Giới Hạn</h1>
                            <p>Khám phá bộ sưu tập túi xách cao cấp từ da bò thật, được chế tác tỉ mỉ bởi những nghệ nhân lành nghề.</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="{{ route('products.index') }}" class="btn-gold">
                                    <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
                                </a>
                                <a href="{{ route('pages.about') }}" class="btn-outline-gold" style="color:#FFF;border-color:rgba(255,255,255,.5)">
                                    Về Chúng Tôi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if($banners->count() > 1)
        {{-- Nút điều hướng trái / phải --}}
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <i class="fas fa-chevron-right"></i>
        </button>

        {{-- Chấm tròn indicator --}}
        <div class="carousel-indicators">
            @foreach($banners as $i => $banner)
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}"
                    class="{{ $i === 0 ? 'active' : '' }}"></button>
            @endforeach
        </div>

        {{-- Bộ đếm slide (1/3) --}}
        <div class="hero-slide-counter">
            <span id="heroCurrentSlide">1</span> / <span>{{ $banners->count() }}</span>
        </div>

        {{-- Thanh tiến trình tự động --}}
        <div class="hero-progress-bar">
            <div class="hero-progress-fill" id="heroProgressFill"></div>
        </div>
        @endif
    </div>
</section>

{{-- ═══ STATS BAR ════════════════════════════════════════════════════ --}}
<div style="background:#F3EEFB;border-bottom:1px solid #E4D7F7;padding:22px 0;">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3 text-center">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="fas fa-shipping-fast text-gold fs-4"></i>
                    <div class="text-start">
                        <div style="font-size:13.5px;font-weight:700;color:#3C2E56;">Miễn Phí Giao Hàng</div>
                        <div style="font-size:11.5px;color:#7B6D96;">Đơn từ 500.000đ</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="fas fa-undo text-gold fs-4"></i>
                    <div class="text-start">
                        <div style="font-size:13.5px;font-weight:700;color:#3C2E56;">Đổi Trả 30 Ngày</div>
                        <div style="font-size:11.5px;color:#7B6D96;">Không điều kiện</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="fas fa-shield-alt text-gold fs-4"></i>
                    <div class="text-start">
                        <div style="font-size:13.5px;font-weight:700;color:#3C2E56;">Bảo Hành 12 Tháng</div>
                        <div style="font-size:11.5px;color:#7B6D96;">Chính hãng 100%</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="fas fa-headset text-gold fs-4"></i>
                    <div class="text-start">
                        <div style="font-size:13.5px;font-weight:700;color:#3C2E56;">Hỗ Trợ 24/7</div>
                        <div style="font-size:11.5px;color:#7B6D96;">Tận tâm & chuyên nghiệp</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ FLASH SALE ════════════════════════════════════════════════════ --}}
@if($flashSale && $flashSale->isActive())
<section class="flash-sale-section section-sm">
    <div class="container">
        <div class="d-flex align-items-center gap-4 flex-wrap mb-5">
            <div>
                <div class="d-flex align-items-center gap-3 mb-1">
                    <i class="fas fa-bolt text-gold fs-2"></i>
                    <h2 class="section-title text-white mb-0" style="color:#fff!important">{{ $flashSale->name }}</h2>
                </div>
                <p style="color:rgba(255,255,255,.6);font-size:14px;">Ưu đãi có hạn - Số lượng có hạn!</p>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="countdown-box">
                    <span class="number" id="countdown-h">00</span>
                    <span class="label">Giờ</span>
                </div>
                <span class="countdown-separator">:</span>
                <div class="countdown-box">
                    <span class="number" id="countdown-m">00</span>
                    <span class="label">Phút</span>
                </div>
                <span class="countdown-separator">:</span>
                <div class="countdown-box">
                    <span class="number" id="countdown-s">00</span>
                    <span class="label">Giây</span>
                </div>
            </div>
        </div>

        <div class="row g-4 stagger-children">
            @foreach($flashSale->products->take(4) as $product)
            <div class="col-6 col-md-3 animate-fade-up">
                @include('partials.product-card', ['product' => $product, 'flashDiscount' => $product->pivot->discount_percent])
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══ FEATURED PRODUCTS ═════════════════════════════════════════════ --}}
<section class="section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <p class="text-muted mt-2">Những chiếc túi được yêu thích nhất của mùa này</p>
            </div>
            <a href="{{ route('products.index') }}?is_featured=1" class="btn-outline-gold">
                Xem Tất Cả <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4 stagger-children">
            @foreach($featuredProducts as $product)
            <div class="col-6 col-md-4 col-lg-3 animate-on-scroll">
                @include('partials.product-card', compact('product'))
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ CATEGORIES ════════════════════════════════════════════════════ --}}
<section class="section section-dark">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title center">Khám Phá Theo Danh Mục</h2>
            <p class="text-muted mt-2">Tìm kiếm phong cách phù hợp với bạn</p>
        </div>

        <div class="row g-3 justify-content-center">
            @foreach($categories->take(6) as $i => $category)
            <div class="col-4 col-md-3 col-lg-2">
                <a href="{{ route('products.category', $category->slug) }}" class="category-card animate-on-scroll">
                    @if($category->image)
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy">
                    @else
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--dark-3),var(--dark));display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-shopping-bag text-gold" style="font-size:2rem;opacity:.5;"></i>
                        </div>
                    @endif
                    <div class="category-card-body">
                        <h5>{{ $category->name }}</h5>
                        <span>{{ $category->products_count }} sản phẩm</span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ NEW ARRIVALS ══════════════════════════════════════════════════ --}}
<section class="section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <h2 class="section-title">Hàng Mới Về</h2>
                <p class="text-muted mt-2">Cập nhật xu hướng thời trang mới nhất</p>
            </div>
            <a href="{{ route('products.index') }}?sap-xep=newest" class="btn-outline-gold">
                Xem Thêm <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4 stagger-children">
            @foreach($newProducts as $product)
            <div class="col-6 col-md-4 col-lg-3 animate-on-scroll">
                @include('partials.product-card', compact('product'))
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ BEST SELLERS ══════════════════════════════════════════════════ --}}
<section class="section section-dark">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <h2 class="section-title">Bán Chạy Nhất</h2>
                <p class="text-muted mt-2">Được hàng nghìn khách hàng tin yêu</p>
            </div>
            <a href="{{ route('products.index') }}?sap-xep=best-seller" class="btn-outline-gold">
                Xem Tất Cả <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4 stagger-children">
            @foreach($bestSellers as $product)
            <div class="col-6 col-md-4 col-lg-3 animate-on-scroll">
                @include('partials.product-card', compact('product'))
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ BRAND BANNER ══════════════════════════════════════════════════ --}}
<section style="background: linear-gradient(135deg, #2A1D42 0%, #523784 60%, #24163B 100%); padding: 80px 0; position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 70% 50%, rgba(184,163,232,.18) 0%, transparent 70%);pointer-events:none;"></div>
    <div class="container text-center position-relative">
        <h2 class="font-heading fw-bold text-white mb-3" style="font-size:clamp(1.8rem,4vw,3rem);">
            Chất Lượng Là <span class="text-gold" style="color:#C4B5FD !important;">Tất Cả</span>
        </h2>
        <p style="color:rgba(255,255,255,.8);font-size:16px;max-width:500px;margin:0 auto 32px;line-height:1.8;">
            Mỗi sản phẩm đều được làm từ nguyên liệu cao cấp nhất, kiểm tra chặt chẽ trước khi đến tay bạn.
        </p>
        <a href="{{ route('products.index') }}" class="btn-gold">
            <i class="fas fa-gem me-2"></i>Khám Phá Bộ Sưu Tập
        </a>
    </div>
</section>

{{-- ═══ TESTIMONIALS ══════════════════════════════════════════════════ --}}
<section class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title center">Khách Hàng Nói Gì?</h2>
            <p class="text-muted mt-2">Hơn 10,000 khách hàng tin tưởng Bloom & Bold</p>
        </div>

        <div class="row g-4">
            @php
            $testimonials = [
                ['name' => 'Nguyễn Thị Lan', 'role' => 'Giám đốc Marketing', 'rating' => 5, 'content' => 'Túi xách da bò chất lượng tuyệt vời! Sau 1 năm sử dụng vẫn còn rất đẹp, không bị bong tróc hay phai màu.'],
                ['name' => 'Trần Minh Khoa', 'role' => 'Doanh nhân', 'rating' => 5, 'content' => 'Ba lô laptop từ Bloom & Bold vừa đẹp vừa tiện dụng. Chống nước tốt, nhiều ngăn, rất phù hợp đi làm hàng ngày.'],
                ['name' => 'Lê Thị Hương', 'role' => 'Giáo viên', 'rating' => 5, 'content' => 'Giao hàng nhanh, đóng gói cẩn thận. Sản phẩm đúng như mô tả, thậm chí còn đẹp hơn ngoài ảnh!'],
            ];
            @endphp
            @foreach($testimonials as $t)
            <div class="col-md-4 animate-on-scroll">
                <div class="review-card h-100">
                    <div class="stars mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $t['rating'] ? '' : 'empty' }}"></i>
                        @endfor
                    </div>
                    <p class="text-muted mb-4" style="font-size:14px;line-height:1.8;">"{{ $t['content'] }}"</p>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:18px;">
                            {{ substr($t['name'], 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:14px;">{{ $t['name'] }}</div>
                            <div style="font-size:12px;color:var(--text-muted);">{{ $t['role'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection

@section('scripts')
@if($flashSale && $flashSale->isActive())
<script>
    initCountdown({{ $flashSale->ends_at->timestamp }});
</script>
@endif

@if($banners->count() > 1)
<script>
(function() {
    const carousel = document.getElementById('heroCarousel');
    const counter = document.getElementById('heroCurrentSlide');
    const progressFill = document.getElementById('heroProgressFill');
    if (!carousel || !counter) return;

    const INTERVAL = 5000; // ms — khớp với data-bs-interval

    // Cập nhật số slide hiện tại
    carousel.addEventListener('slid.bs.carousel', function(e) {
        counter.textContent = e.to + 1;
    });

    // Thanh tiến trình
    function resetProgress() {
        if (!progressFill) return;
        progressFill.style.transition = 'none';
        progressFill.style.width = '0%';
        // Force reflow
        void progressFill.offsetWidth;
        progressFill.style.transition = 'width ' + INTERVAL + 'ms linear';
        progressFill.style.width = '100%';
    }

    // Reset khi bắt đầu slide mới
    carousel.addEventListener('slide.bs.carousel', resetProgress);
    // Khởi động lần đầu
    resetProgress();
})();
</script>
@endif
@endsection
