@extends('layouts.admin')

@section('title', 'Thêm Banner')
@section('page-title', 'Thêm Banner Mới')
@section('breadcrumb', 'Banners / Thêm mới')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">Thêm Banner Mới</h6>
            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
                @csrf

                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Ảnh Banner <span class="text-danger">*</span></label>
                    <div class="upload-zone" onclick="document.getElementById('bannerImg').click()">
                        <i class="fas fa-image" style="font-size:32px;color:var(--text-muted);margin-bottom:12px;display:block;"></i>
                        <div style="font-weight:600;margin-bottom:4px;">Click để chọn ảnh</div>
                        <small style="color:var(--text-muted);">Khuyến nghị: 1920×600px. JPG/PNG/WebP</small>
                    </div>
                    <input type="file" name="image" id="bannerImg" accept="image/*" required style="display:none;" onchange="previewBanner(this)">
                    <img id="bannerPreview" style="display:none;width:100%;border-radius:8px;margin-top:10px;object-fit:cover;height:180px;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" placeholder="Tiêu đề hiển thị trên banner">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phụ đề</label>
                    <textarea name="subtitle" class="form-control" rows="2" placeholder="Mô tả ngắn..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link khi click</label>
                    <input type="text" name="link" class="form-control" placeholder="/san-pham hoặc https://...">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Vị trí</label>
                        <select name="position" class="form-select">
                            <option value="hero">Trang chủ (Hero)</option>
                            <option value="sidebar">Sidebar</option>
                            <option value="popup">Popup</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Hiển thị</option>
                            <option value="inactive">Ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.banners.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Tạo Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('bannerPreview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
