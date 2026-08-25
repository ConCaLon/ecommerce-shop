@extends('layouts.admin')

@section('title', 'Thêm Sản Phẩm Mới')
@section('page-title', 'Thêm Sản Phẩm')
@section('breadcrumb', 'Sản Phẩm / Thêm mới')

@section('head')
<style>
.nav-tabs-product .nav-link { border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:13.5px; padding:12px 20px; border-radius:0; }
.nav-tabs-product .nav-link.active { color:var(--gold); border-bottom-color:var(--gold); background:none; }
.preview-img { width:100px; height:100px; border-radius:8px; object-fit:cover; border:1px solid var(--border); }
.preview-item { position:relative; display:inline-block; }
.preview-item .remove-img { position:absolute; top:-6px; right:-6px; width:20px; height:20px; border-radius:50%; background:var(--red); color:#fff; border:none; font-size:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
</style>
@endsection

@section('content')

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="productForm">
    @csrf

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-700 mb-1">Thêm Sản Phẩm Mới</h5>
            <small style="color:var(--text-muted);">Điền đầy đủ thông tin sản phẩm</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Lưu Sản Phẩm
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">
        {{-- ─── Main Column ─── --}}
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Thông Tin Cơ Bản</h6>
                <div class="mb-3">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Tên sản phẩm...">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand_id" class="form-select">
                            <option value="">Không có thương hiệu</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả ngắn</label>
                    <textarea name="short_description" class="form-control" rows="2" placeholder="Mô tả ngắn hiển thị trong danh sách..." maxlength="500">{{ old('short_description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả chi tiết</label>
                    <textarea name="description" class="form-control" rows="6" id="descriptionEditor" placeholder="Mô tả chi tiết sản phẩm...">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- Images --}}
            <div class="admin-card mb-4">
                <h6 class="fw-700 mb-4">Hình Ảnh Sản Phẩm</h6>
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--text-muted);margin-bottom:12px;display:block;"></i>
                    <div style="font-weight:600;margin-bottom:4px;">Kéo thả hoặc click để chọn ảnh</div>
                    <small style="color:var(--text-muted);">JPG, PNG, WebP — tối đa 3MB/ảnh. Ảnh đầu tiên sẽ là ảnh chính.</small>
                </div>
                <input type="file" name="images[]" id="imageInput" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
                <div id="imagePreviewContainer" class="d-flex flex-wrap gap-3 mt-3"></div>
            </div>
        </div>

        {{-- ─── Side Column ─── --}}
        <div class="col-lg-4">
            {{-- Pricing --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Giá & Kho Hàng</h6>
                <div class="mb-3">
                    <label class="form-label">Giá gốc <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="price" class="form-control" required value="{{ old('price') }}" min="0" step="1000" placeholder="0">
                        <span class="input-group-text" style="font-size:13px;background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">đ</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Giá khuyến mãi</label>
                    <div class="input-group">
                        <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price') }}" min="0" step="1000" placeholder="Để trống nếu không giảm">
                        <span class="input-group-text" style="font-size:13px;background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">đ</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" required value="{{ old('stock', 0) }}" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="Tự động nếu để trống">
                </div>
            </div>

            {{-- Status --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Trạng Thái</h6>
                <div class="mb-3">
                    <label class="form-label">Trạng thái bán hàng</label>
                    <select name="status" class="form-select">
                        <option value="draft">Nháp</option>
                        <option value="active" selected>Đang bán</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="isFeatured">Sản phẩm nổi bật</label>
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="admin-card mb-4 admin-form">
                <h6 class="fw-700 mb-4">Thông Tin Thêm</h6>
                <div class="mb-3">
                    <label class="form-label">Chất liệu</label>
                    <input type="text" name="material" class="form-control" value="{{ old('material') }}" placeholder="Da thật, vải canvas...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Trọng lượng (kg)</label>
                    <input type="number" name="weight" class="form-control" value="{{ old('weight') }}" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>

            {{-- SEO --}}
            <div class="admin-card admin-form">
                <h6 class="fw-700 mb-4">SEO</h6>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}" placeholder="Để trống = dùng tên SP">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3" placeholder="Mô tả cho SEO...">{{ old('meta_description') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">Hủy</a>
        <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Sản Phẩm</button>
    </div>
</form>

@endsection

@section('scripts')
<script>
function previewImages(input) {
    const container = document.getElementById('imagePreviewContainer');
    const files = Array.from(input.files);
    container.innerHTML = '';
    files.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" class="preview-img">
                ${i === 0 ? '<div style="position:absolute;bottom:4px;left:4px;background:var(--gold);color:#000;font-size:9px;padding:2px 6px;border-radius:4px;font-weight:700;">CHÍNH</div>' : ''}
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Drag & drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--gold)'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor = '';
    const input = document.getElementById('imageInput');
    input.files = e.dataTransfer.files;
    previewImages(input);
});
</script>
@endsection
