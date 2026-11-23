<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    private const TEXT_FIELDS = [
        'business_name' => ['required', 'string', 'max:80'],
        'tagline' => ['nullable', 'string', 'max:160'],
        'phone' => ['nullable', 'string', 'max:40'],
        'phone_2' => ['nullable', 'string', 'max:40'],
        'phone_2_label' => ['nullable', 'string', 'max:60'],
        'whatsapp' => ['nullable', 'string', 'max:40'],
        'email' => ['nullable', 'email', 'max:160'],
        'notify_email' => ['nullable', 'email', 'max:160'],
        'address' => ['nullable', 'string', 'max:200'],
        'hours' => ['nullable', 'string', 'max:120'],
        'instagram' => ['nullable', 'url', 'max:255'],
        'tiktok' => ['nullable', 'url', 'max:255'],
        'threads' => ['nullable', 'url', 'max:255'],
        'facebook' => ['nullable', 'url', 'max:255'],
        'linktree' => ['nullable', 'url', 'max:255'],
        'social_handle' => ['nullable', 'string', 'max:60'],
        'currency' => ['required', 'string', 'max:5'],
        'lead_days' => ['required', 'integer', 'min:0', 'max:90'],
        'flavours' => ['nullable', 'string', 'max:2000'],
        'delivery_note' => ['nullable', 'string', 'max:300'],
        'orders_closed_note' => ['nullable', 'string', 'max:300'],
    ];

    private const SWITCHES = ['orders_open', 'delivery_enabled'];

    // Page wording, photos and section switches live in Admin → Site content (ContentController).
    private const IMAGES = ['logo' => 'logo_path'];

    public function edit()
    {
        return view('admin.settings', ['s' => Setting::values()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(self::TEXT_FIELDS + [
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $values = array_map(fn ($v) => (string) $v, array_intersect_key($data, self::TEXT_FIELDS));
        foreach (self::SWITCHES as $key) {
            $values[$key] = $request->boolean($key) ? '1' : '0';
        }

        foreach (self::IMAGES as $input => $key) {
            $current = Setting::get($key);
            if ($request->hasFile($input)) {
                $current && Storage::disk('public')->delete($current);
                $values[$key] = $request->file($input)->store('site', 'public');
            } elseif ($request->boolean('remove_'.$input) && $current) {
                Storage::disk('public')->delete($current);
                $values[$key] = '';
            }
        }

        Setting::put($values);

        return back()->with('status', 'Settings saved.');
    }
}
