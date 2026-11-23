<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Month view of when orders are due (collection or delivery date). */
class CalendarController extends Controller
{
    public function __invoke(Request $request)
    {
        $month = rescue(fn () => Carbon::createFromFormat('!Y-m', (string) $request->query('month')), null, false) ?? now()->startOfMonth();
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $orders = Order::where('status', '!=', 'cancelled')
            ->whereBetween('needed_on', [$start->toDateString(), $end->toDateString()])
            ->orderBy('needed_on')->orderBy('time_slot')->get()
            ->groupBy(fn ($o) => $o->needed_on->toDateString());

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = ['date' => $d->copy(), 'orders' => $orders[$d->toDateString()] ?? collect()];
        }

        return view('admin.calendar', [
            'month' => $month,
            'days' => $days,
            'list' => $orders->flatten()->filter(fn ($o) => $o->needed_on->isSameMonth($month)),
        ]);
    }
}
