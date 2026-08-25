@extends('layouts.admin')

@section('title', 'Cài Đặt Hệ Thống')
@section('page-title', 'Cài Đặt')
@section('breadcrumb', 'Cài Đặt')

@section('content')

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-700 mb-0">Cài Đặt Hệ Thống</h5>
        <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Cài Đặt</button>
    </div>

    {{-- ─── Logo & Thương Hiệu ─── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="admin-card admin-form">
                <h6 class="fw-700 mb-4"><i class="fas fa-palette me-2" style="color:var(--gold);"></i>Thương Hiệu & Logo</h6>
                <div class="row g-4">
                    {{-- Logo chính (Navbar) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">Logo Navbar (Header)</label>
                        <div class="logo-upload-zone" id="logoNavbarZone">
                            @if(!empty($settings['site_logo']->value ?? null))
                                <div class="logo-preview-box" style="background: var(--dark);">
                                    <img src="{{ asset('storage/' . $settings['site_logo']->value) }}" alt="Logo" id="logoNavbarPreview">
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i>Logo đã tải lên</small>
                                    <label class="form-check mb-0" style="font-size:12px;">
                                        <input type="checkbox" name="remove_site_logo" value="1" class="form-check-input" style="width:14px;height:14px;">
                                        <span class="text-danger">Xóa logo</span>
                                    </label>
                                </div>
                            @else
                                <div class="logo-preview-box logo-preview-empty" onclick="document.getElementById('siteLogoInput').click()" style="background: var(--dark);">
                                    <div style="text-align:center;">
                                        <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:var(--gold);margin-bottom:8px;display:block;"></i>
                                        <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Click để tải logo lên</div>
                                        <div style="font-size:11px;color:var(--text-muted);">PNG, WebP, SVG · Nền trong suốt</div>
                                    </div>
                                </div>
                                <div id="logoNavbarPreviewNew" style="display:none;">
                                    <div class="logo-preview-box mt-2" style="background: var(--dark);">
                                        <img id="logoNavbarPreviewImg" src="" alt="Preview">
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="site_logo" id="siteLogoInput" accept="image/*" style="display:none;"
                                   onchange="previewLogo(this, 'logoNavbarPreviewImg', 'logoNavbarPreviewNew')">
                            @if(!empty($settings['site_logo']->value ?? null))
                                <div class="mt-2">
                                    <button type="button" class="btn-admin-secondary btn-sm" onclick="document.getElementById('siteLogoInput').click()">
                                        <i class="fas fa-sync-alt me-1"></i>Thay đổi
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="form-text">Hiển thị trên thanh navbar. Khuyến nghị: chiều cao 40-50px, nền trong suốt (PNG).</div>
                    </div>

                    {{-- Logo footer --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">Logo Footer</label>
                        <div class="logo-upload-zone" id="logoFooterZone">
                            @if(!empty($settings['site_logo_footer']->value ?? null))
                                <div class="logo-preview-box" style="background: var(--dark);">
                                    <img src="{{ asset('storage/' . $settings['site_logo_footer']->value) }}" alt="Logo Footer" id="logoFooterPreview">
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i>Logo footer đã tải lên</small>
                                    <label class="form-check mb-0" style="font-size:12px;">
                                        <input type="checkbox" name="remove_site_logo_footer" value="1" class="form-check-input" style="width:14px;height:14px;">
                                        <span class="text-danger">Xóa logo</span>
                                    </label>
                                </div>
                            @else
                                <div class="logo-preview-box logo-preview-empty" onclick="document.getElementById('siteLogoFooterInput').click()" style="background: var(--dark);">
                                    <div style="text-align:center;">
                                        <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:var(--gold);margin-bottom:8px;display:block;"></i>
                                        <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Click để tải logo footer</div>
                                        <div style="font-size:11px;color:var(--text-muted);">Có thể khác logo chính · Nền trong suốt</div>
                                    </div>
                                </div>
                                <div id="logoFooterPreviewNew" style="display:none;">
                                    <div class="logo-preview-box mt-2" style="background: var(--dark);">
                                        <img id="logoFooterPreviewImg" src="" alt="Preview">
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="site_logo_footer" id="siteLogoFooterInput" accept="image/*" style="display:none;"
                                   onchange="previewLogo(this, 'logoFooterPreviewImg', 'logoFooterPreviewNew')">
                            @if(!empty($settings['site_logo_footer']->value ?? null))
                                <div class="mt-2">
                                    <button type="button" class="btn-admin-secondary btn-sm" onclick="document.getElementById('siteLogoFooterInput').click()">
                                        <i class="fas fa-sync-alt me-1"></i>Thay đổi
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="form-text">Hiển thị ở chân trang. Nếu không tải lên, sẽ dùng logo navbar hoặc tên cửa hàng.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ─── General ─── --}}
        <div class="col-lg-6">
            <div class="admin-card admin-form mb-4">
                <h6 class="fw-700 mb-4"><i class="fas fa-store me-2" style="color:var(--gold);"></i>Thông Tin Cửa Hàng</h6>
                <div class="mb-3">
                    <label class="form-label">Tên cửa hàng</label>
                    <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name']->value ?? 'Bloom & Bold' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="site_tagline" class="form-control" value="{{ $settings['site_tagline']->value ?? 'Túi Xách Cao Cấp' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="site_address" class="form-control" rows="2">{{ $settings['site_address']->value ?? '' }}</textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="site_phone" class="form-control" value="{{ $settings['site_phone']->value ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="site_email" class="form-control" value="{{ $settings['site_email']->value ?? '' }}">
                    </div>
                </div>
            </div>

            {{-- ─── Social ─── --}}
            <div class="admin-card admin-form">
                <h6 class="fw-700 mb-4"><i class="fas fa-share-alt me-2" style="color:var(--gold);"></i>Mạng Xã Hội</h6>
                <div class="mb-3">
                    <label class="form-label"><i class="fab fa-facebook me-2" style="color:#1877F2;"></i>Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control" value="{{ $settings['facebook_url']->value ?? '' }}" placeholder="https://facebook.com/...">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fab fa-instagram me-2" style="color:#E1306C;"></i>Instagram URL</label>
                    <input type="url" name="instagram_url" class="form-control" value="{{ $settings['instagram_url']->value ?? '' }}" placeholder="https://instagram.com/...">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fab fa-tiktok me-2" style="color:#000;"></i>TikTok URL</label>
                    <input type="url" name="tiktok_url" class="form-control" value="{{ $settings['tiktok_url']->value ?? '' }}" placeholder="https://tiktok.com/...">
                </div>
            </div>
        </div>

        {{-- ─── Shipping & Payment ─── --}}
        <div class="col-lg-6">
            <div class="admin-card admin-form mb-4">
                <h6 class="fw-700 mb-4"><i class="fas fa-shipping-fast me-2" style="color:var(--gold);"></i>Vận Chuyển</h6>
                <div class="mb-3">
                    <label class="form-label">Miễn phí vận chuyển từ (đ)</label>
                    <div class="input-group">
                        <input type="number" name="free_shipping_threshold" class="form-control"
                               value="{{ $settings['free_shipping_threshold']->value ?? 500000 }}" min="0" step="10000">
                        <span class="input-group-text" style="background:var(--body-bg);border:1px solid var(--border);border-radius:0 8px 8px 0;">đ</span>
                    </div>
                </div>
            </div>

            <div class="admin-card admin-form mb-4">
                <h6 class="fw-700 mb-4"><i class="fas fa-percentage me-2" style="color:var(--gold);"></i>Thuế & Phí</h6>
                <div class="mb-3">
                    <label class="form-label">Thuế VAT (%)</label>
                    <input type="number" name="vat_rate" class="form-control"
                           value="{{ $settings['vat_rate']->value ?? 0 }}" min="0" max="100" step="0.5">
                </div>
            </div>

            <div class="admin-card admin-form">
                <h6 class="fw-700 mb-4"><i class="fas fa-envelope me-2" style="color:var(--gold);"></i>Email Thông Báo</h6>
                <div class="mb-3">
                    <label class="form-label">Email admin nhận thông báo đơn hàng</label>
                    <input type="email" name="admin_email" class="form-control"
                           value="{{ $settings['admin_email']->value ?? '' }}" placeholder="admin@bloomandbold.vn">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="email_order_notify" value="1"
                           {{ ($settings['email_order_notify']->value ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-600">Gửi email khi có đơn hàng mới</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="email_customer_notify" value="1"
                           {{ ($settings['email_customer_notify']->value ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-600">Gửi email xác nhận cho khách</label>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Chatbot AI ─── --}}
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="admin-card admin-form">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="fw-700 mb-0">
                        <span style="font-size:20px; margin-right:8px;">🤖</span>
                        Chatbot AI Tư Vấn Túi Xách
                    </h6>
                    <span class="badge" style="background:linear-gradient(135deg,#c8a96e,#a07840);color:#fff;padding:6px 14px;border-radius:20px;font-size:12px;">
                        <i class="fas fa-magic me-1"></i>LuxBot
                    </span>
                </div>

                <div class="row g-3">
                    {{-- Enable --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:var(--body-bg);border:1px solid var(--border);">
                            <div>
                                <div class="fw-600">Kích hoạt Chatbot</div>
                                <div class="text-muted" style="font-size:13px;">Hiển thị widget chatbot ở góc phải màn hình trên tất cả các trang</div>
                            </div>
                            <div class="form-check form-switch mb-0" style="transform:scale(1.3);">
                                <input class="form-check-input" type="checkbox" name="chatbot_enabled" id="chatbotEnabled" value="1"
                                       {{ ($settings['chatbot_enabled']->value ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Name & Color --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">Tên Chatbot</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--body-bg);border:1px solid var(--border);">
                                <i class="fas fa-robot" style="color:var(--gold);"></i>
                            </span>
                            <input type="text" name="chatbot_name" class="form-control"
                                   value="{{ $settings['chatbot_name']->value ?? 'LuxBot' }}"
                                   placeholder="LuxBot" maxlength="30">
                        </div>
                        <div class="form-text">Tên sẽ hiển thị trên header của chat widget</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Màu Sắc Chủ Đạo</label>
                        <div class="d-flex gap-3 align-items-center">
                            <input type="color" name="chatbot_color" class="form-control form-control-color"
                                   id="chatbotColor"
                                   value="{{ $settings['chatbot_color']->value ?? '#c8a96e' }}"
                                   style="width:60px;height:40px;cursor:pointer;">
                            <input type="text" id="chatbotColorText" class="form-control"
                                   value="{{ $settings['chatbot_color']->value ?? '#c8a96e' }}"
                                   readonly style="font-family:monospace;color:var(--text-muted);">
                            {{-- Preset colors --}}
                            <div class="d-flex gap-2">
                                @foreach(['#c8a96e'=>'Vàng gold','#1a1a2e'=>'Xanh đêm','#e53e3e'=>'Đỏ','#38a169'=>'Xanh lá','#805ad5'=>'Tím'] as $hex => $label)
                                    <button type="button" class="cb-color-preset"
                                            data-color="{{ $hex }}"
                                            title="{{ $label }}"
                                            style="width:26px;height:26px;border-radius:50%;background:{{ $hex }};border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2);cursor:pointer;flex-shrink:0;"></button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Max products --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600">Số Sản Phẩm Gợi Ý Tối Đa</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="chatbot_max_products" id="maxProductsSlider"
                                   min="1" max="5" step="1"
                                   value="{{ $settings['chatbot_max_products']->value ?? 3 }}"
                                   class="form-range" style="flex:1;">
                            <span id="maxProductsVal" class="fw-700"
                                  style="color:var(--gold);font-size:20px;min-width:24px;text-align:center;">
                                {{ $settings['chatbot_max_products']->value ?? 3 }}
                            </span>
                        </div>
                        <div class="form-text">Mỗi lần gợi ý sẽ hiện tối đa bao nhiêu sản phẩm (1-5)</div>
                    </div>

                    {{-- Greeting --}}
                    <div class="col-12">
                        <label class="form-label fw-600">Lời Chào Khi Mở Chat</label>
                        <textarea name="chatbot_greeting" class="form-control" rows="3"
                                  placeholder="Ví dụ: Xin chào! Tôi có thể giúp bạn tìm túi xách phù hợp...">{{ $settings['chatbot_greeting']->value ?? '' }}</textarea>
                        <div class="form-text">Tin nhắn đầu tiên chatbot gửi cho khách hàng khi mở chat. Hỗ trợ emoji 😊</div>
                    </div>

                    {{-- Preview --}}
                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background:var(--body-bg);border:1px solid var(--border);">
                            <div class="fw-600 mb-2" style="font-size:13px;color:var(--text-muted);">
                                <i class="fas fa-eye me-1"></i>Xem trước widget
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--gold),#a07840);display:flex;align-items:center;justify-content:center;font-size:22px;">👜</div>
                                <div>
                                    <div class="fw-700" id="previewBotName">{{ $settings['chatbot_name']->value ?? 'LuxBot' }}</div>
                                    <div class="text-muted" style="font-size:12px;">● Đang hoạt động · Tư vấn túi xách</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn-admin-primary"><i class="fas fa-save me-1"></i>Lưu Tất Cả Cài Đặt</button>
    </div>
</form>

<script>
// Color picker sync
const colorPicker = document.getElementById('chatbotColor');
const colorText   = document.getElementById('chatbotColorText');
colorPicker.addEventListener('input', () => { colorText.value = colorPicker.value; });

document.querySelectorAll('.cb-color-preset').forEach(btn => {
    btn.addEventListener('click', () => {
        const c = btn.dataset.color;
        colorPicker.value = c;
        colorText.value   = c;
    });
});

// Range slider
const slider   = document.getElementById('maxProductsSlider');
const sliderVal = document.getElementById('maxProductsVal');
slider.addEventListener('input', () => { sliderVal.textContent = slider.value; });

// Bot name preview
const nameInput = document.querySelector('input[name="chatbot_name"]');
const preview   = document.getElementById('previewBotName');
nameInput?.addEventListener('input', () => { if (preview) preview.textContent = nameInput.value || 'LuxBot'; });

// Logo preview
function previewLogo(input, imgId, wrapperId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const wrapper = document.getElementById(wrapperId);
            if (img) {
                img.src = e.target.result;
            }
            if (wrapper) {
                wrapper.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

