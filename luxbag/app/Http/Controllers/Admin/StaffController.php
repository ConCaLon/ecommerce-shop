<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    // Các role được coi là nhân viên nội bộ (không phải customer)
    private array $staffRoles = ['manager', 'staff', 'admin'];

    // ─── Danh sách nhân viên ───────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', $this->staffRoles);
        })->with('roles');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%")
                  ->orWhere('phone', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staff = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::whereIn('name', $this->staffRoles)->get();

        return view('admin.staff.index', compact('staff', 'roles'));
    }

    // ─── Form tạo mới ──────────────────────────────────────────────
    public function create()
    {
        $roles = Role::whereIn('name', ['manager', 'staff'])->get();
        return view('admin.staff.create', compact('roles'));
    }

    // ─── Lưu nhân viên mới ────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
            'role'     => 'required|in:manager,staff',
        ], [
            'name.required'      => 'Vui lòng nhập họ tên.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required'      => 'Vui lòng chọn vai trò.',
            'role.in'            => 'Vai trò không hợp lệ.',
        ]);

        $user = User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'password'         => Hash::make($request->password),
            'status'           => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.staff.index')
            ->with('success', "Đã tạo tài khoản nhân viên **{$user->name}** thành công.");
    }

    // ─── Form chỉnh sửa ───────────────────────────────────────────
    public function edit(User $staff)
    {
        // Chỉ cho phép chỉnh sửa nhân viên (không phải super-admin)
        abort_if($staff->hasRole('super-admin'), 403, 'Không thể chỉnh sửa tài khoản Super Admin.');

        $roles = Role::whereIn('name', ['manager', 'staff'])->get();
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    // ─── Cập nhật nhân viên ───────────────────────────────────────
    public function update(Request $request, User $staff)
    {
        abort_if($staff->hasRole('super-admin'), 403, 'Không thể chỉnh sửa tài khoản Super Admin.');

        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => "required|email|unique:users,email,{$staff->id}",
            'phone'    => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Rules\Password::min(8)->letters()->numbers()],
            'role'     => 'required|in:manager,staff',
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique'   => 'Email này đã được sử dụng.',
            'role.required'  => 'Vui lòng chọn vai trò.',
            'role.in'        => 'Vai trò không hợp lệ.',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);
        $staff->syncRoles([$request->role]);

        return redirect()->route('admin.staff.index')
            ->with('success', "Đã cập nhật thông tin nhân viên **{$staff->name}**.");
    }

    // ─── Xóa nhân viên ────────────────────────────────────────────
    public function destroy(User $staff)
    {
        // Không cho xóa super-admin
        abort_if($staff->hasRole('super-admin'), 403, 'Không thể xóa tài khoản Super Admin.');

        // Không cho xóa chính mình
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', "Đã xóa tài khoản **{$name}**.");
    }

    // ─── Toggle trạng thái ────────────────────────────────────────
    public function toggleStatus(User $staff)
    {
        // Không cho khóa super-admin
        if ($staff->hasRole('super-admin')) {
            return response()->json(['success' => false, 'message' => 'Không thể khóa tài khoản Super Admin.']);
        }

        // Không cho khóa chính mình
        if ($staff->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể khóa tài khoản của chính mình.']);
        }

        $staff->update(['status' => $staff->status === 'active' ? 'banned' : 'active']);

        return response()->json(['success' => true, 'status' => $staff->status]);
    }
}
