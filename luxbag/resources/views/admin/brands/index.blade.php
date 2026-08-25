@extends('layouts.admin')

@section('title', 'Thương Hiệu')
@section('page-title', 'Thương Hiệu')
@section('breadcrumb', 'Thương Hiệu')

@section('content')
<div class="row g-4">
    {{-- ─── Create Form ─── --}}
    <div class="col-lg-4">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Thêm Thương Hiệu Mới</h6>
            <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Gucci, Louis Vuitton...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả thương hiệu..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active">Hiển thị</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
                <button type="submit" class="btn-admin-primary w-100"><i class="fas fa-plus me-1"></i>Tạo Thương Hiệu</button>
            </form>
        </div>
    </div>

    {{-- ─── List ─── --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Danh Sách Thương Hiệu ({{ $brands->total() }})</h6>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Thương hiệu</th>
                            <th>Website</th>
                            <th>Sản phẩm</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($brand->logo)
                                        <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}"
                                             style="width:36px;height:36px;border-radius:6px;object-fit:contain;border:1px solid var(--border);background:#fff;padding:2px;">
                                    @else
                                        <div style="width:36px;height:36px;border-radius:6px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-certificate" style="color:var(--text-muted);font-size:14px;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;">{{ $brand->name }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $brand->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:12px;">
                                @if($brand->website)
                                    <a href="{{ $brand->website }}" target="_blank" style="color:var(--gold);">{{ $brand->website }}</a>
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;font-size:13px;font-weight:600;">{{ $brand->products_count }}</td>
                            <td><span class="status-badge {{ $brand->status }}">{{ $brand->status === 'active' ? 'Hiển thị' : 'Ẩn' }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-admin-secondary"
                                        onclick="openEditBrand({{ $brand->id }}, '{{ addslashes($brand->name) }}', '{{ $brand->website }}', '{{ $brand->status }}')"
                                        title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-admin-danger"
                                            onclick="return confirm('Xóa thương hiệu này?')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">Chưa có thương hiệu nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($brands->hasPages())
            <div class="mt-4 admin-pagination">{{ $brands->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border);">
                <h6 class="modal-title fw-700">Chỉnh Sửa Thương Hiệu</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBrandForm" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editBrandName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo mới</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" id="editBrandWebsite" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" id="editBrandStatus" class="form-select">
                            <option value="active">Hiển thị</option>
                            <option value="inactive">Ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openEditBrand(id, name, website, status) {
    document.getElementById('editBrandForm').action = `/admin/thuong-hieu/${id}`;
    document.getElementById('editBrandName').value = name;
    document.getElementById('editBrandWebsite').value = website || '';
    document.getElementById('editBrandStatus').value = status;
    new bootstrap.Modal(document.getElementById('editBrandModal')).show();
}
</script>
@endsection
