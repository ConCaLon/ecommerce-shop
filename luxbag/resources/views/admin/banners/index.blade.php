@extends('layouts.admin')

@section('title', 'Banners')
@section('page-title', 'Banners')
@section('breadcrumb', 'Banners')

@section('content')

<div class="row g-4">
    {{-- ─── Create Form ─── --}}
    <div class="col-lg-4">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">Thêm Banner Mới</h6>
            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" placeholder="Tiêu đề banner">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phụ đề</label>
                    <textarea name="subtitle" class="form-control" rows="2" placeholder="Mô tả ngắn..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ảnh <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <small style="color:var(--text-muted);font-size:11px;">Khuyến nghị: 1920×600px</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="link" class="form-control" placeholder="/san-pham hoặc URL đầy đủ">
                </div>
                <div class="mb-3">
                    <label class="form-label">Vị trí</label>
                    <select name="position" class="form-select">
                        <option value="hero">Trang chủ (Hero)</option>
                        <option value="sidebar">Sidebar</option>
                        <option value="popup">Popup</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Thứ tự</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Hiển thị</option>
                            <option value="inactive">Ẩn</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-admin-primary w-100"><i class="fas fa-plus me-1"></i>Tạo Banner</button>
            </form>
        </div>
    </div>

    {{-- ─── Banners List ─── --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Danh Sách Banners ({{ $banners->total() }})</h6>
            <div class="row g-3">
                @forelse($banners as $banner)
                <div class="col-12">
                    <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;display:flex;gap:0;">
                        <img src="{{ asset('storage/'.$banner->image) }}" alt="{{ $banner->title }}"
                             style="width:160px;height:90px;object-fit:cover;flex-shrink:0;">
                        <div style="padding:14px 16px;flex:1;display:flex;align-items:center;gap:16px;">
                            <div class="flex-1">
                                <div style="font-weight:700;font-size:13.5px;">{{ $banner->title ?? '(Không có tiêu đề)' }}</div>
                                @if($banner->subtitle)
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ Str::limit($banner->subtitle, 60) }}</div>
                                @endif
                                <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                                    <span style="background:var(--body-bg);padding:2px 8px;border-radius:4px;margin-right:6px;">{{ $banner->position }}</span>
                                    Thứ tự: {{ $banner->sort_order }}
                                </div>
                            </div>
                            <span class="status-badge {{ $banner->status }}">{{ $banner->status === 'active' ? 'Hiển thị' : 'Ẩn' }}</span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn-admin-secondary" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-admin-danger" onclick="return confirm('Xóa banner này?')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12" style="text-align:center;padding:40px;color:var(--text-muted);">
                    <i class="fas fa-image" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
                    Chưa có banner nào
                </div>
                @endforelse
            </div>
            @if($banners->hasPages())
            <div class="mt-4 admin-pagination">{{ $banners->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
