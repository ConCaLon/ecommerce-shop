@php
    $chatEnabled  = setting('chatbot_enabled', '1');
    $chatName     = setting('chatbot_name', 'LuxBot');
    $chatGreeting = setting('chatbot_greeting', 'Xin chào! Tôi có thể giúp bạn tìm túi xách phù hợp. Hãy cho tôi biết ngân sách hoặc loại túi bạn muốn nhé!');
    $chatColor    = setting('chatbot_color', '#8A6FD6');
@endphp

@if($chatEnabled)
{{-- ═══════════════════════════════════════════════════════════ --}}
{{--                   LUXBOT CHATBOT WIDGET                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<style>
/* ── Variables ── */
:root {
    --cb-primary:   {{ $chatColor }};
    --cb-primary-d: color-mix(in srgb, {{ $chatColor }} 80%, #000);
    --cb-primary-l: color-mix(in srgb, {{ $chatColor }} 20%, #fff);
    --cb-radius:    16px;
    --cb-shadow:    0 20px 60px rgba(0,0,0,.25);
}

/* ── Toggle Bubble ── */
#luxbot-toggle {
    position: fixed;
    bottom: 28px; right: 28px;
    width: 62px; height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--cb-primary) 0%, var(--cb-primary-d) 100%);
    box-shadow: 0 6px 24px rgba(0,0,0,.28), 0 0 0 0 rgba(200,169,110,.4);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    z-index: 9998;
    transition: transform .2s ease, box-shadow .2s ease;
    animation: cb-pulse 2.8s ease-in-out infinite;
}
#luxbot-toggle:hover { transform: scale(1.09); box-shadow: 0 10px 32px rgba(0,0,0,.35); }
#luxbot-toggle .cb-icon-chat { display: block; }
#luxbot-toggle .cb-icon-close { display: none; }
#luxbot-toggle.open .cb-icon-chat  { display: none; }
#luxbot-toggle.open .cb-icon-close { display: block; }
@keyframes cb-pulse {
    0%,100% { box-shadow: 0 6px 24px rgba(0,0,0,.28), 0 0 0 0 rgba(200,169,110,.4); }
    50%      { box-shadow: 0 6px 24px rgba(0,0,0,.28), 0 0 0 10px rgba(200,169,110,0); }
}

/* ── Notification Badge ── */
#luxbot-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: #e53e3e;
    color: #fff;
    font-size: 11px; font-weight: 700;
    width: 20px; height: 20px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
    animation: cb-bounce .6s ease;
}
@keyframes cb-bounce {
    0%,100% { transform: scale(1); }
    50% { transform: scale(1.3); }
}

/* ── Chat Window ── */
#luxbot-window {
    position: fixed;
    bottom: 102px; right: 28px;
    width: 380px;
    max-height: 580px;
    border-radius: var(--cb-radius);
    background: rgba(255,255,255,.97);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: var(--cb-shadow);
    border: 1px solid rgba(200,169,110,.2);
    display: flex; flex-direction: column;
    z-index: 9997;
    transform: translateY(20px) scale(.95);
    opacity: 0;
    pointer-events: none;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
    overflow: hidden;
}
#luxbot-window.open {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: all;
}

