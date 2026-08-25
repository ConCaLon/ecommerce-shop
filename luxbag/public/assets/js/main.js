/**
 * LuxBag — Main JavaScript
 */

const LuxBag = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,

    init() {
        this.initPageLoader();
        this.initNavbarScroll();
        this.initBackToTop();
        this.initCartActions();
        this.initWishlistActions();
        this.initQuantityControls();
        this.initGallery();
        this.initToasts();
        this.initAjaxSetup();
        this.initAnimations();
    },

    // ─── Page Loader ───────────────────────────────────────────────
    initPageLoader() {
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => loader.remove(), 400);
            }
        });
    },

    // ─── Navbar Scroll Effect ──────────────────────────────────────
    initNavbarScroll() {
        const nav = document.getElementById('mainNav');
        if (!nav) return;

        const handler = () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        };
        window.addEventListener('scroll', handler, { passive: true });
        handler();
    },

    // ─── Back to Top ───────────────────────────────────────────────
    initBackToTop() {
        const btn = document.getElementById('backToTop');
        if (!btn) return;

        window.addEventListener('scroll', () => {
            btn.classList.toggle('visible', window.scrollY > 400);
        }, { passive: true });

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    },

    // ─── AJAX Setup ────────────────────────────────────────────────
    initAjaxSetup() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': this.csrfToken }
        });
    },

    // ─── Cart Actions ──────────────────────────────────────────────
    initCartActions() {
        // Add to cart
        $(document).on('click', '.btn-add-to-cart, [data-action="add-to-cart"]', (e) => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            const productId = btn.data('product-id') || btn.closest('[data-product-id]').data('product-id');
            const variantId = this.getSelectedVariant();
            const qty = parseInt($('#quantity').val() || 1);

            if (!productId) return;

            const originalHtml = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Đang thêm...').prop('disabled', true);

            $.post('/gio-hang/them', {
                product_id: productId,
                variant_id: variantId || null,
                quantity: qty,
            })
            .done((res) => {
                if (res.success) {
                    this.updateCartBadge(res.cart_count);
                    this.showToast(res.message, 'success');
                    btn.html('<i class="fas fa-check"></i> Đã thêm!');
                    setTimeout(() => btn.html(originalHtml).prop('disabled', false), 2000);
                }
            })
            .fail((xhr) => {
                const msg = xhr.responseJSON?.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                this.showToast(msg, 'error');
                btn.html(originalHtml).prop('disabled', false);
            });
        });

        // Update quantity in cart
        $(document).on('change', '.cart-qty-input', (e) => {
            const input = $(e.currentTarget);
            const cartId = input.data('cart-id');
            const qty = parseInt(input.val());

            if (qty < 1) { input.val(1); return; }

            $.ajax({
                url: `/gio-hang/cap-nhat/${cartId}`,
                method: 'PATCH',
                data: { quantity: qty },
            })
            .done((res) => {
                if (res.success) {
                    $(`[data-item-subtotal="${cartId}"]`).text(res.item_subtotal);
                    $('#cartSubtotal').text(res.cart_subtotal);
                    $('#cartDiscount').text(res.discount);
                    $('#cartTotal').text(res.total);
                    this.updateCartBadge(res.cart_count);
                }
            })
            .fail((xhr) => {
                this.showToast(xhr.responseJSON?.message || 'Lỗi cập nhật', 'error');
                input.val(input.data('original-value'));
            });
        });

        // Remove from cart
        $(document).on('click', '[data-action="remove-cart"]', (e) => {
            e.preventDefault();
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;

            const btn = $(e.currentTarget);
            const cartId = btn.data('cart-id');

            $.ajax({ url: `/gio-hang/xoa/${cartId}`, method: 'DELETE' })
            .done((res) => {
                if (res.success) {
                    btn.closest('.cart-item').fadeOut(300, function() { $(this).remove(); });
                    this.updateCartBadge(res.cart_count);
                    this.showToast('Đã xóa sản phẩm', 'success');
                }
            });
        });
    },

    getSelectedVariant() {
        return $('[name="variant_id"]:checked').val() ||
               $('.variant-btn.selected').data('variant-id') || null;
    },

    updateCartBadge(count) {
        const badge = document.getElementById('cartCount');
        if (!badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    },

    // ─── Wishlist Actions ──────────────────────────────────────────
    initWishlistActions() {
        $(document).on('click', '[data-action="wishlist-toggle"]', (e) => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            const productId = btn.data('product-id');

            if (!$('meta[name="user-logged-in"]').length) {
                window.location.href = '/dang-nhap';
                return;
            }

            $.post(`/yeu-thich/them/${productId}`)
            .done((res) => {
                if (res.success) {
                    btn.toggleClass('wishlisted', res.action === 'added');
                    this.showToast(res.message, 'success');
                    const badge = document.getElementById('wishlistCount');
                    if (badge) {
                        badge.textContent = res.count;
                        badge.style.display = res.count > 0 ? 'flex' : 'none';
                    }
                }
            });
        });
    },

    // ─── Quantity Controls ─────────────────────────────────────────
    initQuantityControls() {
        $(document).on('click', '.qty-btn', (e) => {
            const btn = $(e.currentTarget);
            const input = btn.siblings('.qty-input');
            const action = btn.data('action');
            let val = parseInt(input.val()) || 1;
            const max = parseInt(input.attr('max')) || 99;
            const min = parseInt(input.attr('min')) || 1;

            if (action === 'increase') val = Math.min(val + 1, max);
            if (action === 'decrease') val = Math.max(val - 1, min);

            input.val(val).trigger('change');
        });
    },

    // ─── Product Gallery ───────────────────────────────────────────
    initGallery() {
        $(document).on('click', '.product-thumb', (e) => {
            const thumb = $(e.currentTarget);
            const imgUrl = thumb.data('full');

            $('#mainProductImage').attr('src', imgUrl);
            $('.product-thumb').removeClass('active');
            thumb.addClass('active');
        });

        // Variant selection
        $(document).on('click', '.variant-btn', (e) => {
            const btn = $(e.currentTarget);
            if (btn.hasClass('out-of-stock')) return;

            btn.closest('.variant-group').find('.variant-btn').removeClass('selected');
            btn.addClass('selected');

            // Update price if modifier exists
            const modifier = parseFloat(btn.data('price-modifier') || 0);
            if (modifier) {
                // Update displayed price
            }

            // Update stock display
            const stock = btn.data('stock');
            if (stock !== undefined) {
                const stockEl = document.getElementById('stockCount');
                if (stockEl) stockEl.textContent = stock;
            }
        });

        $(document).on('click', '.color-btn', (e) => {
            const btn = $(e.currentTarget);
            btn.closest('.color-group').find('.color-btn').removeClass('selected');
            btn.addClass('selected');

            // Update variant image if available
            const img = btn.data('image');
            if (img) $('#mainProductImage').attr('src', img);
        });
    },

    // ─── Toast Notification ────────────────────────────────────────
    showToast(message, type = 'success') {
        const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle', warning: 'exclamation-triangle' };
        const colors = { success: 'var(--green)', error: 'var(--red)', info: 'var(--blue)', warning: 'var(--orange)' };

        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast toast-luxbag ${type} align-items-center show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${icons[type] || 'info-circle'} me-2" style="color:${colors[type]}"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('beforeend', html);
        const toastEl = document.getElementById(id);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    },

    // ─── Scroll Animations ─────────────────────────────────────────
    initAnimations() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity .6s ease, transform .6s ease';
            observer.observe(el);
        });
    },

    initToasts() {
        // Already initialized via Blade @yield scripts
    },
};

