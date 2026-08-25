@extends('layouts.admin')

@section('title', 'Flash Sale: '.$flashSale->name)
@section('page-title', 'Quản Lý Sản Phẩm Flash Sale')
@section('breadcrumb', 'Flash Sale / '.$flashSale->name)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-700 mb-1">⚡ {{ $flashSale->name }}</h5>
        <small style="color:var(--text-muted);">
            {{ $flashSale->starts_at->format('d/m/Y H:i') }} → {{ $flashSale->ends_at->format('d/m/Y H:i') }}
        </small>
    </div>
    <a href="{{ route('admin.flash-sales.index') }}" class="btn-admin-secondary">
        <i class="fas fa-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-4">
    {{-- ─── Add Product ─── --}}
    <div class="col-lg-4">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">Thêm Sản Phẩm</h6>
            <form method="POST" action="{{ route('admin.flash-sales.products.add', $flashSale) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">ID Sản phẩm <span class="text-danger">*</span></label>
                    <input type="number" name="product_id" class="form-control" required placeholder="Nhập ID sản phẩm">
                    <small style="color:var(--text-muted);font-size:11px;">Lấy ID từ trang quản lý sản phẩm</small>
                </div>
                <div class="mb-4">
                    <label class="form-label">Giảm giá (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="discount_percent" class="form-control" required min="1" max="99" step="0.5" placeholder="10">
                        <span class="input-group-text" style="background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">%</span>
                    </div>
                </div>
                <button type="submit" class="btn-admin-primary w-100"><i class="fas fa-plus me-1"></i>Thêm Vào Flash Sale</button>
            </form>
        </div>
    </div>

    {{-- ─── Products List ─── --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Sản Phẩm Trong Flash Sale ({{ $flashSale->products->count() }})</h6>
            @forelse($flashSale->products as $product)
            <div class="d-flex align-items-center gap-3 mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                @if($product->primaryImage)
                    <img src="{{ $product->primaryImage->url }}" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
                @else
                    <div style="width:48px;height:48px;border-radius:8px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-box" style="color:var(--text-muted);"></i>
                    </div>
                @endif
                <div class="flex-1">
                    <div style="font-weight:600;font-size:13.5px;">{{ $product->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">
                        Giá gốc: {{ vnd($product->price) }}
                        <span style="color:var(--red);font-weight:700;margin-left:8px;">-{{ $product->pivot->discount_percent }}%</span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:14px;font-weight:700;color:var(--gold);">{{ vnd($product->pivot->sale_price) }}</div>
                </div>
                <form method="POST" action="{{ route('admin.flash-sales.products.remove', [$flashSale, $product]) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-admin-danger" onclick="return confirm('Xóa sản phẩm khỏi Flash Sale?')" style="padding:6px 12px;">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            @empty
            <div style="text-align:center;padding:40px;color:var(--text-muted);">
                <i class="fas fa-box-open" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
                Chưa có sản phẩm nào
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