/* ── Header ── */
.cb-header {
    background: linear-gradient(135deg, var(--cb-primary) 0%, var(--cb-primary-d) 100%);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    flex-shrink: 0;
}
.cb-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.4);
}
.cb-header-info { flex: 1; }
.cb-header-name { color: #fff; font-weight: 700; font-size: 15px; line-height: 1.2; }
.cb-header-status { color: rgba(255,255,255,.85); font-size: 12px; display: flex; align-items: center; gap: 5px; }
.cb-dot { width: 7px; height: 7px; border-radius: 50%; background: #48bb78; animation: cb-blink 1.4s ease infinite; }
@keyframes cb-blink { 0%,100%{opacity:1}50%{opacity:.4} }

/* ── Messages ── */
.cb-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scroll-behavior: smooth;
}
.cb-messages::-webkit-scrollbar { width: 4px; }
.cb-messages::-webkit-scrollbar-track { background: transparent; }
.cb-messages::-webkit-scrollbar-thumb { background: rgba(200,169,110,.4); border-radius: 4px; }

/* ── Message Bubble ── */
.cb-msg { display: flex; gap: 8px; animation: cb-fadein .3s ease; max-width: 100%; }
.cb-msg.user { flex-direction: row-reverse; }
@keyframes cb-fadein { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

.cb-bubble {
    max-width: 82%;
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.55;
    word-break: break-word;
}
.cb-bubble.bot {
    background: #f7f5f0;
    color: #2d2d2d;
    border-radius: 4px 16px 16px 16px;
}
.cb-bubble.user {
    background: linear-gradient(135deg, var(--cb-primary) 0%, var(--cb-primary-d) 100%);
    color: #fff;
    border-radius: 16px 4px 16px 16px;
}
.cb-bubble strong { font-weight: 700; }

/* ── Typing Indicator ── */
.cb-typing { display: flex; gap: 5px; align-items: center; padding: 10px 14px; background: #f7f5f0; border-radius: 4px 16px 16px 16px; width: fit-content; }
.cb-typing span { width: 7px; height: 7px; border-radius: 50%; background: var(--cb-primary); animation: cb-type 1.2s ease infinite; }
.cb-typing span:nth-child(2) { animation-delay: .2s; }
.cb-typing span:nth-child(3) { animation-delay: .4s; }
@keyframes cb-type { 0%,80%,100%{transform:scale(.8);opacity:.4}40%{transform:scale(1.1);opacity:1} }

/* ── Product Cards ── */
.cb-products { display: flex; flex-direction: column; gap: 8px; margin-top: 6px; width: 100%; }
.cb-product-card {
    display: flex; gap: 10px;
    background: #fff;
    border: 1px solid rgba(200,169,110,.25);
    border-radius: 12px;
    padding: 10px;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s;
    cursor: pointer;
}
.cb-product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(200,169,110,.2);
    border-color: var(--cb-primary);
}
.cb-product-img {
    width: 64px; height: 64px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    background: #f7f5f0;
}
.cb-product-info { flex: 1; min-width: 0; }
.cb-product-name {
    font-size: 12.5px; font-weight: 600;
    color: #2d2d2d;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 4px;
}
.cb-product-prices { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.cb-price-sale { color: var(--cb-primary); font-weight: 700; font-size: 13px; }
.cb-price-orig { color: #999; font-size: 11.5px; text-decoration: line-through; }
.cb-price-badge {
    background: #fff0f0;
    color: #e53e3e;
    font-size: 10px; font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
}
.cb-product-meta { color: #999; font-size: 11px; margin-top: 3px; }
.cb-view-btn {
    display: inline-flex; align-items: center; gap: 3px;
    color: var(--cb-primary);
    font-size: 11.5px; font-weight: 600;
    margin-top: 4px;
    padding: 2px 0;
}
.cb-view-btn i { font-size: 10px; transition: transform .2s; }
.cb-product-card:hover .cb-view-btn i { transform: translateX(3px); }

/* ── Quick Replies ── */
.cb-quick-replies { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.cb-quick-btn {
    background: transparent;
    border: 1px solid var(--cb-primary);
    color: var(--cb-primary);
    font-size: 12px; font-weight: 500;
    padding: 5px 12px;
    border-radius: 20px;
    cursor: pointer;
    transition: all .18s;
    white-space: nowrap;
}
.cb-quick-btn:hover {
    background: var(--cb-primary);
    color: #fff;
    transform: scale(1.04);
}

/* ── Input Area ── */
.cb-input-area {
    padding: 12px 14px;
    border-top: 1px solid rgba(0,0,0,.07);
    display: flex; align-items: flex-end; gap: 10px;
    background: #fff;
    flex-shrink: 0;
}
.cb-input-wrap { flex: 1; position: relative; }
#luxbot-input {
    width: 100%;
    border: 1.5px solid #e8e0d5;
    border-radius: 24px;
    padding: 9px 16px;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    resize: none;
    transition: border-color .2s;
    background: #faf9f7;
    color: #2d2d2d;
    line-height: 1.5;
    max-height: 90px;
}
#luxbot-input:focus { border-color: var(--cb-primary); background: #fff; }
#luxbot-input::placeholder { color: #bbb; }
#luxbot-send {
    width: 40px; height: 40px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--cb-primary) 0%, var(--cb-primary-d) 100%);
    color: #fff;
    cursor: pointer;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: transform .18s, box-shadow .18s;
    box-shadow: 0 4px 12px rgba(200,169,110,.4);
}
#luxbot-send:hover { transform: scale(1.08); box-shadow: 0 6px 18px rgba(200,169,110,.5); }
#luxbot-send:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ── Divider ── */
.cb-divider {
    text-align: center;
    font-size: 11px;
    color: #bbb;
    margin: 2px 0;
}
.cb-divider span { background: #fff; padding: 0 8px; }

/* ── Mobile ── */
@media (max-width: 460px) {
    #luxbot-window {
        right: 12px; left: 12px;
        width: auto;
        bottom: 92px;
        max-height: 75vh;
    }
    #luxbot-toggle { bottom: 18px; right: 18px; }
}
</style>

{{-- ── Toggle Button ── --}}
<button id="luxbot-toggle" aria-label="Mở chat với {{ $chatName }}" title="Chat với {{ $chatName }}">
    <span class="cb-icon-chat" aria-hidden="true">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
            <path d="M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="white"/>
            <circle cx="8" cy="11" r="1.2" fill="#c8a96e"/>
            <circle cx="12" cy="11" r="1.2" fill="#c8a96e"/>
            <circle cx="16" cy="11" r="1.2" fill="#c8a96e"/>
        </svg>
    </span>
    <span class="cb-icon-close" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
    </span>
    <span id="luxbot-badge" style="display:none">1</span>
</button>

{{-- ── Chat Window ── --}}
<div id="luxbot-window" role="dialog" aria-label="Chatbot tư vấn túi xách" aria-modal="true">

    {{-- Header --}}
    <div class="cb-header">
        <div class="cb-avatar">👜</div>
        <div class="cb-header-info">
            <div class="cb-header-name">{{ $chatName }}</div>
            <div class="cb-header-status">
                <span class="cb-dot"></span>
                <span>Đang hoạt động · Tư vấn túi xách</span>
            </div>
        </div>
    </div>

    {{-- Messages --}}
    <div class="cb-messages" id="luxbot-messages" role="log" aria-live="polite" aria-atomic="false">
        {{-- Bot greeting --}}
        <div class="cb-msg bot" id="cb-greeting">
            <div>
                <div class="cb-bubble bot" id="cb-greeting-text">
                    {!! nl2br(e($chatGreeting)) !!}
                </div>
                <div class="cb-quick-replies" id="cb-initial-tips">
                    @foreach(['Túi dưới 500.000đ','Túi công sở','Túi đeo chéo','Túi tầm 1-2 triệu','Ba lô thời trang','Túi dạ tiệc'] as $tip)
                        <button class="cb-quick-btn" data-msg="{{ $tip }}">{{ $tip }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Input Area --}}
    <div class="cb-input-area">
        <div class="cb-input-wrap">
            <textarea id="luxbot-input"
                      rows="1"
                      placeholder="Hỏi tôi về túi xách..."
                      aria-label="Nhập câu hỏi về túi xách"
                      maxlength="500"></textarea>
        </div>
        <button id="luxbot-send" aria-label="Gửi tin nhắn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ENDPOINT = '{{ route("chatbot.message") }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const toggle   = document.getElementById('luxbot-toggle');
    const win      = document.getElementById('luxbot-window');
    const messages = document.getElementById('luxbot-messages');
    const input    = document.getElementById('luxbot-input');
    const sendBtn  = document.getElementById('luxbot-send');
    const badge    = document.getElementById('luxbot-badge');

    let isOpen    = false;
    let isLoading = false;

    // ── Toggle ──────────────────────────────────────────────────────
    toggle.addEventListener('click', () => {
        isOpen = !isOpen;
        toggle.classList.toggle('open', isOpen);
        win.classList.toggle('open', isOpen);
        if (isOpen) {
            badge.style.display = 'none';
            input.focus();
            scrollBottom();
        }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (isOpen && !win.contains(e.target) && !toggle.contains(e.target)) {
            isOpen = false;
            toggle.classList.remove('open');
            win.classList.remove('open');
        }
    });

    // ── Quick replies ────────────────────────────────────────────────
    document.addEventListener('click', (e) => {
        if (e.target.matches('.cb-quick-btn')) {
            const msg = e.target.dataset.msg;
            if (msg) sendMessage(msg);
        }
    });

    // ── Input auto-resize ────────────────────────────────────────────
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 90) + 'px';
    });

    // ── Send on Enter (Shift+Enter for newline) ──────────────────────
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            doSend();
        }
    });
    sendBtn.addEventListener('click', doSend);

    function doSend() {
        const text = input.value.trim();
        if (!text || isLoading) return;
        sendMessage(text);
        input.value = '';
        input.style.height = 'auto';
    }

    // ── Core send ────────────────────────────────────────────────────
    async function sendMessage(text) {
        if (isLoading) return;

        // User bubble
        appendBubble('user', escapeHtml(text));
        scrollBottom();

        // Typing indicator
        const typing = appendTyping();
        isLoading = true;
        sendBtn.disabled = true;

        try {
            const res = await fetch(ENDPOINT, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ message: text }),
            });

            if (!res.ok) throw new Error('Lỗi kết nối');
            const data = await res.json();

            typing.remove();

            // Bot message
            appendBotMessage(data);

        } catch (err) {
            typing.remove();
            appendBubble('bot', '⚠️ Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau!');
        } finally {
            isLoading = false;
            sendBtn.disabled = false;
            scrollBottom();
        }
    }

    // ── Render bot message ────────────────────────────────────────────
    function appendBotMessage(data) {
        const wrap = document.createElement('div');
        wrap.className = 'cb-msg bot';

        const inner = document.createElement('div');

        // Text bubble
        if (data.message) {
            const bubble = document.createElement('div');
            bubble.className = 'cb-bubble bot';
            bubble.innerHTML = markdownToHtml(data.message);
            inner.appendChild(bubble);
        }

        // Product cards
        if (data.products && data.products.length > 0) {
            const cards = document.createElement('div');
            cards.className = 'cb-products';
            data.products.forEach(p => {
                cards.appendChild(buildProductCard(p));
            });
            inner.appendChild(cards);
        }

        // Quick replies / tips
        if (data.tips && data.tips.length > 0) {
            const tips = document.createElement('div');
            tips.className = 'cb-quick-replies';
            data.tips.forEach(t => {
                const btn = document.createElement('button');
                btn.className = 'cb-quick-btn';
                btn.dataset.msg = t;
                btn.textContent = t;
                tips.appendChild(btn);
            });
            inner.appendChild(tips);
        }

        wrap.appendChild(inner);
        messages.appendChild(wrap);
    }

    // ── Product Card ─────────────────────────────────────────────────
    function buildProductCard(p) {
        const a = document.createElement('a');
        a.href = p.url;
        a.className = 'cb-product-card';
        a.target = '_blank';
        a.rel = 'noopener';

        const img = document.createElement('img');
        img.src = p.image;
        img.alt = p.name;
        img.className = 'cb-product-img';
        img.loading = 'lazy';
        img.onerror = () => { img.src = '/assets/images/product-placeholder.jpg'; };

        const info = document.createElement('div');
        info.className = 'cb-product-info';

        const name = document.createElement('div');
        name.className = 'cb-product-name';
        name.textContent = p.name;

        const prices = document.createElement('div');
        prices.className = 'cb-product-prices';

        const priceEl = document.createElement('span');
        priceEl.className = 'cb-price-sale';
        priceEl.textContent = p.is_on_sale ? p.sale_price : p.price;

        prices.appendChild(priceEl);

        if (p.is_on_sale) {
            const orig = document.createElement('span');
            orig.className = 'cb-price-orig';
            orig.textContent = p.price;
            const badge = document.createElement('span');
            badge.className = 'cb-price-badge';
            badge.textContent = '-' + p.discount + '%';
            prices.appendChild(orig);
            prices.appendChild(badge);
        }

        const meta = document.createElement('div');
        meta.className = 'cb-product-meta';
        const parts = [];
        if (p.brand)    parts.push(p.brand);
        if (p.rating > 0) parts.push('⭐ ' + parseFloat(p.rating).toFixed(1));
        meta.textContent = parts.join(' · ');

        const viewBtn = document.createElement('div');
        viewBtn.className = 'cb-view-btn';
        viewBtn.innerHTML = 'Xem sản phẩm <i class="fas fa-arrow-right"></i>';

        info.appendChild(name);
        info.appendChild(prices);
        if (parts.length > 0) info.appendChild(meta);
        info.appendChild(viewBtn);

        a.appendChild(img);
        a.appendChild(info);
        return a;
    }

    // ── Helpers ──────────────────────────────────────────────────────
    function appendBubble(role, html) {
        const wrap = document.createElement('div');
        wrap.className = 'cb-msg ' + role;
        const bubble = document.createElement('div');
        bubble.className = 'cb-bubble ' + role;
        bubble.innerHTML = html;
        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        return wrap;
    }

    function appendTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'cb-msg bot';
        wrap.innerHTML = '<div class="cb-typing"><span></span><span></span><span></span></div>';
        messages.appendChild(wrap);
        scrollBottom();
        return wrap;
    }

    function scrollBottom() {
        requestAnimationFrame(() => {
            messages.scrollTop = messages.scrollHeight;
        });
    }

    function escapeHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    // Minimal markdown: **bold** and newlines
    function markdownToHtml(text) {
        return escapeHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }

    // Show badge after 4s if not opened
    setTimeout(() => {
        if (!isOpen) {
            badge.style.display = 'flex';
        }
    }, 4000);

})();
</script>
@endif
