<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Support\LeadNotifier;
use App\Support\OrderIntake;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function home()
    {
        return view('site.home', [
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->where('is_active', true)])->get()->where('products_count', '>', 0),
            'featured' => Product::active()->with('category:id,name')->where('is_featured', true)->take(8)->get(),
            'gallery' => GalleryItem::published()->where('kind', 'photo')->take(8)->get(),
            'testimonials' => $this->reviews(3),
        ]);
    }

    public function about()
    {
        return view('site.about', ['testimonials' => $this->reviews(3)]);
    }

    public function menu(Request $request)
    {
        $categories = Category::active()->with(['products' => fn ($q) => $q->active()])->get()->filter(fn ($c) => $c->products->isNotEmpty());
        $current = $categories->firstWhere('slug', $request->query('category'));

        return view('site.menu', [
            'categories' => $categories,
            'current' => $current,
            'shown' => $current ? collect([$current]) : $categories,
            'uncategorised' => $current ? collect() : Product::active()->whereNull('category_id')->get(),
            'faqs' => Faq::published()->get(),
        ]);
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load(['images', 'category']);

        return view('site.product', [
            'product' => $product,
            'related' => Product::active()->whereKeyNot($product->id)
                ->orderByRaw('category_id = ? desc', [$product->category_id ?? 0])->take(4)->get(),
            'faqs' => Faq::published()->take(5)->get(),
        ]);
    }

    public function gallery(Request $request)
    {
        $categories = Category::active()->whereHas('galleryItems', fn ($q) => $q->where('is_published', true))->get();
        $current = $categories->firstWhere('slug', $request->query('category'));

        return view('site.gallery', [
            'items' => GalleryItem::published()->when($current, fn ($q) => $q->where('category_id', $current->id))->get(),
            'categories' => $categories,
            'current' => $current,
        ]);
    }

    public function order(Request $request)
    {
        $products = Product::active()->with('category:id,name')->get();

        return view('site.order', [
            'products' => $products,
            // Pre-select from links like /order?product=vintage-heart-cake
            'selected' => $products->firstWhere('slug', $request->query('product'))?->id,
        ]);
    }

    public function storeOrder(Request $request)
    {
        abort_if(Setting::get('orders_open') === '0', 403, 'Online orders are paused.');

        $product = OrderIntake::product($request->input('product_id'));

        $data = $request->validate(OrderIntake::rules($product) + [
            'reference_images' => ['nullable', 'array', 'max:3'],
            'reference_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,heic', 'max:8192'],
            'website' => ['prohibited'],
        ], OrderIntake::messages($product));
        unset($data['website'], $data['reference_images']);

        $images = collect($request->file('reference_images', []))
            ->map(fn ($file) => $file->store('orders', 'public'))->values()->all();

        $order = OrderIntake::create($data, $product, 'website', $images);

        return redirect()->route('order.placed')->with('order_id', $order->id);
    }

    /** Thank-you page after ordering, with a summary they can send on WhatsApp. */
    public function orderPlaced(Request $request)
    {
        $order = Order::find($request->session()->get('order_id'));
        if (! $order) {
            return redirect()->route('order');
        }
        $request->session()->reflash(); // survives a refresh

        return view('site.order-placed', ['order' => $order]);
    }

    public function contact()
    {
        return view('site.contact');
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'website' => ['prohibited'],
        ]);
        unset($data['website']);

        LeadNotifier::message(Message::create($data));

        return back()->with('sent', site('contact_success', ['name' => strtok($data['name'], ' ')]));
    }

    /** Public page where customers leave a review; it waits for approval in Admin → Reviews. */
    public function review()
    {
        return view('site.review');
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'quote' => ['required', 'string', 'min:10', 'max:1000'],
            'website' => ['prohibited'],
        ], [
            'rating.required' => 'Please choose a star rating.',
            'quote.required' => 'Please write a few words about your order.',
            'quote.min' => 'Please write a little more (at least 10 characters).',
        ]);
        unset($data['website']);

        Testimonial::create($data + ['is_published' => false, 'source' => 'website', 'sort' => 0]);

        return redirect()->route('review')->with('sent', site('review_success', ['name' => strtok($data['name'], ' ')]));
    }

    public function sitemap()
    {
        $urls = collect(['home', 'menu', 'gallery', 'order', 'about', 'contact'])
            ->map(fn ($name) => ['loc' => route($name), 'lastmod' => null])
            ->merge(Product::active()->get(['slug', 'updated_at'])->map(fn ($p) => ['loc' => route('menu.show', $p), 'lastmod' => $p->updated_at]));

        return response()->view('site.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    /** Hidden "Follow us" page opened from printed QR codes (Admin → QR codes). */
    public function follow()
    {
        return response()->view('site.follow')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function reviews(int $take)
    {
        return Testimonial::where('is_published', true)->orderBy('sort')->latest()->take($take)->get();
    }
}
