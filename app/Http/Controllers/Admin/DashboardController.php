<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Orders received per day for the last 30 days (zero-filled), for the bar chart.
        $since = now()->subDays(29)->startOfDay();
        $counts = Order::where('created_at', '>=', $since)->get(['created_at'])->countBy(fn ($o) => $o->created_at->toDateString());
        $chart = collect(range(0, 29))->map(function ($i) use ($since, $counts) {
            $day = $since->copy()->addDays($i);

            return ['date' => $day, 'total' => $counts[$day->toDateString()] ?? 0];
        });

        $live = Order::where('status', '!=', 'cancelled');
        $month = (clone $live)->whereBetween('needed_on', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        return view('admin.dashboard', [
            'stats' => [
                'new' => Order::where('status', 'new')->count(),
                'today' => Order::whereIn('status', Order::OPEN)->whereDate('needed_on', today())->count(),
                'week' => Order::whereIn('status', Order::OPEN)->whereBetween('needed_on', [today()->toDateString(), today()->addDays(6)->toDateString()])->count(),
                'monthOrders' => (clone $month)->count(),
                'monthValue' => (float) (clone $month)->sum('total'),
                'monthPaid' => (float) (clone $month)->sum('paid'),
                'outstanding' => (float) Order::whereIn('status', Order::OPEN)->whereNotNull('total')->selectRaw('coalesce(sum(total - paid), 0) as due')->value('due'),
                'unread' => Message::whereNull('read_at')->count(),
                'products' => Product::where('is_active', true)->count(),
            ],
            'chart' => $chart,
            'upcoming' => Order::whereIn('status', Order::OPEN)->whereDate('needed_on', '>=', today())->orderBy('needed_on')->orderBy('time_slot')->take(8)->get(),
            'overdue' => Order::whereIn('status', ['new', 'confirmed', 'baking', 'ready'])->whereDate('needed_on', '<', today())->count(),
            'recent' => Order::latest()->take(6)->get(),
            'popular' => Product::withCount(['orders' => fn ($q) => $q->where('status', '!=', 'cancelled')->where('created_at', '>=', now()->subMonths(6))])
                ->get()->where('orders_count', '>', 0)->sortByDesc('orders_count')->take(5),
            'sources' => (clone $live)->where('created_at', '>=', now()->subMonths(6))->selectRaw('source, count(*) as total')->groupBy('source')->pluck('total', 'source'),
            'pipeline' => Order::whereIn('status', Order::OPEN)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
