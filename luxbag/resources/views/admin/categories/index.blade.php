@extends('layouts.admin')

@section('title', 'Danh Mục')
@section('page-title', 'Danh Mục')
@section('breadcrumb', 'Danh Mục')

@section('content')

<div class="row g-4">
    {{-- ─── Create Form ─── --}}
    <div class="col-lg-4">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Thêm Danh Mục Mới</h6>
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="admin-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Túi xách nữ">
                </div>
                <div class="mb-3">
                    <label class="form-label">Danh mục cha</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— Danh mục gốc —</option>
                        @foreach($allCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ảnh danh mục</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active">Hiển thị</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
                <button type="submit" class="btn-admin-primary w-100">
                    <i class="fas fa-plus"></i> Tạo Danh Mục
                </button>
            </form>
        </div>
    </div>

    {{-- ─── List ─── --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-700 mb-0">Danh Sách ({{ $categories->total() }})</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Danh mục</th>
                            <th>Danh mục cha</th>
                            <th>Sản phẩm</th>
                            <th>Thứ tự</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($category->image)
                                        <img src="{{ asset('storage/categories/'.$category->image) }}"
                                             alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                                    @else
                                        <div style="width:36px;height:36px;border-radius:6px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-tags" style="color:var(--text-muted);font-size:14px;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;">{{ $category->name }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $category->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:13px;">{{ $category->parent?->name ?? '—' }}</td>
                            <td style="font-size:13px;text-align:center;">{{ $category->products_count }}</td>
                            <td style="font-size:13px;text-align:center;">{{ $category->sort_order }}</td>
                            <td><span class="status-badge {{ $category->status }}">{{ $category->status === 'active' ? 'Hiển thị' : 'Ẩn' }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-admin-secondary"
                                        onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->parent_id ?? 'null' }}, '{{ $category->status }}', {{ $category->sort_order }})"
                                        title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-admin-danger"
                                            onclick="return confirm('{{ $category->products_count > 0 ? 'Danh mục này có ' . $category->products_count . ' sản phẩm. Bạn có chắc muốn xóa?' : 'Xóa danh mục này?' }}')"
                                            title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                                Chưa có danh mục nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
            <div class="mt-4 admin-pagination">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--border);">
            <div class="modal-header" style="border-bottom:1px solid var(--border);">
                <h6 class="modal-title fw-700">Chỉnh Sửa Danh Mục</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh mục cha</label>
                        <select name="parent_id" id="editParent" class="form-select">
                            <option value="">— Danh mục gốc —</option>
                            @foreach($allCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh mới (nếu muốn thay)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thứ tự</label>
                        <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" id="editStatus" class="form-select">
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
function openEditModal(id, name, parentId, status, sortOrder) {
    document.getElementById('editForm').action = `/admin/danh-muc/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editStatus').value = status;
    document.getElementById('editSortOrder').value = sortOrder;
    const sel = document.getElementById('editParent');
    sel.value = parentId || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
