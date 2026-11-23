<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Make QR codes for any link (the hidden Follow us page by default), keep them, and download them. */
class QrCodeController extends Controller
{
    public function index()
    {
        return view('admin.qr-codes.index', [
            'codes' => QrCode::latest()->get(),
            'followUrl' => route('follow'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            // Shorter links make simpler, easier-to-scan codes; 500 characters is plenty.
            'url' => ['required', 'url:http,https', 'max:500'],
        ], ['url.url' => 'Enter a full link starting with https:// (or http://).']);

        $qr = QrCode::generate($data['label'], $data['url']);

        return redirect()->route('admin.qr-codes.index')->with('status', "QR code “{$qr->label}” created. Download it below.");
    }

    public function download(QrCode $qrCode, string $format)
    {
        abort_unless(in_array($format, ['png', 'svg'], true), 404);
        $path = $format === 'png' ? $qrCode->png_path : $qrCode->svg_path;
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $qrCode->downloadName($format));
    }

    public function destroy(QrCode $qrCode)
    {
        $qrCode->delete();

        return back()->with('status', 'QR code deleted.');
    }
}
