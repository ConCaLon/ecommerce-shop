@extends('layouts.app')
@section('title', 'Thương hiệu: ' . $brand->name)

@section('content')
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản Phẩm</a></li>
                <li class="breadcrumb-item active">{{ $brand->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <h1 class="section-title mb-4">{{ $brand->name }}</h1>

        @if($products->count() > 0)
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
                <p class="text-muted">Chưa có sản phẩm từ thương hiệu này.</p>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Xem Tất Cả Sản Phẩm</a>
            </div>
        @endif
    </div>
</section>
@endsection
