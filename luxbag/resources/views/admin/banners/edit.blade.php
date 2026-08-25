@extends('layouts.admin')

@section('title', 'Sửa Banner')
@section('page-title', 'Chỉnh Sửa Banner')
@section('breadcrumb', 'Banners / Chỉnh sửa')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">Chỉnh Sửa Banner</h6>

            {{-- Current Image --}}
            <div class="mb-4">
                <label class="form-label">Ảnh hiện tại</label>
                <div>
                    <img src="{{ asset('storage/'.$banner->image) }}" alt=""
                         style="max-width:100%;height:160px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                </div>
            </div>

            <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

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
                    <label class="form-label">Ảnh mới (nếu muốn thay)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phụ đề</label>
                    <textarea name="subtitle" class="form-control" rows="2">{{ old('subtitle', $banner->subtitle) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link</label>
                    <input type="text" name="link" class="form-control" value="{{ old('link', $banner->link) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Vị trí</label>
                        <select name="position" class="form-select">
                            <option value="hero" {{ $banner->position == 'hero' ? 'selected' : '' }}>Hero</option>
                            <option value="sidebar" {{ $banner->position == 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                            <option value="popup" {{ $banner->position == 'popup' ? 'selected' : '' }}>Popup</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Thứ tự</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order) }}" min="0">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $banner->status == 'active' ? 'selected' : '' }}>Hiển thị</option>
                        <option value="inactive" {{ $banner->status == 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.banners.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
