@extends('layouts.admin')

@section('title', 'Đánh Giá Sản Phẩm')
@section('page-title', 'Đánh Giá')
@section('breadcrumb', 'Đánh Giá')

@section('content')

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="d-flex gap-3 align-items-center">
        <div class="d-flex gap-2">
            @foreach([''=>'Tất cả','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'] as $key => $label)
            <a href="{{ route('admin.reviews.index') }}{{ $key ? '?status='.$key : '' }}"
               style="padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
                      {{ request('status') == $key && ($key || !request('status'))
                        ? 'background:rgba(201,168,76,.15);color:var(--gold);border:1px solid rgba(201,168,76,.3);'
                        : 'background:var(--body-bg);color:var(--text-muted);border:1px solid var(--border);' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </form>
</div>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Người dùng</th>
                    <th>Đánh giá</th>
                    <th>Nội dung</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>
                        <div style="font-size:13px;font-weight:600;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $review->product?->name ?? '—' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ $review->user?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $review->user?->email }}</div>
                    </td>
                    <td>
                        <div style="color:#f39c12;font-size:13px;">
                            @for($i=1;$i<=5;$i++)
                                <i class="fas fa-star{{ $i > $review->rating ? '-half-alt' : '' }}"
                                   style="{{ $i <= $review->rating ? '' : 'opacity:.3' }}"></i>
                            @endfor
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $review->rating }}/5</div>
                    </td>
                    <td>
                        <div style="font-size:13px;max-width:200px;">
                            @if($review->title)<strong>{{ $review->title }}</strong><br>@endif
                            <span style="color:var(--text-muted);">{{ Str::limit($review->comment, 80) }}</span>
                        </div>
                        @if($review->admin_reply)
                        <div style="font-size:11px;margin-top:4px;color:var(--blue);font-style:italic;">
                            <i class="fas fa-reply me-1"></i>Đã phản hồi
                        </div>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $review->created_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="status-badge {{ $review->status }}">
                            {{ match($review->status) { 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', default => 'Chờ duyệt' } }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($review->status !== 'approved')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-admin-primary" style="padding:5px 10px;font-size:12px;" title="Duyệt">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            @if($review->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-admin-danger" style="padding:5px 10px;font-size:12px;" title="Từ chối">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn-admin-secondary" style="padding:5px 10px;font-size:12px;"
                                onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->admin_reply ?? '') }}')"
                                title="Phản hồi">
                                <i class="fas fa-reply"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger" style="padding:5px 10px;font-size:12px;" onclick="return confirm('Xóa đánh giá này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-star" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Không có đánh giá nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
    <div class="mt-4 admin-pagination">{{ $reviews->links() }}</div>
    @endif
</div>

{{-- Reply Modal --}}
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border);">
                <h6 class="modal-title fw-700">Phản Hồi Đánh Giá</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="replyForm" method="POST" class="admin-form">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Nội dung phản hồi</label>
                    <textarea name="reply" id="replyText" class="form-control" rows="4" placeholder="Nhập phản hồi..." maxlength="1000"></textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-admin-primary"><i class="fas fa-paper-plane me-1"></i>Gửi Phản Hồi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openReplyModal(reviewId, currentReply) {
    document.getElementById('replyForm').action = `/admin/danh-gia/${reviewId}/tra-loi`;
    document.getElementById('replyText').value = currentReply;
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}
</script>
@endsection
