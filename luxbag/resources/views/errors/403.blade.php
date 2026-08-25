<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Không có quyền truy cập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        /* Animated background */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at 20% 30%, rgba(212,175,55,.08) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 70%, rgba(124,58,237,.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .error-container {
            text-align: center;
            padding: 40px 24px;
            max-width: 520px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        .error-icon-wrap {
            width: 120px;
            height: 120px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, rgba(239,68,68,.15), rgba(220,38,38,.05));
            border: 1px solid rgba(239,68,68,.2);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.2); }
            50% { box-shadow: 0 0 0 16px rgba(239,68,68,0); }
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }
        .error-desc {
            font-size: 14px;
            color: rgba(255,255,255,.5);
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #d4af37, #b8860b);
            color: #0f172a;
            font-weight: 700;
            font-size: 14px;
            border-radius: 10px;
            text-decoration: none;
            transition: all .2s;
            border: none;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(212,175,55,.3);
            color: #0f172a;
        }
        .btn-secondary-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 20px;
            background: rgba(255,255,255,.05);
            color: rgba(255,255,255,.7);
            font-size: 14px;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.1);
            transition: all .2s;
            margin-left: 12px;
        }
        .btn-secondary-link:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #d4af37, transparent);
            border-radius: 2px;
            margin: 16px auto 24px;
        }
        .role-info {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            color: rgba(255,255,255,.5);
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon-wrap">
            <i class="fas fa-shield-alt" style="font-size:48px;color:#ef4444;"></i>
        </div>

        <div class="error-code">403</div>
        <div class="divider"></div>

        <h1 class="error-title">Không có quyền truy cập</h1>

        @if(auth()->check())
        <div class="role-info">
            <img src="{{ auth()->user()->avatar_url }}" style="width:18px;height:18px;border-radius:50%;object-fit:cover;" alt="">
            {{ auth()->user()->name }} · {{ auth()->user()->getRoleNames()->first() }}
        </div>
        @endif

        <p class="error-desc">
            Tài khoản của bạn không có quyền thực hiện thao tác này.<br>
            Vui lòng liên hệ quản trị viên nếu bạn cần được cấp quyền.
        </p>

        <div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.dashboard') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>
</body>
</html>
