@extends('layouts.admin')

@section('title', 'Sửa Danh Mục')
@section('page-title', 'Chỉnh Sửa Danh Mục')
@section('breadcrumb', '<a href="'.route('admin.categories.index').'">Danh Mục</a> / Chỉnh Sửa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Chỉnh Sửa: {{ $category->name }}</h6>
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="admin-form">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                    @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Danh mục cha</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— Danh mục gốc —</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                </div>
                @if($category->image)
                <div class="mb-3">
                    <label class="form-label">Ảnh hiện tại</label>
                    <div>
                        <img src="{{ asset('storage/categories/'.$category->image) }}" alt="{{ $category->name }}"
                             style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">{{ $category->image ? 'Thay ảnh mới' : 'Ảnh danh mục' }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $category->status === 'active' ? 'selected' : '' }}>Hiển thị</option>
                        <option value="inactive" {{ $category->status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Thay Đổi</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
