@extends('layouts.admin')

@section('title', 'Tạo Mã Giảm Giá')
@section('page-title', 'Tạo Mã Giảm Giá')
@section('breadcrumb', 'Mã Giảm Giá / Tạo mới')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0">Tạo Mã Giảm Giá Mới</h5>
    <a href="{{ route('admin.coupons.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="admin-card admin-form">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf
                @if($errors->any())
                <div class="alert alert-danger mb-4"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required value="{{ old('code') }}"
                               placeholder="VD: SUMMER2024" style="text-transform:uppercase;font-family:monospace;font-size:15px;letter-spacing:2px;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="couponType" onchange="toggleValueLabel()">
                            <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                            <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" id="valueLabel">Giá trị <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="value" class="form-control" required value="{{ old('value') }}" min="0" step="0.01">
                            <span class="input-group-text" id="valueSuffix" style="background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;font-size:13px;">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giảm tối đa (đ)</label>
                        <input type="number" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount') }}" min="0" step="1000" placeholder="Không giới hạn">
                        <small style="color:var(--text-muted);font-size:11px;">Chỉ áp dụng cho loại phần trăm</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đơn hàng tối thiểu (đ)</label>
                        <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', 0) }}" min="0" step="1000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giới hạn lượt dùng</label>
                        <input type="number" name="max_uses" class="form-control" value="{{ old('max_uses') }}" min="1" placeholder="Không giới hạn">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giới hạn / người dùng</label>
                        <input type="number" name="per_user_limit" class="form-control" value="{{ old('per_user_limit', 1) }}" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày hết hạn</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="Mô tả ngắn cho mã giảm giá..." maxlength="255">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 justify-content-end">
                    <a href="{{ route('admin.coupons.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Tạo Mã Giảm Giá</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleValueLabel() {
    const type = document.getElementById('couponType').value;
    document.getElementById('valueSuffix').textContent = type === 'percent' ? '%' : 'đ';
}
toggleValueLabel();
</script>
@endsection
