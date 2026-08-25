@extends('layouts.app')
@section('title', 'Tìm kiếm: ' . $keyword)

@section('content')
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li class="breadcrumb-item active">Tìm Kiếm</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <h1 class="section-title mb-4">
            @if($keyword)
                Kết quả tìm kiếm: "<strong>{{ $keyword }}</strong>"
            @else
                Tìm Kiếm Sản Phẩm
            @endif
        </h1>

        {{-- Search bar --}}
        <form action="{{ route('products.search') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="{{ $keyword }}">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>

        @if(strlen($keyword) >= 2)
            @if($products->count() > 0)
                <p class="text-muted mb-3">Tìm thấy {{ $products->total() }} sản phẩm cho "{{ $keyword }}"</p>
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card">
                                <a href="{{ route('products.show', $product->slug) }}">
                                    <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" class="img-fluid">
                                </a>
                                <div class="product-card-body p-3">
                                    <h3 class="product-name">
                                        <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h3>
                                    <div class="product-price">
                                        @if($product->is_on_sale)
                                            <span class="price-sale">{{ $product->formatted_price }}</span>
                                            <span class="price-original text-muted text-decoration-line-through">{{ $product->formatted_original_price }}</span>
                                        @else
                                            <span class="price-original">{{ $product->formatted_price }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Không tìm thấy sản phẩm nào cho "{{ $keyword }}".</p>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Xem Tất Cả Sản Phẩm</a>
                </div>
            @endif
        @elseif($keyword)
            <div class="alert alert-info">Vui lòng nhập ít nhất 2 ký tự để tìm kiếm.</div>
        @endif
    </div>
</section>
@endsection
