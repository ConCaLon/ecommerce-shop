@extends('layouts.admin')

@section('title', 'Sửa Flash Sale')
@section('page-title', 'Chỉnh Sửa Flash Sale')
@section('breadcrumb', 'Flash Sale / Chỉnh sửa')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">⚡ Chỉnh Sửa Flash Sale</h6>
            <form method="POST" action="{{ route('admin.flash-sales.update', $flashSale) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Tên Flash Sale <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $flashSale->name) }}">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at', $flashSale->starts_at->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at', $flashSale->ends_at->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ $flashSale->status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ $flashSale->status === 'inactive' ? 'selected' : '' }}>Tắt</option>
                    </select>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.flash-sales.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
