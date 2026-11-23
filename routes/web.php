<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// ---------------- Public website ----------------
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/menu', [SiteController::class, 'menu'])->name('menu');
Route::get('/menu/{product}', [SiteController::class, 'product'])->name('menu.show');
Route::get('/gallery', [SiteController::class, 'gallery'])->name('gallery');
Route::get('/order', [SiteController::class, 'order'])->name('order');
Route::post('/order', [SiteController::class, 'storeOrder'])->name('order.store')->middleware('throttle:6,10');
Route::get('/order/thank-you', [SiteController::class, 'orderPlaced'])->name('order.placed');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::post('/contact', [SiteController::class, 'sendContact'])->name('contact.send')->middleware('throttle:5,1');
Route::get('/review', [SiteController::class, 'review'])->name('review');
Route::post('/review', [SiteController::class, 'storeReview'])->name('review.store')->middleware('throttle:3,10');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');
// Hidden "Follow us" page for QR codes: not linked from the site, not in the sitemap, noindex.
Route::get('/follow', [SiteController::class, 'follow'])->name('follow');
// Order chat (Claude): each message is one paid API call, so keep the rate modest.
Route::post('/chat', [ChatController::class, 'send'])->name('chat.send')->middleware('throttle:20,1');
Route::post('/chat/reset', [ChatController::class, 'reset'])->name('chat.reset')->middleware('throttle:10,1');

Route::redirect('/book', '/order', 301);
Route::redirect('/login', '/admin/login');

// ---------------- Admin dashboard ----------------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'show'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('calendar', Admin\CalendarController::class)->name('calendar');

        Route::get('orders/export', [Admin\OrderController::class, 'export'])->name('orders.export');
        Route::resource('orders', Admin\OrderController::class);
        Route::post('orders/{order}/status', [Admin\OrderController::class, 'status'])->name('orders.status');

        Route::resource('messages', Admin\MessageController::class)->only(['index', 'show', 'destroy']);

        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::delete('products/{product}/images/{image}', [Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::get('gallery', [Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [Admin\GalleryController::class, 'store'])->name('gallery.store');
        Route::put('gallery/{item}', [Admin\GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{item}', [Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::resource('faqs', Admin\FaqController::class)->except('show');

        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        Route::post('testimonials/{testimonial}/publish', [Admin\TestimonialController::class, 'publish'])->name('testimonials.publish');

        Route::get('content/{page?}', [Admin\ContentController::class, 'edit'])->name('content');
        Route::put('content/{page}', [Admin\ContentController::class, 'update'])->name('content.update');
        Route::post('content/{page}/reset', [Admin\ContentController::class, 'reset'])->name('content.reset');

        Route::get('qr-codes', [Admin\QrCodeController::class, 'index'])->name('qr-codes.index');
        Route::post('qr-codes', [Admin\QrCodeController::class, 'store'])->name('qr-codes.store');
        Route::get('qr-codes/{qrCode}/download/{format}', [Admin\QrCodeController::class, 'download'])->name('qr-codes.download')->whereIn('format', ['png', 'svg']);
        Route::delete('qr-codes/{qrCode}', [Admin\QrCodeController::class, 'destroy'])->name('qr-codes.destroy');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings');
        Route::put('settings', [Admin\SettingController::class, 'update']);
        Route::get('account', [Admin\AccountController::class, 'edit'])->name('account');
        Route::put('account', [Admin\AccountController::class, 'update'])->middleware('throttle:6,1');
    });
});
