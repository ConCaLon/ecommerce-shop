@extends('layouts.admin')

@section('title', 'Quản Lý Sản Phẩm')
@section('page-title', 'Sản Phẩm')
@section('breadcrumb', 'Sản Phẩm')

@section('content')

{{-- ─── Header ─── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-700 mb-1">Danh Sách Sản Phẩm</h5>
        <small style="color:var(--text-muted)">Tổng: {{ $products->total() }} sản phẩm</small>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Thêm Sản Phẩm
    </a>
</div>

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <div class="admin-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm tên, SKU..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang bán</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Ẩn</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-admin-primary flex-1">
                <i class="fas fa-search"></i> Lọc
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <form id="bulkForm" method="POST" action="{{ route('admin.products.bulk-delete') }}">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="selectAll" class="form-check-input" style="cursor:pointer;width:16px;height:16px;">
                <button type="button" id="bulkDeleteBtn" class="btn-admin-danger" style="display:none;" onclick="confirmBulkDelete()">
                    <i class="fas fa-trash"></i> Xóa đã chọn
                </button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Đã bán</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input product-check" style="width:16px;height:16px;cursor:pointer;">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($product->primaryImage)
                                    <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}"
                                         style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
                                @else
                                    <div style="width:44px;height:44px;border-radius:8px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;border:1px solid var(--border);">
                                        <i class="fas fa-box" style="color:var(--text-muted);"></i>
                                    </div>
                                @endif
                                <div>
                                    <div style="font-size:13.5px;font-weight:600;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $product->name }}
                                    </div>
                                    @if($product->is_featured)
                                        <span style="font-size:10px;background:rgba(201,168,76,.12);color:var(--gold);padding:2px 7px;border-radius:20px;font-weight:600;">⭐ Nổi bật</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);font-family:monospace;">{{ $product->sku }}</td>
                        <td style="font-size:13px;">{{ $product->category?->name ?? '—' }}</td>
                        <td>
                            <div style="font-size:13px;font-weight:700;color:var(--gold);">{{ vnd($product->current_price) }}</div>
                            @if($product->is_on_sale)
                                <div style="font-size:11px;color:var(--text-muted);text-decoration:line-through;">{{ vnd($product->price) }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:13px;font-weight:600;{{ $product->stock < 10 ? 'color:var(--red)' : '' }}">
                                {{ number_format($product->stock) }}
                            </span>
                            @if($product->stock < 10)
                                <i class="fas fa-exclamation-triangle" style="color:var(--red);font-size:11px;margin-left:4px;" title="Sắp hết hàng"></i>
                            @endif
                        </td>
                        <td style="font-size:13px;">{{ number_format($product->sold_count) }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ $product->status === 'active' ? 'checked' : '' }}
                                    data-ajax-toggle="{{ route('admin.products.toggle-status', $product) }}"
                                    {{ $product->status === 'draft' ? 'disabled' : '' }}>
                            </div>
                            <span class="status-badge {{ $product->status }}" style="font-size:11px;margin-top:4px;display:inline-flex;">
                                {{ match($product->status) { 'active' => 'Đang bán', 'inactive' => 'Đã ẩn', default => 'Nháp' } }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn-admin-secondary" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn-admin-danger"
                                    onclick="deleteProduct({{ $product->id }})"
                                    title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:60px 20px;color:var(--text-muted);">
                            <i class="fas fa-box-open" style="font-size:40px;margin-bottom:16px;opacity:.3;display:block;"></i>
                            Không tìm thấy sản phẩm nào
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Hidden single delete input --}}
        <input type="hidden" id="deleteProductId" name="_delete_id">
    </form>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small style="color:var(--text-muted);">
            Hiển thị {{ $products->firstItem() }}–{{ $products->lastItem() }} / {{ $products->total() }} sản phẩm
        </small>
        <div class="admin-pagination">
            {{ $products->links() }}
        </div>
    </div>
    @endif
</div>

{{-- Delete single form --}}
<form id="deleteSingleForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
// Select all
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.product-check').forEach(c => c.checked = this.checked);
    updateBulkBtn();
});
document.querySelectorAll('.product-check').forEach(c => {
    c.addEventListener('change', updateBulkBtn);
});
function updateBulkBtn() {
    const count = document.querySelectorAll('.product-check:checked').length;
    const btn = document.getElementById('bulkDeleteBtn');
    btn.style.display = count > 0 ? 'inline-flex' : 'none';
    btn.textContent = `Xóa ${count} sản phẩm đã chọn`;
    // Re-add icon
    btn.innerHTML = `<i class="fas fa-trash me-1"></i> Xóa ${count} sản phẩm đã chọn`;
}
function confirmBulkDelete() {
    const count = document.querySelectorAll('.product-check:checked').length;
    if (confirm(`Bạn có chắc muốn xóa ${count} sản phẩm đã chọn? Thao tác này không thể hoàn tác!`)) {
        document.getElementById('bulkForm').submit();
    }
}
function deleteProduct(id) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    const form = document.getElementById('deleteSingleForm');
    form.action = `/admin/san-pham/${id}`;
    form.submit();
}
</script>
@endsection
