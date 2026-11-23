<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Order;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Order chat (Claude). Tests swap this for an instance with a fake HTTP transport.
        $this->app->bind(\App\Support\OrderAssistant::class, fn () => \App\Support\OrderAssistant::make());
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // Sidebar badges in the dashboard.
        View::composer('components.layouts.admin', function ($view) {
            $view->with('badges', [
                'orders' => Order::where('status', 'new')->count(),
                'messages' => Message::whereNull('read_at')->count(),
                'reviews' => Testimonial::where('is_published', false)->count(),
            ]);
        });
    }
}
