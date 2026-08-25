<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

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

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->status === 'banned') {
                Auth::logout();
                return back()->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
            }

            if ($user->status === 'unverified') {
                Auth::logout();
                return back()->with('error', 'Vui lòng xác thực email trước khi đăng nhập.');
            }

            // Merge guest cart
            $this->mergeGuestCart($user->id);

            $request->session()->regenerate();

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
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
        ], [
            'name.required'    => 'Vui lòng nhập họ tên.',
            'email.required'   => 'Vui lòng nhập email.',
            'email.unique'     => 'Email này đã được đăng ký.',
            'password.min'     => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $verifyToken = Str::random(64);

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'phone'               => $request->phone,
            'password'            => Hash::make($request->password),
            'status'              => 'unverified',
            'email_verify_token'  => $verifyToken,
        ]);

        $user->assignRole('customer');

        // TODO: Send verification email
        // Mail::to($user->email)->send(new VerifyEmailMail($user, $verifyToken));

        // For development: auto-verify
        $user->update(['status' => 'active', 'email_verified_at' => now(), 'email_verify_token' => null]);
        Auth::login($user);
        $this->mergeGuestCart($user->id);

        return redirect()->route('home')->with('success', 'Đăng ký thành công! Chào mừng bạn đến với LuxBag.');
    }

    // ─── Email Verification ───────────────────────────────────────
    public function verifyEmail($token)
    {
        $user = User::where('email_verify_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Token xác thực không hợp lệ.');
        }

        $user->update([
            'status' => 'active',
            'email_verified_at' => now(),
            'email_verify_token' => null,
        ]);

        Auth::login($user);
        return redirect()->route('home')->with('success', 'Email đã được xác thực thành công!');
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
    private function mergeGuestCart(int $userId): void
    {
        \App\Models\Cart::where('session_id', session()->getId())
            ->whereNull('user_id')
            ->update(['user_id' => $userId, 'session_id' => null]);
    }
}
