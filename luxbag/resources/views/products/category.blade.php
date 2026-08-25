@extends('layouts.app')
@section('title', 'Danh mục: ' . $category->name)

@section('content')
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản Phẩm</a></li>
                <li class="breadcrumb-item active">{{ $category->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <h1 class="section-title mb-4">{{ $category->name }}</h1>

        @if($products->count() > 0)
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
                @foreach($products as $product)
                    <div class="col">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <p class="text-muted">Chưa có sản phẩm trong danh mục này.</p>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Xem Tất Cả Sản Phẩm</a>
            </div>
        @endif
    </div>
</section>
@endsection
