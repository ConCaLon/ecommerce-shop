@extends('layouts.app')

@section('title', 'Danh Sách Yêu Thích')
@section('meta_description', 'Xem các sản phẩm yêu thích của bạn tại Bloom & Bold.')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-700 mb-0">❤️ Yêu Thích Của Tôi</h1>
        <a href="{{ route('profile.index') }}" style="color:#888;font-size:13px;text-decoration:none;">
            <i class="fas fa-arrow-left me-1"></i>Tài khoản
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4 rounded-3" style="border-left:4px solid #27AE60;">
        <i class="fas fa-check-circle me-2" style="color:#27AE60;"></i>{{ session('success') }}
    </div>
    @endif

    @if($wishlistItems->isEmpty())
    <div style="text-align:center;padding:80px 20px;background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;">
        <div style="font-size:72px;margin-bottom:20px;opacity:.4;">💔</div>
        <h5 style="color:#555;margin-bottom:12px;">Chưa có sản phẩm yêu thích</h5>
        <p style="color:#888;margin-bottom:24px;">Hãy khám phá và lưu những sản phẩm bạn thích!</p>
        <a href="{{ route('products.index') }}"
           style="display:inline-block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;padding:12px 28px;border-radius:12px;font-weight:700;text-decoration:none;box-shadow:0 4px 20px rgba(138,111,214,.35);">
            <i class="fas fa-shopping-bag me-2"></i>Khám Phá Sản Phẩm
        </a>
    </div>
    @else

    <div class="row g-4">
        @foreach($wishlistItems as $item)
        @if($item->product)
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(123,94,201,.06);border:1px solid #EAE1F8;overflow:hidden;transition:.2s;"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 30px rgba(123,94,201,.15)';this.style.borderColor='#C4B5FD';"
                 onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 16px rgba(123,94,201,.06)';this.style.borderColor='#EAE1F8';">
                {{-- Image --}}
                <div style="position:relative;overflow:hidden;height:220px;">
                    <a href="{{ route('products.show', $item->product->slug) }}">
                        @if($item->product->primaryImage)
                            <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product->name }}"
                                 style="width:100%;height:100%;object-fit:cover;transition:.4s;"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='none'">
                        @else
                            <div style="width:100%;height:100%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-box" style="font-size:32px;color:#ddd;"></i>
                            </div>
                        @endif
                    </a>
                    {{-- Remove button --}}
                    <form method="POST" action="{{ route('wishlist.remove', $item->product_id) }}"
                          style="position:absolute;top:10px;right:10px;">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.9);border:none;color:#e74c3c;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.15);"
                            title="Xóa khỏi yêu thích">
                            <i class="fas fa-heart"></i>
                        </button>
                    </form>
                    @if($item->product->is_on_sale)
                    <span style="position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;">
                        -{{ $item->product->discount_percent }}%
                    </span>
                    @endif
                </div>

                {{-- Info --}}
                <div style="padding:16px;">
                    @if($item->product->category)
                    <div style="font-size:11px;color:#8A6FD6;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                        {{ $item->product->category->name }}
                    </div>
                    @endif
                    <a href="{{ route('products.show', $item->product->slug) }}"
                       style="font-weight:700;font-size:14px;color:#2D243D;text-decoration:none;display:block;margin-bottom:8px;
                              white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $item->product->name }}
                    </a>
                    <div class="d-flex align-items-center gap-2 mb-12">
                        <span style="font-weight:800;font-size:16px;color:#6345B5;">{{ vnd($item->product->current_price) }}</span>
                        @if($item->product->is_on_sale)
                        <span style="font-size:12px;color:#aaa;text-decoration:line-through;">{{ vnd($item->product->price) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('products.show', $item->product->slug) }}"
                       style="display:block;text-align:center;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;padding:9px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;margin-top:12px;box-shadow:0 4px 15px rgba(138,111,214,.35);">
                        <i class="fas fa-shopping-bag me-1"></i>Mua Ngay
                    </a>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    @if($wishlistItems->hasPages())
    <div class="d-flex justify-content-center mt-5">{{ $wishlistItems->links() }}</div>
    @endif

    @endif
</div>
@endsection
