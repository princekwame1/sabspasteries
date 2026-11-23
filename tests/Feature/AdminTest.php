<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed();
        $this->admin = User::first();
    }

    private function order(array $attributes = []): Order
    {
        return Order::create($attributes + [
            'name' => 'Ama Mensah', 'phone' => '07700 900123', 'item' => 'Vintage Heart Cake', 'quantity' => 1,
            'needed_on' => today()->addDays(4), 'fulfilment' => 'collection', 'source' => 'website', 'status' => 'new',
        ]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/orders')->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in(): void
    {
        $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_wrong_password_shows_an_error_on_the_login_page(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Sign in to your dashboard');

        $this->from('/admin/login')->followingRedirects()
            ->post('/admin/login', ['email' => $this->admin->email, 'password' => 'not-it'])
            ->assertSee('Those details do not match our records.')
            ->assertSee('role="alert"', false);
        $this->assertGuest();
    }

    public function test_every_admin_page_loads(): void
    {
        $order = $this->order(['total' => 40, 'paid' => 10, 'reference_images' => null]);
        $message = Message::create(['name' => 'Kofi', 'message' => 'Hello']);
        $this->actingAs($this->admin);

        foreach ([
            '/admin', '/admin/calendar', '/admin/calendar?month=2026-01', '/admin/orders', '/admin/orders?view=upcoming', '/admin/orders?payment=balance&q=SP-0001',
            '/admin/orders/create', "/admin/orders/{$order->id}", "/admin/orders/{$order->id}/edit",
            '/admin/messages', "/admin/messages/{$message->id}", '/admin/products', '/admin/products/create',
            '/admin/products/'.Product::first()->slug.'/edit', '/admin/categories', '/admin/categories/create',
            '/admin/gallery', '/admin/faqs', '/admin/testimonials', '/admin/content', '/admin/content/order', '/admin/settings', '/admin/account',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_shows_new_orders_and_balance(): void
    {
        $this->order(['total' => 50, 'paid' => 20]);

        $this->actingAs($this->admin)->get('/admin')
            ->assertSee('New orders')
            ->assertSee('£30');
    }

    public function test_admin_can_add_an_order_from_a_dm(): void
    {
        $this->actingAs($this->admin)->post('/admin/orders', [
            'name' => 'Instagram Customer', 'phone' => '07700 900999', 'product_id' => Product::first()->id,
            'quantity' => 1, 'needed_on' => today()->addDays(3)->toDateString(), 'fulfilment' => 'collection',
            'source' => 'instagram', 'status' => 'confirmed', 'total' => 45, 'paid' => 20,
            'reference_images' => [UploadedFile::fake()->image('dm.png')],
        ])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(Product::first()->name, $order->item);
        $this->assertSame('deposit', $order->paymentState());
        $this->assertEquals(25, $order->balance());
    }

    public function test_order_moves_through_the_stages(): void
    {
        $order = $this->order();
        $this->actingAs($this->admin);

        $this->post("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->status);

        $this->put("/admin/orders/{$order->id}", ['quick' => 1, 'status' => 'baking', 'total' => 60, 'paid' => 60, 'notes' => 'Paid in full'])->assertRedirect();
        $order->refresh();
        $this->assertSame('baking', $order->status);
        $this->assertSame('paid', $order->paymentState());
        $this->assertSame('Paid in full', $order->notes);
    }

    public function test_orders_export_as_csv(): void
    {
        $this->order();
        $response = $this->actingAs($this->admin)->get('/admin/orders/export');
        $response->assertOk();
        $this->assertStringContainsString('SP-0001', $response->streamedContent());
    }

    public function test_deleting_an_order_removes_its_photos(): void
    {
        Storage::disk('public')->put('orders/idea.png', 'x');
        $order = $this->order(['reference_images' => ['orders/idea.png']]);

        $this->actingAs($this->admin)->delete("/admin/orders/{$order->id}")->assertRedirect('/admin/orders');
        Storage::disk('public')->assertMissing('orders/idea.png');
    }

    public function test_opening_a_message_marks_it_read(): void
    {
        $message = Message::create(['name' => 'Kofi', 'message' => 'Hello']);
        $this->actingAs($this->admin)->get("/admin/messages/{$message->id}")->assertSee('Hello');
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_admin_can_manage_products(): void
    {
        $category = Category::first();
        $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Red Velvet Cupcakes', 'category_id' => $category->id, 'price_from' => 18, 'price_note' => 'per box of 6',
            'sizes' => "Box of 6\nBox of 12", 'is_active' => 1, 'image' => UploadedFile::fake()->image('cupcakes.png'),
            'photos' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.png')],
        ])->assertRedirect('/admin/products');

        $product = Product::where('slug', 'red-velvet-cupcakes')->sole();
        $this->assertSame('From £18 per box of 6', $product->priceLabel());
        $this->assertSame(['Box of 6', 'Box of 12'], $product->sizeList());
        $this->assertCount(2, $product->images);
        $this->get('/menu/red-velvet-cupcakes')->assertOk()->assertSee('Box of 12');

        $this->delete("/admin/products/{$product->slug}")->assertRedirect('/admin/products');
        $this->assertNull(Product::find($product->id));
    }

    public function test_admin_can_upload_and_hide_gallery_items(): void
    {
        $before = GalleryItem::count();
        $this->actingAs($this->admin)->post('/admin/gallery', [
            'files' => [UploadedFile::fake()->image('one.png'), UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4')],
        ])->assertRedirect();

        $this->assertSame($before + 2, GalleryItem::count());
        $this->assertTrue(GalleryItem::where('kind', 'video')->latest('id')->first()->isVideo());

        $item = GalleryItem::where('caption', 'Pink pearl cake with an organza bow')->first();
        $this->put("/admin/gallery/{$item->id}", ['caption' => 'Pink pearl cake with an organza bow'])->assertRedirect(); // unticked = hidden
        $this->assertFalse($item->fresh()->is_published);
    }

    public function test_settings_and_currency(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'business_name' => 'Sabs Pastries', 'currency' => 'GH₵', 'lead_days' => 5, 'orders_open' => '1', 'delivery_enabled' => '0',
            'flavours' => "Vanilla\nCoconut",
        ])->assertRedirect();

        $this->assertSame('GH₵', Setting::get('currency'));
        $this->assertSame(['Vanilla', 'Coconut'], Setting::flavours());
        $this->assertSame('GH₵12.50', money(12.5));
        $this->get('/order')->assertDontSee('value="delivery"', false);
    }

    public function test_approving_a_review_publishes_it(): void
    {
        $review = Testimonial::create(['name' => 'Efua', 'quote' => 'Loved the cupcakes so much!', 'rating' => 5, 'is_published' => false, 'source' => 'website']);

        $this->actingAs($this->admin)->post("/admin/testimonials/{$review->id}/publish")->assertRedirect();
        $this->get('/')->assertSee('Loved the cupcakes so much!');
    }

    public function test_every_site_content_tab_opens(): void
    {
        $this->actingAs($this->admin);
        foreach (array_keys(SiteContent::pages()) as $page) {
            $this->get("/admin/content/{$page}")->assertOk();
        }
        $this->get('/admin/content/home')->assertDontSee('Second photo')->assertSee('Customer favourite');
    }

    public function test_follow_popup_shows_on_site_pages_but_not_while_ordering(): void
    {
        $this->get('/')->assertSee('followPopup', false)->assertSee('Follow on Instagram');
        $this->get('/menu')->assertSee('followPopup', false);
        $this->get('/order')->assertDontSee('followPopup', false);

        Setting::put(['follow_show' => '0']);
        $this->get('/')->assertDontSee('followPopup', false);
    }

    public function test_labels_and_buttons_are_editable(): void
    {
        $this->actingAs($this->admin);
        $save = fn (string $page, array $values) => $this->put("/admin/content/{$page}", $values + collect(SiteContent::pageFields($page))->reject(fn ($field) => in_array($field['type'], ['image', 'video'], true))->map(fn ($field) => $field['default'])->all())->assertRedirect();

        $save('home', ['home_categories_button' => 'Take a look', 'home_cta_eyebrow' => 'Party time']);
        $save('contact', ['contact_call_label' => 'Ring us', 'contact_cta_title' => 'Fancy a cake?']);
        $save('layout', ['nav_about' => 'Our story', 'nav_call' => 'Phone', 'chat_button' => 'Chat to order', 'footer_order_link' => 'Place an order']);
        $save('menu', ['menu_custom_button' => 'Ask for something custom']);
        $save('order', ['order_chat_title' => 'Rather message us?']);

        $this->get('/')->assertSee('Take a look')->assertSee('Party time')
            ->assertSee('Our story')->assertSee('Phone +44')->assertSee('Place an order')->assertDontSee('>Browse<', false);
        $this->get('/contact')->assertSee('Ring us')->assertSee('Fancy a cake?');
        $this->get('/menu')->assertSee('Ask for something custom');
        $this->get('/order')->assertSee('Rather message us?');

        config(['services.anthropic.key' => 'test-key']);
        $this->get('/')->assertSee('Chat to order');
    }
}
