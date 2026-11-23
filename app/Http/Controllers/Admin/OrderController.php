<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Orders from the website plus ones added by hand from DMs, calls and WhatsApp. */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.orders.index', [
            'orders' => $this->query($request)->paginate(25)->withQueryString(),
            'counts' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.orders.form', [
            'order' => new Order([
                'source' => 'instagram', 'status' => 'confirmed', 'quantity' => 1, 'fulfilment' => 'collection',
                'needed_on' => $request->query('date') ?: today()->addWeek()->toDateString(),
            ]),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $order = Order::create($this->validated($request));

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order '.$order->reference().' added.');
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load('product'),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            // Earlier orders from the same customer
            'history' => Order::whereKeyNot($order->id)
                ->where(fn ($q) => $q->where('phone', $order->phone)->when($order->email, fn ($w) => $w->orWhere('email', $order->email)))
                ->latest('needed_on')->take(10)->get(),
            'sameDay' => Order::whereKeyNot($order->id)->whereIn('status', Order::OPEN)->whereDate('needed_on', $order->needed_on)->count(),
        ]);
    }

    public function edit(Order $order)
    {
        return view('admin.orders.form', [
            'order' => $order,
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        // The side panel on the order page only sends the stage, money and notes.
        if ($request->boolean('quick')) {
            $data = $request->validate([
                'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
                'total' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
                'paid' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
                'notes' => ['nullable', 'string', 'max:5000'],
            ]);
            $order->update(['paid' => (float) ($data['paid'] ?? 0)] + $data);

            return back()->with('status', 'Order '.$order->reference().' saved.');
        }

        $order->update($this->validated($request, $order));

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order '.$order->reference().' saved.');
    }

    /** One-click stage change from the order page and lists. */
    public function status(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Order::STATUSES))]]);
        $order->update($data);

        return back()->with('status', $order->reference().' marked as '.mb_strtolower($order->statusLabel()).'.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('status', 'Order deleted.');
    }

    /** Download the filtered orders as a spreadsheet (CSV). */
    public function export(Request $request)
    {
        $rows = $this->query($request)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Received', 'Source', 'Status', 'Name', 'Phone', 'Email', 'Item', 'Qty', 'Size', 'Flavour', 'Occasion', 'Message on cake', 'Needed on', 'Time', 'Collection/Delivery', 'Address', 'Total', 'Paid', 'Balance', 'Design notes', 'Allergies', 'Private notes']);
            foreach ($rows as $o) {
                fputcsv($out, [
                    $o->reference(), $o->created_at->format('Y-m-d H:i'), $o->sourceLabel(), $o->statusLabel(), $o->name, $o->phone, $o->email,
                    $o->item, $o->quantity, $o->size, $o->flavour, $o->occasion, $o->cake_message, $o->needed_on->format('Y-m-d'), $o->timeLabel(),
                    $o->fulfilmentLabel(), $o->delivery_address, $o->total, $o->paid, $o->balance(), $o->design_notes, $o->allergies, $o->notes,
                ]);
            }
            fclose($out);
        }, 'orders-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function validated(Request $request, ?Order $order = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'product_id' => ['nullable', 'exists:products,id'],
            'item' => ['nullable', 'string', 'max:160'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'size' => ['nullable', 'string', 'max:120'],
            'flavour' => ['nullable', 'string', 'max:120'],
            'occasion' => ['nullable', 'string', 'max:80'],
            'cake_message' => ['nullable', 'string', 'max:160'],
            'design_notes' => ['nullable', 'string', 'max:3000'],
            'allergies' => ['nullable', 'string', 'max:300'],
            'needed_on' => ['required', 'date'],
            'time_slot' => ['nullable', Rule::in(array_keys(Order::TIME_SLOTS))],
            'fulfilment' => ['required', Rule::in(array_keys(Order::FULFILMENT))],
            'delivery_address' => ['nullable', 'string', 'max:300'],
            'source' => ['required', Rule::in(array_keys(Order::SOURCES))],
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'total' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'paid' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'reference_images' => ['nullable', 'array', 'max:6'],
            'reference_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_images' => ['nullable', 'array'],
        ]);
        $data['paid'] = (float) ($data['paid'] ?? 0);
        if (! filled($data['item'] ?? null) && ($data['product_id'] ?? null)) {
            $data['item'] = Product::whereKey($data['product_id'])->value('name');
        }

        // Inspiration photos: keep the current ones unless removed, add new uploads.
        $images = $order?->reference_images ?? [];
        $remove = array_intersect($data['remove_images'] ?? [], $images);
        foreach ($remove as $path) {
            Storage::disk('public')->delete($path);
        }
        $images = array_values(array_diff($images, $remove));
        foreach ($request->file('reference_images', []) as $file) {
            $images[] = $file->store('orders', 'public');
        }
        $data['reference_images'] = $images ?: null;
        unset($data['remove_images']);

        return $data;
    }

    private function query(Request $request): Builder
    {
        $view = $request->query('view');

        return Order::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('source'), fn ($q, $s) => $q->where('source', $s))
            ->when($request->query('payment') === 'balance', fn ($q) => $q->whereNotNull('total')->whereColumn('paid', '<', 'total')->where('status', '!=', 'cancelled'))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('item', 'like', "%{$term}%")
                ->when(preg_match('/^SP-?(\d+)$/i', trim($term), $m), fn ($w) => $w->orWhere('id', (int) $m[1]))))
            ->when($view === 'upcoming', fn ($q) => $q->whereIn('status', Order::OPEN)->whereDate('needed_on', '>=', today())->orderBy('needed_on'))
            ->when($view !== 'upcoming', fn ($q) => $q->latest());
    }
}
