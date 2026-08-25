@extends('layouts.app')
@section('title', 'Tất Cả Sản Phẩm')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li class="breadcrumb-item active">Sản Phẩm</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">

            {{-- ─── Filter Sidebar ─── --}}
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="mb-0 fw-700">Bộ Lọc</h6>
                        <a href="{{ route('products.index') }}" style="font-size:12px;color:var(--gold);">Xóa tất cả</a>
                    </div>

                    <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                        {{-- Preserve search --}}
                        @if(request('tim-kiem'))
                            <input type="hidden" name="tim-kiem" value="{{ request('tim-kiem') }}">
                        @endif

                        {{-- Categories --}}
                        <div class="mb-4">
                            <div class="filter-section-title">Danh Mục</div>
                            @foreach($categories as $cat)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="danh-muc"
                                       id="cat-{{ $cat->id }}" value="{{ $cat->slug }}"
                                       {{ request('danh-muc') == $cat->slug ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <label class="form-check-label d-flex justify-content-between" for="cat-{{ $cat->id }}"
                                       style="font-size:13px;cursor:pointer;">
                                    {{ $cat->name }}
                                    <span style="color:var(--text-muted)">({{ $cat->products_count }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- Brands --}}
                        <div class="mb-4">
                            <div class="filter-section-title">Thương Hiệu</div>
                            @foreach($brands as $brand)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="thuong-hieu"
                                       id="brand-{{ $brand->id }}" value="{{ $brand->slug }}"
                                       {{ request('thuong-hieu') == $brand->slug ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <label class="form-check-label" for="brand-{{ $brand->id }}" style="font-size:13px;cursor:pointer;">
                                    {{ $brand->name }}
                                    <span style="color:var(--text-muted)">({{ $brand->products_count }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- Price Range --}}
                        <div class="mb-4">
                            <div class="filter-section-title">Giá</div>
                            <div class="price-range-labels d-flex justify-content-between mb-2">
                                <span id="priceMinLabel">{{ vnd($priceMin) }}</span>
                                <span id="priceMaxLabel">{{ vnd($priceMax) }}</span>
                            </div>
                            <input type="range" id="priceMin" name="gia-min"
                                   min="{{ $priceMin }}" max="{{ $priceMax }}"
                                   value="{{ request('gia-min', $priceMin) }}" step="50000"
                                   onchange="document.getElementById('priceMax').min = this.value;">
                            <input type="range" id="priceMax" name="gia-max"
                                   min="{{ $priceMin }}" max="{{ $priceMax }}"
                                   value="{{ request('gia-max', $priceMax) }}" step="50000"
                                   class="mt-2"
                                   onchange="document.getElementById('priceMin').max = this.value;">
                            <button type="submit" class="btn-gold w-100 mt-3 justify-content-center" style="font-size:13px;padding:10px;">
                                Áp Dụng
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ─── Product Grid ─── --}}
            <div class="col-lg-9">
                {{-- Toolbar --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <h5 class="fw-700 mb-0">Sản Phẩm
                            @if(request('danh-muc'))
                                <span class="text-gold"> — {{ ucfirst(str_replace('-', ' ', request('danh-muc'))) }}</span>
                            @endif
                        </h5>
                        <p class="text-muted mb-0" style="font-size:13px;">{{ $products->total() }} sản phẩm</p>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        {{-- Search --}}
                        <form action="{{ route('products.search') }}" method="GET" class="d-none d-md-flex">
                            <div class="input-group" style="width:220px;">
                                <input type="text" name="q" class="form-control form-control-sm"
                                       placeholder="Tìm kiếm..." value="{{ request('q') }}"
                                       style="border-radius:8px 0 0 8px;font-size:13px;">
                                <button class="btn-dark" type="submit" style="border-radius:0 8px 8px 0;padding:0 12px;font-size:13px;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Sort --}}
                        <form action="{{ route('products.index') }}" method="GET" id="sortForm">
                            @foreach(request()->except('sap-xep') as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <select name="sap-xep" class="form-select form-select-sm" style="font-size:13px;border-radius:8px;width:auto;"
                                    onchange="this.form.submit()">
                                <option value="newest" {{ request('sap-xep', 'newest') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="price-asc" {{ request('sap-xep') == 'price-asc' ? 'selected' : '' }}>Giá tăng dần</option>
                                <option value="price-desc" {{ request('sap-xep') == 'price-desc' ? 'selected' : '' }}>Giá giảm dần</option>
                                <option value="best-seller" {{ request('sap-xep') == 'best-seller' ? 'selected' : '' }}>Bán chạy nhất</option>
                                <option value="rating" {{ request('sap-xep') == 'rating' ? 'selected' : '' }}>Đánh giá cao</option>
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Products --}}
                @if($products->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-search" style="font-size:3rem;color:var(--gray-300);margin-bottom:16px;display:block;"></i>
                    <h6 class="text-muted">Không tìm thấy sản phẩm phù hợp</h6>
                    <a href="{{ route('products.index') }}" class="btn-gold mt-3" style="display:inline-flex;">
                        Xem Tất Cả Sản Phẩm
                    </a>
                </div>
                @else
                <div class="row g-4 stagger-children">
                    @foreach($products as $product)
                    <div class="col-6 col-md-4">
                        @include('partials.product-card', compact('product'))
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-5">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
