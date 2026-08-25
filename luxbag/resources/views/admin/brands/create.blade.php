@extends('layouts.admin')

@section('title', 'Thêm Thương Hiệu')
@section('page-title', 'Thêm Thương Hiệu Mới')
@section('breadcrumb', '<a href="'.route('admin.brands.index').'">Thương Hiệu</a> / Thêm Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Thêm Thương Hiệu Mới</h6>
            <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Bloom & Bold Original" value="{{ old('name') }}">
                    @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://..." value="{{ old('website') }}">
                    @error('website') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả về thương hiệu...">{{ old('description') }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active">Hiển thị</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-plus me-1"></i>Tạo Thương Hiệu</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn-admin-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
