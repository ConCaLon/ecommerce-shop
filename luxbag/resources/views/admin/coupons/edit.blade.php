@extends('layouts.admin')

@section('title', 'Sửa Mã: '.$coupon->code)
@section('page-title', 'Chỉnh Sửa Mã Giảm Giá')
@section('breadcrumb', 'Mã Giảm Giá / Chỉnh sửa')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0">Chỉnh Sửa: <span style="color:var(--gold);font-family:monospace;">{{ $coupon->code }}</span></h5>
    <a href="{{ route('admin.coupons.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="admin-card admin-form">
            <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
                @csrf @method('PUT')
                @if($errors->any())
                <div class="alert alert-danger mb-4"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Loại giảm giá</label>
                        <select name="type" class="form-select" id="couponType" onchange="toggleValueLabel()">
                            <option value="percent" {{ $coupon->type == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                            <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giá trị <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="value" class="form-control" required value="{{ old('value', $coupon->value) }}" min="0" step="0.01">
                            <span class="input-group-text" id="valueSuffix" style="background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;font-size:13px;">
                                {{ $coupon->type === 'percent' ? '%' : 'đ' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giảm tối đa (đ)</label>
                        <input type="number" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" min="0" step="1000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đơn hàng tối thiểu</label>
                        <input type="number" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" min="0" step="1000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hết hạn</label>
                        <input type="datetime-local" name="expires_at" class="form-control"
                               value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $coupon->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ $coupon->status == 'inactive' ? 'selected' : '' }}>Tắt</option>
                            <option value="expired" {{ $coupon->status == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $coupon->description) }}" maxlength="255">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 justify-content-end">
                    <a href="{{ route('admin.coupons.index') }}" class="btn-admin-secondary">Hủy</a>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Thay Đổi</button>
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
</script>
@endsection
