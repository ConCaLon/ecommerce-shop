@extends('layouts.admin')

@section('title', $category->name)
@section('page-title', $category->name)
@section('breadcrumb', '<a href="'.route('admin.categories.index').'">Danh Mục</a> / Chi Tiết')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card text-center">
            @if($category->image)
                <img src="{{ asset('storage/categories/'.$category->image) }}" alt="{{ $category->name }}"
                     style="width:120px;height:120px;object-fit:cover;border-radius:12px;border:2px solid var(--border);margin-bottom:16px;">
            @else
                <div style="width:80px;height:80px;border-radius:12px;background:rgba(201,168,76,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-tags" style="font-size:28px;color:var(--gold);"></i>
                </div>
            @endif
            <h5 class="fw-700 mb-1">{{ $category->name }}</h5>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">/{{ $category->slug }}</div>
            <span class="status-badge {{ $category->status }}">{{ $category->status === 'active' ? 'Hiển thị' : 'Ẩn' }}</span>
            <div class="mt-4 d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-admin-primary">
                    <i class="fas fa-edit me-1"></i>Chỉnh Sửa
                </a>
                <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Thông Tin Danh Mục</h6>
            <table class="admin-table">
                <tr>
                    <td style="width:35%;font-weight:600;color:var(--text-muted);font-size:13px;">Danh mục cha</td>
                    <td>{{ $category->parent?->name ?? '— Danh mục gốc —' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:600;color:var(--text-muted);font-size:13px;">Mô tả</td>
                    <td style="font-size:13px;">{{ $category->description ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="font-weight:600;color:var(--text-muted);font-size:13px;">Thứ tự</td>
                    <td>{{ $category->sort_order }}</td>
                </tr>
                <tr>
                    <td style="font-weight:600;color:var(--text-muted);font-size:13px;">Số sản phẩm</td>
                    <td>{{ $category->products()->count() }}</td>
                </tr>
                <tr>
                    <td style="font-weight:600;color:var(--text-muted);font-size:13px;">Ngày tạo</td>
                    <td style="font-size:13px;">{{ $category->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="font-weight:600;color:var(--text-muted);font-size:13px;">Cập nhật</td>
                    <td style="font-size:13px;">{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
