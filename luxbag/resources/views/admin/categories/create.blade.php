@extends('layouts.admin')

@section('title', 'Thêm Danh Mục')
@section('page-title', 'Thêm Danh Mục Mới')
@section('breadcrumb', '<a href="'.route('admin.categories.index').'">Danh Mục</a> / Thêm Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Thêm Danh Mục Mới</h6>
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Túi xách nữ" value="{{ old('name') }}">
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
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn...">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ảnh danh mục</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active">Hiển thị</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-plus me-1"></i>Tạo Danh Mục</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
