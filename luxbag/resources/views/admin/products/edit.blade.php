@extends('layouts.admin')

@section('title', 'Chỉnh Sửa: '.$product->name)
@section('page-title', 'Chỉnh Sửa Sản Phẩm')
@section('breadcrumb', 'Sản Phẩm / Chỉnh sửa')

@section('head')
<style>
.img-gallery-item { position:relative; border-radius:8px; overflow:hidden; border:1px solid var(--border); }
.img-gallery-item img { width:90px; height:90px; object-fit:cover; display:block; }
.img-gallery-item .overlay { position:absolute; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; gap:6px; opacity:0; transition:.2s; }
.img-gallery-item:hover .overlay { opacity:1; }
.img-gallery-item .primary-badge { position:absolute; top:4px; left:4px; background:var(--gold); color:#000; font-size:9px; padding:2px 6px; border-radius:4px; font-weight:700; }
.variant-row { background:var(--body-bg); border-radius:8px; padding:12px 16px; margin-bottom:8px; display:flex; align-items:center; gap:12px; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-700 mb-1">{{ $product->name }}</h5>
        <small style="color:var(--text-muted);">SKU: {{ $product->sku }} • Tạo: {{ $product->created_at->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn-admin-secondary">
            <i class="fas fa-external-link-alt"></i> Xem ngoài shop
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" id="editProductForm">
    @csrf @method('PUT')

    <div class="row g-4" style="align-items:flex-start;">
        {{-- ─── Main Column ─── --}}
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Thông Tin Cơ Bản</h6>
                <div class="mb-3">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $product->name) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand_id" class="form-select">
                            <option value="">Không có</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả ngắn</label>
                    <textarea name="short_description" class="form-control" rows="2" maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả chi tiết</label>
                    <textarea name="description" class="form-control" rows="8">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            {{-- Images --}}
            <div class="admin-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-700 mb-0">Hình Ảnh ({{ $product->images->count() }})</h6>
                    <label for="newImageInput" class="btn-admin-secondary" style="cursor:pointer;">
                        <i class="fas fa-plus me-1"></i>Thêm ảnh
                    </label>
                </div>
                <input type="file" id="newImageInput" multiple accept="image/*" style="display:none;" onchange="uploadNewImages(this)">

                <div class="d-flex flex-wrap gap-3" id="imageGallery">
                    @foreach($product->images as $img)
                    <div class="img-gallery-item" id="img-{{ $img->id }}">
                        <img src="{{ $img->url }}" alt="">
                        @if($img->is_primary)
                            <div class="primary-badge">CHÍNH</div>
                        @endif
                        <div class="overlay">
                            @if(!$img->is_primary)
                            <button type="button" class="btn-admin-secondary" style="padding:4px 8px;font-size:11px;" onclick="setPrimary({{ $img->id }})" title="Đặt làm ảnh chính">
                                <i class="fas fa-star"></i>
                            </button>
                            @endif
                            <button type="button" class="btn-admin-danger" style="padding:4px 8px;font-size:11px;" onclick="removeImage({{ $img->id }})" title="Xóa ảnh">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($product->images->isEmpty())
                <div class="upload-zone mt-2" onclick="document.getElementById('newImageInput').click()">
                    <i class="fas fa-image" style="font-size:28px;color:var(--text-muted);display:block;margin-bottom:8px;"></i>
                    <div>Click để thêm ảnh sản phẩm</div>
                </div>
                @endif
            </div>

            {{-- Variants --}}
            <div class="admin-card admin-form">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-700 mb-0">Biến Thể ({{ $product->variants->count() }})</h6>
                    <button type="button" class="btn-admin-secondary" onclick="document.getElementById('variantForm').style.display='block'">
                        <i class="fas fa-plus"></i> Thêm biến thể
                    </button>
                </div>

                {{-- Existing Variants --}}
                <div id="variantList">
                    @foreach($product->variants as $variant)
                    <div class="variant-row" id="variant-{{ $variant->id }}">
                        @if($variant->color_hex)
                            <div style="width:20px;height:20px;border-radius:50%;background:{{ $variant->color_hex }};border:2px solid var(--border);flex-shrink:0;"></div>
                        @endif
                        <div class="flex-1">
                            <div style="font-weight:600;font-size:13px;">
                                {{ $variant->size ? 'Size: '.$variant->size : '' }}
                                {{ $variant->color ? ' / '.$variant->color : '' }}
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);">
                                Tồn kho: {{ $variant->stock }}
                                @if($variant->price_modifier != 0)
                                 • {{ $variant->price_modifier > 0 ? '+' : '' }}{{ vnd($variant->price_modifier) }}
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn-admin-danger" style="padding:5px 10px;font-size:11px;" onclick="removeVariant({{ $variant->id }})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Add Variant Form --}}
                <div id="variantForm" style="display:none;background:var(--body-bg);border-radius:8px;padding:16px;margin-top:12px;">
                    <div class="row g-3">
                        <div class="col-6"><input type="text" id="vSize" class="form-control" placeholder="Size (S/M/L...)"></div>
                        <div class="col-6"><input type="text" id="vColor" class="form-control" placeholder="Màu sắc"></div>
                        <div class="col-6"><input type="color" id="vColorHex" class="form-control form-control-color" style="height:42px;" title="Chọn màu hex"></div>
                        <div class="col-6"><input type="number" id="vStock" class="form-control" placeholder="Tồn kho" min="0"></div>
                        <div class="col-12"><input type="number" id="vPriceModifier" class="form-control" placeholder="Chênh lệch giá (+ hoặc -)" step="1000"></div>
                        <div class="col-12 d-flex gap-2">
                            <button type="button" class="btn-admin-primary" onclick="addVariant()"><i class="fas fa-plus me-1"></i>Thêm</button>
                            <button type="button" class="btn-admin-secondary" onclick="document.getElementById('variantForm').style.display='none'">Hủy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Side Column ─── --}}
        <div class="col-lg-4" style="align-self:flex-start;">
            {{-- Pricing --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Giá & Kho Hàng</h6>
                <div class="mb-3">
                    <label class="form-label">Giá gốc <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="price" class="form-control" required value="{{ old('price', $product->price) }}" min="0" step="1000">
                        <span class="input-group-text" style="font-size:13px;background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">đ</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Giá khuyến mãi</label>
                    <div class="input-group">
                        <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}" min="0" step="1000" placeholder="Để trống nếu không giảm">
                        <span class="input-group-text" style="font-size:13px;background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">đ</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" required value="{{ old('stock', $product->stock) }}" min="0">
                </div>
            </div>

            {{-- Status --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Trạng Thái</h6>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Đang bán</option>
                        <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                        <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}>Nháp</option>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" {{ $product->is_featured ? 'checked' : '' }}>
                    <label class="form-check-label fw-600" for="isFeatured">Sản phẩm nổi bật</label>
                </div>
            </div>

            {{-- Stats --}}
            <div class="admin-card mb-4">
                <h6 class="fw-700 mb-3">Thống Kê</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Đã bán</span>
                    <strong style="font-size:13px;">{{ number_format($product->sold_count) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Lượt xem</span>
                    <strong style="font-size:13px;">{{ number_format($product->view_count) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Đánh giá</span>
                    <strong style="font-size:13px;">{{ $product->avg_rating }}/5 ({{ $product->review_count }})</strong>
                </div>
            </div>

            {{-- Details --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Thông Tin Thêm</h6>
                <div class="mb-3">
                    <label class="form-label">Chất liệu</label>
                    <input type="text" name="material" class="form-control" value="{{ old('material', $product->material) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Trọng lượng (kg)</label>
                    <input type="number" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}" step="0.01" min="0">
                </div>
            </div>

            {{-- SEO --}}
            <div class="admin-card admin-form">
                <h6 class="fw-700 mb-4">SEO</h6>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">Hủy</a>
        <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Thay Đổi</button>
    </div>
</form>

@endsection

@section('scripts')
<script>
const PRODUCT_ID = {{ $product->id }};
const BASE_URL = '{{ url('/') }}';

function removeImage(imgId) {
    if (!confirm('Xóa ảnh này?')) return;
    $.ajax({
        url: `${BASE_URL}/admin/san-pham/anh/${imgId}`,
        method: 'DELETE',
    }).done(res => {
        if (res.success) document.getElementById(`img-${imgId}`).remove();
    });
}

function setPrimary(imgId) {
    $.ajax({
        url: `${BASE_URL}/admin/san-pham/anh/${imgId}/chinh`,
        method: 'PATCH',
    }).done(res => {
        if (res.success) location.reload();
    });
}

function uploadNewImages(input) {
    const formData = new FormData();
    Array.from(input.files).forEach(f => formData.append('images[]', f));
    $.ajax({
        url: `${BASE_URL}/admin/san-pham/${PRODUCT_ID}/anh`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
    }).done(res => {
        if (res.success) location.reload();
    }).fail(err => {
        console.error(err);
        let msg = 'Lỗi không xác định khi upload ảnh';
        if (err.responseJSON && err.responseJSON.message) {
            msg = err.responseJSON.message;
        }
        alert('Lỗi upload: ' + msg);
    });
}

function addVariant() {
    const size = document.getElementById('vSize').value.trim();
    const color = document.getElementById('vColor').value.trim();
    if (!size && !color) {
        alert('Vui lòng nhập ít nhất Size hoặc Màu sắc!');
        return;
    }
    const stock = document.getElementById('vStock').value;
    if (!stock && stock !== '0') {
        alert('Vui lòng nhập số lượng tồn kho!');
        return;
    }
    const data = {
        size:           size,
        color:          color,
        color_hex:      document.getElementById('vColorHex').value,
        stock:          stock || 0,
        price_modifier: document.getElementById('vPriceModifier').value || 0,
    };
    $.ajax({
        url: `${BASE_URL}/admin/san-pham/${PRODUCT_ID}/bien-the`,
        method: 'POST',
        data: data,
    }).done(res => {
        if (res.success) {
            location.reload();
        } else {
            alert('Thêm biến thể thất bại!');
        }
    }).fail(err => {
        console.error(err);
        let msg = 'Có lỗi xảy ra khi thêm biến thể';
        if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
            msg = Object.values(err.responseJSON.errors).flat().join('\n');
        }
        alert(msg);
    });
}

function removeVariant(variantId) {
    if (!confirm('Xóa biến thể này? Hành động không thể hoàn tác!')) return;
    $.ajax({
        url: `${BASE_URL}/admin/san-pham/bien-the/${variantId}`,
        method: 'DELETE',
    }).done(res => {
        if (res.success) {
            const el = document.getElementById(`variant-${variantId}`);
            if (el) el.remove();
        } else {
            alert('Xóa biến thể thất bại!');
        }
    }).fail(err => {
        console.error(err);
        alert('Có lỗi xảy ra khi xóa biến thể. Vui lòng thử lại!');
    });
}
</script>
@endsection

