@extends('layouts.app')
@section('title', $product->name)
@section('meta_description', $product->short_description ?? $product->name . ' - Mua ngay tại Bloom & Bold với giá tốt nhất')

@section('content')

{{-- Breadcrumb --}}
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản Phẩm</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('products.category', $product->category->slug) }}">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ Str::limit($product->name, 40) }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section" style="padding-top: 40px;">
    <div class="container">
        <div class="row g-5">

            {{-- ─── Image Gallery ─────────────────────────── --}}
            <div class="col-lg-6">
                <div class="product-gallery">
                    {{-- Main Image --}}
                    <div class="gallery-main" id="galleryMain" style="position: relative;">
                        @php $mainImg = $product->images->first() ?? null; @endphp
                        <img id="mainImage"
                             src="{{ $mainImg ? asset('storage/products/'.$mainImg->image_path) : asset('assets/images/product-placeholder.jpg') }}"
                             alt="{{ $product->name }}"
                             style="width:100%;border-radius:16px;object-fit:cover;aspect-ratio:1;box-shadow:0 8px 32px rgba(0,0,0,.1);">
                        @if($product->is_on_sale)
                        <div style="position:absolute;top:16px;left:16px;">
                            <span class="badge-sale" style="font-size:14px;padding:6px 12px;">-{{ $product->discount_percent }}%</span>
                        </div>
                        @endif
                        @if($product->is_new)
                        <div style="position:absolute;top:16px;right:16px;">
                            <span class="badge-new" style="font-size:12px;padding:4px 10px;">MỚI</span>
                        </div>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if($product->images->count() > 1)
                    <div class="gallery-thumbs mt-3 d-flex gap-2 flex-wrap">
                        @foreach($product->images as $img)
                        <img src="{{ asset('storage/products/'.$img->image_path) }}"
                             alt=""
                             onclick="document.getElementById('mainImage').src = this.src; document.querySelectorAll('.gallery-thumb-active').forEach(e=>e.classList.remove('gallery-thumb-active')); this.classList.add('gallery-thumb-active');"
                             style="width:72px;height:72px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid {{ $img->is_primary ? 'var(--gold)' : 'var(--gray-300)' }};transition:var(--transition);"
                             class="{{ $img->is_primary ? 'gallery-thumb-active' : '' }}">
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- ─── Product Info ──────────────────────────── --}}
            <div class="col-lg-6">
                <div>
                    {{-- Brand & Category --}}
                    <div class="d-flex gap-3 mb-2" style="font-size:13px;">
                        @if($product->brand)
                            <span style="color:var(--gold);font-weight:600;">{{ $product->brand->name }}</span>
                        @endif
                        @if($product->category)
                            <span style="color:var(--text-muted);">{{ $product->category->name }}</span>
                        @endif
                    </div>

                    <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;color:var(--dark);margin-bottom:16px;">
                        {{ $product->name }}
                    </h1>

                    {{-- Rating --}}
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="stars" style="font-size:16px;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($product->avg_rating) ? '' : 'empty' }}"></i>
                            @endfor
                        </div>
                        <span style="font-size:13px;color:var(--text-muted);">
                            {{ number_format($product->avg_rating, 1) }} ({{ $product->review_count }} đánh giá)
                        </span>
                        @if($product->sold_count > 0)
                        <span style="font-size:13px;color:var(--text-muted);">· Đã bán {{ number_format($product->sold_count) }}</span>
                        @endif
                    </div>

                    {{-- Price --}}
                    <div class="mb-4" style="background:linear-gradient(135deg,rgba(201,168,76,.05),rgba(201,168,76,.1));border:1px solid rgba(201,168,76,.2);border-radius:12px;padding:20px 24px;">
                        @if($product->is_on_sale)
                        <div class="d-flex align-items-baseline gap-3">
                            <span style="font-size:2rem;font-weight:800;color:var(--gold);font-family:var(--font-heading);">{{ vnd($product->sale_price) }}</span>
                            <span style="font-size:1.1rem;color:var(--text-muted);text-decoration:line-through;">{{ vnd($product->price) }}</span>
                            <span class="badge-sale">-{{ $product->discount_percent }}%</span>
                        </div>
                        <div style="font-size:13px;color:var(--green);margin-top:6px;">
                            <i class="fas fa-tag me-1"></i> Tiết kiệm {{ vnd($product->price - $product->sale_price) }}
                        </div>
                        @else
                        <span style="font-size:2rem;font-weight:800;color:var(--gold);font-family:var(--font-heading);">{{ vnd($product->price) }}</span>
                        @endif
                    </div>

                    {{-- Short Description --}}
                    @if($product->short_description)
                    <p style="color:var(--text-muted);font-size:14px;line-height:1.8;margin-bottom:24px;">{{ $product->short_description }}</p>
                    @endif

                    {{-- Variants --}}
                    @php
                        $colors = $product->variants->where('color', '!=', null)->pluck('color')->unique();
                        $sizes = $product->variants->where('size', '!=', null)->pluck('size')->unique();
                    @endphp

                    @if($colors->count() > 0)
                    <div class="mb-4">
                        <div style="font-size:13px;font-weight:600;margin-bottom:10px;">Màu sắc: <span id="selectedColor" style="color:var(--gold);">{{ $colors->first() }}</span></div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($colors as $color)
                            <button class="color-btn {{ $loop->first ? 'active' : '' }}"
                                    onclick="selectColor('{{ $color }}', this)"
                                    data-color="{{ $color }}"
                                    style="padding:6px 16px;border:2px solid {{ $loop->first ? 'var(--gold)' : 'var(--gray-300)' }};border-radius:20px;font-size:12px;font-weight:600;background:white;cursor:pointer;transition:var(--transition);">
                                {{ $color }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($sizes->count() > 0)
                    <div class="mb-4">
                        <div style="font-size:13px;font-weight:600;margin-bottom:10px;">Kích thước: <span id="selectedSize" style="color:var(--gold);">{{ $sizes->first() }}</span></div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($sizes as $size)
                            <button class="size-btn {{ $loop->first ? 'active' : '' }}"
                                    onclick="selectSize('{{ $size }}', this)"
                                    data-size="{{ $size }}"
                                    style="padding:8px 20px;border:2px solid {{ $loop->first ? 'var(--gold)' : 'var(--gray-300)' }};border-radius:8px;font-size:13px;font-weight:600;background:white;cursor:pointer;transition:var(--transition);">
                                {{ $size }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Quantity + Add to Cart --}}
                    <div class="d-flex gap-3 align-items-center mb-4 flex-wrap">
                        <div class="qty-control d-flex align-items-center" style="border:1px solid var(--gray-300);border-radius:8px;overflow:hidden;">
                            <button onclick="changeQty(-1)" style="width:40px;height:44px;border:none;background:var(--gray-100);font-size:18px;cursor:pointer;" id="qtyMinus">−</button>
                            <input type="number" id="quantity" value="1" min="1" max="{{ $product->stock }}"
                                   style="width:60px;text-align:center;border:none;font-size:15px;font-weight:600;outline:none;padding:0 8px;height:44px;">
                            <button onclick="changeQty(1)" style="width:40px;height:44px;border:none;background:var(--gray-100);font-size:18px;cursor:pointer;" id="qtyPlus">+</button>
                        </div>

                        @if($product->stock > 0)
                        <button class="btn-gold flex-1" id="addToCartBtn"
                                onclick="addToCart({{ $product->id }})"
                                style="min-width:180px;justify-content:center;gap:10px;">
                            <i class="fas fa-shopping-bag"></i> Thêm Vào Giỏ Hàng
                        </button>
                        @else
                        <button class="btn-gold flex-1" disabled style="background:var(--gray-400);cursor:not-allowed;min-width:180px;justify-content:center;">
                            <i class="fas fa-times"></i> Hết Hàng
                        </button>
                        @endif

                        @auth
                        <button class="product-action-btn {{ $isWishlisted ? 'wishlisted' : '' }}"
                                style="width:44px;height:44px;font-size:18px;"
                                data-action="wishlist-toggle"
                                data-product-id="{{ $product->id }}"
                                title="Yêu thích">
                            <i class="fas fa-heart"></i>
                        </button>
                        @endauth
                    </div>

                    {{-- Stock Info --}}
                    <div style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">
                        @if($product->stock > 0)
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Còn hàng
                            @if($product->stock < 10)
                                — <span style="color:var(--orange);font-weight:600;">Chỉ còn {{ $product->stock }} sản phẩm!</span>
                            @endif
                        @else
                            <i class="fas fa-times-circle text-danger me-1"></i> Hết hàng
                        @endif
                        @if($product->sku)
                            <span class="ms-3">SKU: {{ $product->sku }}</span>
                        @endif
                        @if($product->material)
                            <span class="ms-3">Chất liệu: {{ $product->material }}</span>
                        @endif
                    </div>

                    {{-- Trust Badges --}}
                    <div class="d-flex gap-3 flex-wrap" style="padding:16px 0;border-top:1px solid var(--gray-200);border-bottom:1px solid var(--gray-200);">
                        <div class="d-flex align-items-center gap-2" style="font-size:12px;color:var(--text-muted);">
                            <i class="fas fa-shipping-fast text-gold"></i> Miễn phí ship từ 500k
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:12px;color:var(--text-muted);">
                            <i class="fas fa-undo text-gold"></i> Đổi trả 30 ngày
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:12px;color:var(--text-muted);">
                            <i class="fas fa-shield-alt text-gold"></i> Bảo hành 12 tháng
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Description & Reviews ──────────────────────────── --}}
        <div class="row mt-5">
            <div class="col-12">
                {{-- Tabs --}}
                <ul class="nav nav-tabs" style="border-bottom:2px solid var(--gray-200);margin-bottom:32px;">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="showTab('desc',this)" style="font-weight:600;font-size:14px;color:var(--gold);border:none;border-bottom:2px solid var(--gold);margin-bottom:-2px;padding:12px 20px;">Mô Tả Sản Phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showTab('reviews',this)" style="font-weight:600;font-size:14px;color:var(--text-muted);border:none;padding:12px 20px;">Đánh Giá ({{ $product->review_count }})</a>
                    </li>
                </ul>

                {{-- Description Tab --}}
                <div id="tab-desc">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div style="line-height:1.9;color:#333;font-size:14px;">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div style="background:var(--gray-100);border-radius:12px;padding:24px;">
                                <h6 style="font-weight:700;margin-bottom:16px;">Thông Tin Sản Phẩm</h6>
                                <table style="width:100%;font-size:13px;">
                                    @if($product->material)<tr><td style="color:var(--text-muted);padding:6px 0;">Chất liệu</td><td style="font-weight:600;">{{ $product->material }}</td></tr>@endif
                                    @if($product->weight)<tr><td style="color:var(--text-muted);padding:6px 0;">Trọng lượng</td><td style="font-weight:600;">{{ $product->weight }} kg</td></tr>@endif
                                    @if($product->sku)<tr><td style="color:var(--text-muted);padding:6px 0;">SKU</td><td style="font-weight:600;">{{ $product->sku }}</td></tr>@endif
                                    @if($product->brand)<tr><td style="color:var(--text-muted);padding:6px 0;">Thương hiệu</td><td style="font-weight:600;">{{ $product->brand->name }}</td></tr>@endif
                                    <tr><td style="color:var(--text-muted);padding:6px 0;">Tình trạng</td><td><span style="color:{{ $product->stock > 0 ? 'var(--green)' : 'var(--red)' }};font-weight:600;">{{ $product->stock > 0 ? 'Còn hàng' : 'Hết hàng' }}</span></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reviews Tab --}}
                <div id="tab-reviews" style="display:none;">
                    <div class="row g-4">
                        {{-- Rating Summary --}}
                        <div class="col-lg-3">
                            <div style="text-align:center;background:var(--gray-100);border-radius:12px;padding:32px 16px;">
                                <div style="font-size:4rem;font-weight:800;color:var(--gold);line-height:1;">{{ number_format($product->avg_rating, 1) }}</div>
                                <div class="stars my-2" style="font-size:20px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($product->avg_rating) ? '' : 'empty' }}"></i>
                                    @endfor
                                </div>
                                <div style="font-size:13px;color:var(--text-muted);">{{ $product->review_count }} đánh giá</div>
                            </div>
                            {{-- Rating Breakdown --}}
                            <div class="mt-3">
                                @foreach($ratingBreakdown as $star => $data)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span style="font-size:12px;width:16px;">{{ $star }}</span>
                                    <i class="fas fa-star text-gold" style="font-size:11px;"></i>
                                    <div style="flex:1;height:6px;background:var(--gray-300);border-radius:3px;overflow:hidden;">
                                        <div style="width:{{ $data['percent'] }}%;height:100%;background:var(--gold);border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:12px;color:var(--text-muted);width:24px;">{{ $data['count'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-9">
                            {{-- Write Review --}}
                            @auth
                            @if($canReview && !$userReview)
                            <div style="background:rgba(201,168,76,.05);border:1px solid rgba(201,168,76,.2);border-radius:12px;padding:24px;margin-bottom:24px;">
                                <h6 style="font-weight:700;margin-bottom:16px;">Viết Đánh Giá Của Bạn</h6>
                                <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label style="font-size:13px;font-weight:600;margin-bottom:8px;display:block;">Đánh giá</label>
                                        <div class="star-rating d-flex gap-2" style="font-size:24px;">
                                            @for($i = 1; $i <= 5; $i++)
                                            <label style="cursor:pointer;color:var(--gray-300);">
                                                <input type="radio" name="rating" value="{{ $i }}" style="display:none;" required>
                                                <i class="fas fa-star" onclick="setRating({{ $i }})"></i>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" name="title" class="form-control" placeholder="Tiêu đề đánh giá" style="font-size:13px;border-radius:8px;">
                                    </div>
                                    <div class="mb-3">
                                        <textarea name="comment" class="form-control" rows="4" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." style="font-size:13px;border-radius:8px;resize:none;"></textarea>
                                    </div>
                                    <button type="submit" class="btn-gold" style="padding:10px 24px;">
                                        <i class="fas fa-paper-plane me-2"></i>Gửi Đánh Giá
                                    </button>
                                </form>
                            </div>
                            @endif
                            @endauth

                            {{-- Review List --}}
                            @forelse($product->reviews->take(10) as $review)
                            <div style="padding:20px 0;border-bottom:1px solid var(--gray-200);">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex gap-3">
                                        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0;">
                                            {{ substr($review->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600;font-size:14px;">{{ $review->user->name ?? 'Ẩn danh' }}</div>
                                            <div class="stars" style="font-size:13px;margin:2px 0;">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'empty' }}"></i>
                                                @endfor
                                            </div>
                                            <div style="font-size:12px;color:var(--text-muted);">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                                @if($review->title)
                                <div style="font-weight:600;font-size:14px;margin:12px 0 6px;">{{ $review->title }}</div>
                                @endif
                                @if($review->comment)
                                <p style="font-size:14px;color:#444;line-height:1.7;margin:0;">{{ $review->comment }}</p>
                                @endif
                                @if($review->admin_reply)
                                <div style="margin-top:12px;background:var(--gray-100);border-left:3px solid var(--gold);padding:12px 16px;border-radius:0 8px 8px 0;font-size:13px;">
                                    <strong style="color:var(--gold);">Phản hồi từ Bloom & Bold:</strong>
                                    <p style="margin:4px 0 0;color:#555;">{{ $review->admin_reply }}</p>
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fas fa-star" style="font-size:2.5rem;color:var(--gray-300);display:block;margin-bottom:12px;"></i>
                                <p style="color:var(--text-muted);">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Related Products ──────────────────────────────── --}}
        @if($related->count() > 0)
        <div class="mt-5 pt-4" style="border-top:1px solid var(--gray-200);">
            <h2 class="section-title mb-4">Sản Phẩm Liên Quan</h2>
            <div class="row g-4 stagger-children">
                @foreach($related as $relProduct)
                <div class="col-6 col-md-4 col-lg-2 animate-on-scroll">
                    @include('partials.product-card', ['product' => $relProduct])
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

<input type="hidden" id="selectedVariantId" value="">

@endsection

@section('scripts')
<script>
let selectedColor = '{{ optional($product->variants->first())->color ?? "" }}';
let selectedSize = '{{ optional($product->variants->first())->size ?? "" }}';
const variants = @json($product->variants);

function showTab(tab, el) {
    document.getElementById('tab-desc').style.display = 'none';
    document.getElementById('tab-reviews').style.display = 'none';
    document.getElementById('tab-' + tab).style.display = 'block';
    document.querySelectorAll('.nav-link').forEach(l => {
        l.style.color = 'var(--text-muted)';
        l.style.borderBottom = 'none';
    });
    el.style.color = 'var(--gold)';
    el.style.borderBottom = '2px solid var(--gold)';
    return false;
}

function selectColor(color, btn) {
    selectedColor = color;
    document.getElementById('selectedColor').textContent = color;
    document.querySelectorAll('.color-btn').forEach(b => {
        b.style.border = '2px solid var(--gray-300)';
    });
    btn.style.border = '2px solid var(--gold)';
    updateVariant();
}

function selectSize(size, btn) {
    selectedSize = size;
    document.getElementById('selectedSize').textContent = size;
    document.querySelectorAll('.size-btn').forEach(b => {
        b.style.border = '2px solid var(--gray-300)';
    });
    btn.style.border = '2px solid var(--gold)';
    updateVariant();
}

function updateVariant() {
    const variant = variants.find(v =>
        (!selectedColor || v.color === selectedColor) &&
        (!selectedSize || v.size === selectedSize)
    );
    document.getElementById('selectedVariantId').value = variant ? variant.id : '';
}

function changeQty(delta) {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, max));
    input.value = val;
}

function addToCart(productId) {
    const qty = parseInt(document.getElementById('quantity').value);
    const variantId = document.getElementById('selectedVariantId').value;
    const btn = document.getElementById('addToCartBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';

    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, variant_id: variantId || null, quantity: qty })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Đã Thêm!';
            btn.style.background = 'var(--green)';
            const badge = document.getElementById('cartCount');
            if (badge) { badge.textContent = data.cart_count; badge.style.display = 'flex'; }
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Thêm Vào Giỏ Hàng';
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shopping-bag"></i> Thêm Vào Giỏ Hàng';
        }
    });
}

function setRating(val) {
    document.querySelectorAll('.star-rating label').forEach((l, i) => {
        l.style.color = i < val ? 'var(--gold)' : 'var(--gray-300)';
    });
    document.querySelectorAll('input[name="rating"]').forEach((r, i) => {
        r.checked = i + 1 === val;
    });
}

updateVariant();
</script>
@endsection