// ─── Countdown Timer ───────────────────────────────────────────────
function initCountdown(endTime) {
    const update = () => {
        const diff = Math.max(0, endTime - Math.floor(Date.now() / 1000));
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;

        const pad = n => String(n).padStart(2, '0');

        const el = id => document.getElementById(id);
        if (el('countdown-h')) el('countdown-h').textContent = pad(h);
        if (el('countdown-m')) el('countdown-m').textContent = pad(m);
        if (el('countdown-s')) el('countdown-s').textContent = pad(s);

        if (diff <= 0) clearInterval(timer);
    };

    update();
    const timer = setInterval(update, 1000);
    return timer;
}

// ─── Price Range Filter ────────────────────────────────────────────
function initPriceRange() {
    const minInput = document.getElementById('priceMin');
    const maxInput = document.getElementById('priceMax');
    const minLabel = document.getElementById('priceMinLabel');
    const maxLabel = document.getElementById('priceMaxLabel');

    if (!minInput) return;

    const format = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';

    const update = () => {
        if (minLabel) minLabel.textContent = format(minInput.value);
        if (maxLabel) maxLabel.textContent = format(maxInput.value);
    };

    minInput.addEventListener('input', update);
    if (maxInput) maxInput.addEventListener('input', update);
    update();
}

// ─── Image Preview Upload ──────────────────────────────────────────
function initImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', () => {
        preview.innerHTML = '';
        [...input.files].forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;margin:4px;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    LuxBag.init();
    initPriceRange();
});

// Export globally
window.LuxBag = LuxBag;
window.initCountdown = initCountdown;
window.initImagePreview = initImagePreview;
