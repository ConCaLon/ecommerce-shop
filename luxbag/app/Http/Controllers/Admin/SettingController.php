<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Các field file upload (logo)
        $fileFields = ['site_logo', 'site_logo_footer'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $request->validate([$field => 'image|mimes:jpg,jpeg,png,webp,svg|max:2048']);

                // Xóa file cũ nếu có
                $old = Setting::where('key', $field)->first();
                if ($old && $old->value) {
                    Storage::disk('public')->delete($old->value);
                }

                $path = $request->file($field)->store('settings', 'public');
                Setting::updateOrCreate(['key' => $field], ['value' => $path, 'group' => 'branding']);
            }
        }

        // Xử lý xóa logo (nếu checkbox "remove" được check)
        foreach (['remove_site_logo' => 'site_logo', 'remove_site_logo_footer' => 'site_logo_footer'] as $removeKey => $settingKey) {
            if ($request->has($removeKey)) {
                $old = Setting::where('key', $settingKey)->first();
                if ($old && $old->value) {
                    Storage::disk('public')->delete($old->value);
                    $old->update(['value' => null]);
                }
            }
        }

        // Xử lý các field text thông thường
        $excludeFields = array_merge(['_token', '_method', 'remove_site_logo', 'remove_site_logo_footer'], $fileFields);
        foreach ($request->except($excludeFields) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Cài đặt đã được lưu.');
    }
}
