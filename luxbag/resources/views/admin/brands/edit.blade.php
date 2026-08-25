@extends('layouts.admin')

@section('title', 'Sửa Thương Hiệu')
@section('page-title', 'Chỉnh Sửa Thương Hiệu')
@section('breadcrumb', '<a href="'.route('admin.brands.index').'">Thương Hiệu</a> / Chỉnh Sửa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Chỉnh Sửa: {{ $brand->name }}</h6>
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="admin-form">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $brand->name) }}">
                    @error('name') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                @if($brand->logo)
                <div class="mb-3">
                    <label class="form-label">Logo hiện tại</label>
                    <div>
                        <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}"
                             style="width:80px;height:80px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:#fff;padding:6px;">
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">{{ $brand->logo ? 'Thay logo mới' : 'Logo' }}</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://..." value="{{ old('website', $brand->website) }}">
                    @error('website') <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $brand->description) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $brand->status === 'active' ? 'selected' : '' }}>Hiển thị</option>
                        <option value="inactive" {{ $brand->status === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Thay Đổi</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn-admin-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
