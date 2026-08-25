@php
    $price = isset($flashDiscount) ? ($product->price * (1 - $flashDiscount / 100)) : $product->current_price;
    $isOnSale = isset($flashDiscount) || $product->is_on_sale;
    $discountLabel = isset($flashDiscount) ? "-{$flashDiscount}%" : ($product->is_on_sale ? "-{$product->discount_percent}%" : null);
@endphp

<div class="product-card">
    {{-- Image --}}
    <div class="product-card-img">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}" loading="lazy">
        </a>

        {{-- Badges --}}
        <div class="product-card-badges">
            @if($discountLabel)
                <span class="badge-sale">{{ $discountLabel }}</span>
            @endif
            @if($product->is_new)
                <span class="badge-new">MỚI</span>
            @endif
            @if($product->stock < 5 && $product->stock > 0)
                <span style="background:var(--orange);color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:3px;">Sắp hết</span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="product-card-actions">
            <button class="product-action-btn {{ auth()->check() && auth()->user()->wishlistProducts()->where('products.id', $product->id)->exists() ? 'wishlisted' : '' }}"
                    data-action="wishlist-toggle"
                    data-product-id="{{ $product->id }}"
                    title="Yêu thích">
                <i class="fas fa-heart"></i>
            </button>
            <a href="{{ route('products.show', $product->slug) }}" class="product-action-btn" title="Xem chi tiết">
                <i class="fas fa-eye"></i>
            </a>
        </div>
    </div>

    {{-- Body --}}
    <div class="product-card-body">
        @if($product->category)
        <div class="product-card-category">{{ $product->category->name }}</div>
        @endif

        <a href="{{ route('products.show', $product->slug) }}" class="product-card-name d-block">
            {{ $product->name }}
        </a>

        <div class="product-card-rating">
            <div class="stars">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= round($product->avg_rating) ? '' : 'empty' }}"></i>
                @endfor
            </div>
            <span class="rating-count">({{ $product->review_count }})</span>
            @if($product->sold_count > 0)
                <span class="rating-count">· Đã bán {{ number_format($product->sold_count) }}</span>
            @endif
        </div>

        <div class="product-card-price">
            <span class="price-current">{{ vnd($price) }}</span>
            @if($isOnSale || isset($flashDiscount))
                <span class="price-original">{{ vnd($product->price) }}</span>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="product-card-footer">
        @if($product->stock > 0)
        <button class="btn-add-to-cart"
                data-action="add-to-cart"
                data-product-id="{{ $product->id }}">
            <i class="fas fa-shopping-bag"></i>
            Thêm Vào Giỏ
        </button>
        @else
        <button class="btn-add-to-cart" disabled style="background:var(--gray-400);cursor:not-allowed;">
            <i class="fas fa-times"></i> Hết Hàng
        </button>
        @endif
    </div>
</div>
