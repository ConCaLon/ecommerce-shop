<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\UserAddress;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('orders');
        $orderStats = [
            'total'     => $user->orders->count(),
            'pending'   => $user->orders->where('status', 'pending')->count(),
            'delivered' => $user->orders->where('status', 'delivered')->count(),
        ];
        return view('profile.index', compact('user', 'orderStats'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'          => 'required|string|max:150',
            'phone'         => 'nullable|string|max:20',
            'gender'        => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        $user->update($request->only('name', 'phone', 'gender', 'date_of_birth'));
        return back()->with('success', 'Thông tin tài khoản đã được cập nhật.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Mật khẩu đã được thay đổi thành công.');
    }

    public function avatarForm()
    {
        return redirect()->route('profile.index');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);

        $user = Auth::user();

        // Delete old avatar
        if ($user->avatar && $user->avatar !== 'default-avatar.png') {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $image = $request->file('avatar');
        $filename = 'avatar-' . $user->id . '-' . time() . '.webp';

        $img = Image::read($image);
        $img->cover(200, 200);
        Storage::disk('public')->put('avatars/' . $filename, (string) $img->toWebp(80));

        $user->update(['avatar' => $filename]);

        return back()->with('success', 'Ảnh đại diện đã được cập nhật.');
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->get();
        return view('profile.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:150',
            'phone'          => 'required|string|max:20',
            'province'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'address_detail' => 'required|string',
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        Auth::user()->addresses()->create($request->all());
        return back()->with('success', 'Địa chỉ mới đã được thêm.');
    }

    public function updateAddress(Request $request, $id)
    {
        $address = UserAddress::where('user_id', Auth::id())->findOrFail($id);
        $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'address_detail' => 'required|string',
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address->update($request->all());
        return back()->with('success', 'Địa chỉ đã được cập nhật.');
    }

    public function deleteAddress($id)
    {
        UserAddress::where('user_id', Auth::id())->findOrFail($id)->delete();
        return back()->with('success', 'Địa chỉ đã được xóa.');
    }

    public function setDefaultAddress($id)
    {
        Auth::user()->addresses()->update(['is_default' => false]);
        Auth::user()->addresses()->findOrFail($id)->update(['is_default' => true]);
        return back()->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }
}
