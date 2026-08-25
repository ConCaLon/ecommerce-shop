@extends('layouts.admin')

@section('title', 'Tạo Flash Sale')
@section('page-title', 'Tạo Flash Sale')
@section('breadcrumb', 'Flash Sale / Tạo mới')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="admin-card admin-form">
            <h6 class="fw-700 mb-4">⚡ Tạo Flash Sale Mới</h6>
            <form method="POST" action="{{ route('admin.flash-sales.store') }}">
                @csrf
                @if($errors->any())
                <div class="alert alert-danger mb-4"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Tên Flash Sale <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="VD: Flash Sale Cuối Tuần">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at') }}">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Tắt</option>
                    </select>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.flash-sales.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Tạo Flash Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
