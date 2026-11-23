<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Admin → Site content: every editable text, photo and section switch on the public site, one tab per page. */
class ContentController extends Controller
{
    public function edit(string $page = 'home')
    {
        $pages = SiteContent::pages();
        abort_unless(isset($pages[$page]), 404);

        return view('admin.content', ['pages' => $pages, 'page' => $page, 's' => Setting::values()]);
    }

    public function update(Request $request, string $page)
    {
        $fields = SiteContent::pageFields($page);
        abort_if($fields === [], 404);

        $rules = [];
        foreach ($fields as $key => $f) {
            $rules[$key] = match ($f['type']) {
                'textarea' => ['nullable', 'string', 'max:5000'],
                'toggle' => ['nullable', 'boolean'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
                'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:61440'],
                default => ['nullable', 'string', 'max:300'],
            };
        }
        $data = $request->validate($rules);

        $values = [];
        foreach ($fields as $key => $f) {
            if (in_array($f['type'], ['image', 'video'], true)) {
                $current = Setting::get($key);
                if ($request->hasFile($key)) {
                    $current && Storage::disk('public')->delete($current);
                    $values[$key] = $request->file($key)->store('site', 'public');
                } elseif ($request->boolean('remove_'.$key) && $current) {
                    Storage::disk('public')->delete($current);
                    $values[$key] = '';
                }
            } elseif ($f['type'] === 'toggle') {
                $values[$key] = $request->boolean($key) ? '1' : '0';
            } else {
                $values[$key] = (string) ($data[$key] ?? '');
            }
        }
        Setting::put($values);

        return back()->with('status', SiteContent::pages()[$page]['label'].' saved.');
    }

    /** Put one tab back to the original wording, photos and switches. */
    public function reset(string $page)
    {
        $fields = SiteContent::pageFields($page);
        abort_if($fields === [], 404);

        foreach ($fields as $key => $f) {
            if (in_array($f['type'], ['image', 'video'], true) && ($path = Setting::get($key))) {
                Storage::disk('public')->delete($path);
            }
        }
        Setting::whereIn('key', array_keys($fields))->delete();
        Setting::put([]); // clears the cache

        return back()->with('status', SiteContent::pages()[$page]['label'].' reset to the original text.');
    }
}
