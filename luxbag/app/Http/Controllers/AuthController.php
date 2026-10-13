<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    // ─── Login ────────────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');
        $guestSessionId = $request->session()->getId();

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->status === 'banned') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
            }

            // Merge guest cart
            $this->mergeGuestCart($user->id, $guestSessionId);

            $request->session()->regenerate();

            if (!$user->hasVerifiedEmail()) {
                return redirect()->intended(route('verification.notice'))
                    ->with('warning', 'Vui lòng xác thực email để kích hoạt tài khoản và đặt hàng.');
            }

            if ($user->isStaff()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('home'))->with('success', "Chào mừng trở lại, {$user->name}!");
        }

        return back()->withInput($request->only('email'))->with('error', 'Email hoặc mật khẩu không đúng.');
    }

    // ─── Register ─────────────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:255|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
        ], [
            'name.required'    => 'Vui lòng nhập họ tên.',
            'email.required'   => 'Vui lòng nhập email.',
            'email.unique'     => 'Email này đã được đăng ký.',
            'password.min'     => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'unverified',
            ]);

            $user->assignRole('customer');

            return $user;
        });

        $guestSessionId = $request->session()->getId();
        Auth::login($user);
        $this->mergeGuestCart($user->id, $guestSessionId);
        $request->session()->regenerate();

        $sent = $this->sendVerificationEmail($user, true);

        return redirect()->route('verification.notice')->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.'
                : 'Tài khoản đã được tạo nhưng chưa gửi được email xác thực. Vui lòng thử gửi lại sau một phút.'
        );
    }

    // ─── Email Verification ───────────────────────────────────────
    public function showVerificationNotice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->isStaff() ? 'admin.dashboard' : 'home');
        }

        return view('auth.verify_email', [
            'user' => $request->user(),
            'retryAfter' => RateLimiter::availableIn('verification-email:'.$request->user()->getKey()),
            'expiresInMinutes' => config('auth.verification.expire'),
        ]);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->intended(route($request->user()->isStaff() ? 'admin.dashboard' : 'home'))
            ->with('success', 'Email đã được xác thực thành công! Bạn có thể bắt đầu mua sắm.');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->isStaff() ? 'admin.dashboard' : 'home');
        }

        $key = 'verification-email:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return redirect()->route('verification.notice')->with(
                'warning', 'Vui lòng chờ '.RateLimiter::availableIn($key).' giây trước khi gửi lại email.'
            );
        }

        $sent = $this->sendVerificationEmail($user);

        return redirect()->route('verification.notice')->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Email xác thực đã được gửi lại. Vui lòng kiểm tra hộp thư và thư mục Spam.'
                : 'Chưa gửi được email xác thực. Vui lòng thử lại sau một phút.'
        );
    }

    // ─── Forgot Password ──────────────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'Email này chưa được đăng ký.',
        ]);

        $token = Str::random(64);
        $user = User::where('email', $request->email)->first();
        $user->update([
            'reset_token' => $token,
            'reset_token_expires' => now()->addHours(2),
        ]);

        // TODO: Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

        return back()->with('success', 'Link đặt lại mật khẩu đã được gửi vào email của bạn.');
    }

    public function showResetPassword($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        }

        return view('auth.reset_password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
        ]);

        $user = User::where('reset_token', $request->token)
            ->where('reset_token_expires', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Token không hợp lệ hoặc đã hết hạn.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires' => null,
        ]);

        return redirect()->route('login')->with('success', 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập lại.');
    }

    // ─── Logout ───────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Đã đăng xuất thành công.');
    }

    // ─── Google OAuth (stub) ──────────────────────────────────────
    public function redirectToGoogle()
    {
        // TODO: Implement Socialite Google login
        return redirect()->route('login')->with('info', 'Đăng nhập Google sẽ sớm được hỗ trợ.');
    }

    public function handleGoogleCallback()
    {
        // TODO: Implement Socialite callback
        return redirect()->route('login');
    }

    // ─── Private Helpers ──────────────────────────────────────────
    private function sendVerificationEmail(User $user, bool $registered = false): bool
    {
        RateLimiter::hit('verification-email:'.$user->getKey(), 60);

        try {
            if ($registered) {
                event(new Registered($user));
            } else {
                $user->sendEmailVerificationNotification();
            }

            return true;
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Unable to send account verification email.', [
                'user_id' => $user->getKey(),
                'exception_type' => $exception::class,
            ]);

            return false;
        }
    }

    private function mergeGuestCart(int $userId, string $guestSessionId): void
    {
        \App\Models\Cart::where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId, 'session_id' => null]);
    }
}
