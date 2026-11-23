<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed();
    }

    public function test_public_pages_load(): void
    {
        $product = Product::first();
        foreach (['/', '/menu', '/menu?category=cupcakes', '/menu/'.$product->slug, '/gallery', '/order', '/order?product='.$product->slug, '/about', '/contact', '/review', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/menu/no-such-cake')->assertNotFound();
    }

    public function test_home_shows_contact_details_and_menu(): void
    {
        $this->get('/')
            ->assertSee('Sabs Pastries')
            ->assertSee('+44 7846 796313')
            ->assertSee('+233 54 372 0244')
            ->assertSee('Ghana (preorders only)')
            ->assertSee('https://wa.me/447846796313', false)
            ->assertSee('Vintage Heart Cake');
    }

    public function test_hidden_products_are_not_shown(): void
    {
        $product = Product::where('slug', 'vintage-heart-cake')->first();
        $product->update(['is_active' => false]);

        $this->get('/menu/vintage-heart-cake')->assertNotFound();
        $this->get('/menu')->assertDontSee('Vintage Heart Cake');
    }

    public function test_customer_can_place_an_order_with_inspiration_photos(): void
    {
        $product = Product::where('slug', 'personalised-cupcakes')->first();

        $this->post('/order', [
            'product_id' => $product->id,
            'size' => 'Box of 12',
            'flavour' => 'Vanilla',
            'quantity' => 2,
            'occasion' => 'Birthday',
            'cake_message' => 'Happy Birthday Yasin',
            'needed_on' => today()->addDays(7)->toDateString(),
            'time_slot' => 'morning',
            'fulfilment' => 'delivery',
            'delivery_address' => '1 Sample Street',
            'name' => 'Ama Mensah',
            'phone' => '07700 900123',
            'reference_images' => [UploadedFile::fake()->image('idea.png')],
        ])->assertRedirect('/order/thank-you');

        $order = Order::sole();
        $this->assertSame('Personalised Cupcakes', $order->item);
        $this->assertSame('website', $order->source);
        $this->assertSame('new', $order->status);
        $this->assertCount(1, $order->reference_images);
        Storage::disk('public')->assertExists($order->reference_images[0]);

        $this->get('/order/thank-you')
            ->assertOk()
            ->assertSee('Thank you, Ama!')
            ->assertSee($order->reference())
            ->assertSee('2 × Personalised Cupcakes');
    }

    public function test_order_needs_enough_notice(): void
    {
        $product = Product::first();
        $product->update(['lead_days' => 10]);

        $this->from('/order')->post('/order', [
            'product_id' => $product->id, 'quantity' => 1, 'fulfilment' => 'collection',
            'needed_on' => today()->addDays(5)->toDateString(), 'name' => 'Ama', 'phone' => '0244000000',
        ])->assertRedirect('/order')->assertSessionHasErrors('needed_on');

        $this->assertSame(0, Order::count());
    }

    public function test_custom_order_needs_a_description_and_delivery_needs_an_address(): void
    {
        $this->from('/order')->post('/order', [
            'quantity' => 1, 'fulfilment' => 'delivery', 'needed_on' => today()->addDays(10)->toDateString(),
            'name' => 'Ama', 'phone' => '0244000000',
        ])->assertSessionHasErrors(['item', 'delivery_address']);
    }

    public function test_orders_can_be_paused(): void
    {
        Setting::put(['orders_open' => '0']);

        $this->get('/order')->assertSee('Orders are paused');
        $this->post('/order', ['item' => 'Cake', 'quantity' => 1, 'fulfilment' => 'collection', 'needed_on' => today()->addDays(10)->toDateString(), 'name' => 'A', 'phone' => '1'])
            ->assertForbidden();
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/order', ['website' => 'spam', 'item' => 'Cake', 'quantity' => 1, 'fulfilment' => 'collection', 'needed_on' => today()->addDays(10)->toDateString(), 'name' => 'Bot', 'phone' => '1'])
            ->assertSessionHasErrors('website');
        $this->assertSame(0, Order::count());
    }

    public function test_contact_form_creates_a_message(): void
    {
        $this->post('/contact', ['name' => 'Kofi', 'phone' => '0244000000', 'message' => 'Do you make eggless cakes?'])
            ->assertSessionHas('sent');

        $this->assertSame('Do you make eggless cakes?', Message::sole()->message);
    }

    public function test_reviews_wait_for_approval(): void
    {
        $this->post('/review', ['name' => 'Efua', 'rating' => 5, 'quote' => 'The cake was beautiful and delicious!'])->assertSessionHas('sent');

        $review = Testimonial::sole();
        $this->assertFalse($review->is_published);
        $this->get('/')->assertDontSee('The cake was beautiful and delicious!');
    }

    public function test_phone_numbers_become_international_links(): void
    {
        $this->assertSame('+447846796313', Setting::tel('07846 796313'));
        $this->assertSame('+233543720244', Setting::tel('054 372 0244'));
        $this->assertSame('+233543720244', Setting::tel('+233 54 372 0244'));
        $this->assertSame('https://wa.me/447846796313', Setting::whatsappUrl(null, '07846796313'));
    }
}
