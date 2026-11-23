<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sabs Pastries' own photos (database/seeders/media), arranged into a starter menu and gallery.
 * Prices are left blank ("Price on request") until the owner adds them. Safe to run again: rows match by slug/path.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@example.com')], [
            'name' => 'Sabs Admin',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
        ]);

        $categories = collect([
            ['Celebration Cakes', 'Birthdays, anniversaries and milestones, designed around you.'],
            ['Heart & Vintage Cakes', 'Piped vintage-style cakes and heart cakes with all the frills.'],
            ['Wedding & Luxury Cakes', 'Elegant statement cakes for weddings and special occasions.'],
            ['Cupcakes', 'Swirled, personalised and perfect for sharing.'],
            ['Bento Boxes', 'A mini cake with matching cupcakes in one gift box.'],
            ['Doughnuts & Treats', 'Doughnuts, dipped treats and little bites of joy.'],
            ['Dessert Tables', 'Full dessert spreads and party packages for your event.'],
        ])->map(fn ($c, $i) => Category::updateOrCreate(['slug' => Str::slug($c[0])], ['name' => $c[0], 'summary' => $c[1], 'sort' => $i, 'is_active' => true]))
            ->keyBy('slug');

        // [name, category, summary, main photo, extra photos, featured, sizes]
        $products = [
            ['Custom Celebration Cake', 'celebration-cakes', 'A made-to-order celebration cake in your colours, with toppers, drips, pearls and all the finishing touches.',
                'birthday-lilac', ['birthday-white-gold', 'pink-bow-cake'], true, "6 inch\n8 inch\n10 inch\nTiered (ask us)"],
            ['Chocolate Drip Cake', 'celebration-cakes', 'Rich chocolate layers with a glossy drip, loaded with chocolates and a personalised plaque.',
                'chocolate-drip', [], true, "6 inch\n8 inch"],
            ['Kids’ Themed Cake', 'celebration-cakes', 'Their favourite characters, colours and theme brought to life with printed and handmade decorations.',
                'kids-princess', ['kids-cocomelon'], true, "6 inch\n8 inch\nTiered (ask us)"],
            ['Ruffle Bouquet Cake', 'celebration-cakes', 'Wrapped in ruffles and topped with a bouquet of roses, made for Mother’s Day, birthdays and love notes.',
                'ruffle-mothers-day', [], false, "6 inch\n8 inch"],
            ['Vintage Heart Cake', 'heart-vintage-cakes', 'A heart-shaped cake piped in vintage frills and ribbons, finished with your message.',
                'heart-hbd-love', ['heart-red-rose', 'heart-sweet-16'], true, "6 inch heart\n8 inch heart"],
            ['Vintage Round Cake', 'heart-vintage-cakes', 'Classic vintage piping on a round cake, with pearls, gold details and lettering of your choice.',
                'vintage-husband', [], false, "6 inch\n8 inch"],
            ['Gender Reveal Cake', 'heart-vintage-cakes', 'Keep everyone guessing: a pastel heart cake with the big surprise hidden inside.',
                'heart-gender-reveal', [], false, "6 inch heart\n8 inch heart"],
            ['Photo Print Heart Cake', 'heart-vintage-cakes', 'Your favourite photo or artwork printed on a piped heart cake.',
                'heart-photo-print', [], false, "6 inch heart\n8 inch heart"],
            ['Wedding & Luxury Cake', 'wedding-luxury-cakes', 'Statement cakes with sugar flowers, gold leaf and sculpted details for weddings and grand celebrations.',
                'wedding-sugar-flower', ['luxury-white-gold'], true, "Single tier\n2 tiers\n3 tiers"],
            ['Personalised Cupcakes', 'cupcakes', 'Cupcakes topped with printed or piped toppers: names, photos, logos or inside jokes.',
                'cupcakes-personalised', [], true, "Box of 6\nBox of 12"],
            ['Bento Cake & Cupcake Box', 'bento-boxes', 'A mini cake with four matching cupcakes, boxed and ready to gift.',
                'bento-lilac-butterfly', ['bento-football'], true, "Mini cake + 4 cupcakes"],
            ['Chocolate-Dipped Treats', 'doughnuts-treats', 'Golden pastries half-dipped in milk and white chocolate and finished with sprinkles.',
                'chocolate-dipped-treats', [], false, "Box of 12\nBox of 24"],
            ['Doughnuts', 'doughnuts-treats', 'Soft, fluffy doughnuts, perfect for parties, offices and sharing.',
                'dessert-table-garden', [], false, "Box of 12\nBox of 24\nParty platter"],
            ['Dessert Table & Party Package', 'dessert-tables', 'Cakes, cupcakes, doughnuts, brownies, pies and more, styled as a full dessert spread for your event.',
                'dessert-table-party', ['dessert-table-garden'], true, "Up to 30 guests\n30-60 guests\n60+ guests"],
        ];

        foreach ($products as $i => [$name, $category, $summary, $photo, $extra, $featured, $sizes]) {
            $product = Product::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name, 'category_id' => $categories[$category]->id, 'summary' => $summary, 'sizes' => $sizes,
                'image_path' => $this->media($photo, 'products'), 'is_featured' => $featured, 'is_active' => true, 'sort' => $i,
            ]);
            foreach ($extra as $n => $file) {
                $product->images()->firstOrCreate(['path' => $this->media($file, 'products')], ['sort' => $n + 1]);
            }
        }

        // Gallery: every photo, plus the two videos.
        $gallery = [
            ['pink-bow-cake', 'celebration-cakes', 'Pink pearl cake with an organza bow', true],
            ['heart-hbd-love', 'heart-vintage-cakes', 'Vintage heart cake: HBD Love', true],
            ['dessert-table-party', 'dessert-tables', 'Birthday dessert table', true],
            ['wedding-sugar-flower', 'wedding-luxury-cakes', 'Sugar flower cake', false],
            ['cupcakes-personalised', 'cupcakes', 'Personalised birthday cupcakes', false],
            ['kids-princess', 'celebration-cakes', 'Princess birthday cake', false],
            ['bento-lilac-butterfly', 'bento-boxes', 'Lilac butterfly bento box', false],
            ['chocolate-drip', 'celebration-cakes', 'Chocolate drip birthday cake', false],
            ['heart-sweet-16', 'heart-vintage-cakes', 'Sweet 16 black heart cake', false],
            ['luxury-white-gold', 'wedding-luxury-cakes', 'White and gold geometric cake', false],
            ['ruffle-mothers-day', 'celebration-cakes', 'Mother’s Day ruffle cake', false],
            ['chocolate-dipped-treats', 'doughnuts-treats', 'Chocolate-dipped treats', false],
            ['birthday-lilac', 'celebration-cakes', 'Lilac birthday cake with balloons', false],
            ['heart-red-rose', 'heart-vintage-cakes', 'Heart cake with a red rose', false],
            ['dessert-table-garden', 'dessert-tables', 'Garden party dessert table', false],
            ['vintage-husband', 'heart-vintage-cakes', 'Vintage cake for a 45th birthday', false],
            ['bento-football', 'bento-boxes', 'Football fan bento box', false],
            ['kids-cocomelon', 'celebration-cakes', 'Kids’ themed birthday cake', false],
            ['heart-gender-reveal', 'heart-vintage-cakes', 'Gender reveal heart cake', false],
            ['birthday-white-gold', 'celebration-cakes', 'White and gold birthday cake', false],
            ['heart-photo-print', 'heart-vintage-cakes', 'Photo print heart cake', false],
        ];
        foreach ($gallery as $i => [$file, $category, $caption, $featured]) {
            GalleryItem::updateOrCreate(['path' => $this->media($file, 'gallery')], [
                'kind' => 'photo', 'caption' => $caption, 'category_id' => $categories[$category]->id, 'is_featured' => $featured, 'sort' => $i, 'is_published' => true,
            ]);
        }

        // General ordering questions. Worded to match the site's defaults; edit them in Admin → FAQs.
        $faqs = [
            ['How far in advance should I order?', 'Please order at least 3 days ahead for most cakes and treats. Wedding cakes, tiered cakes and dessert tables need more notice, so get in touch as early as you can.'],
            ['How do I confirm my order?', 'Send your order through the website or message us. We will confirm the design, price and date with you, and your slot is secured once your deposit is paid.'],
            ['Can you recreate a design I found online?', 'Yes. Upload your inspiration photos on the order form and we will create something in that style, adapted to your colours and message.'],
            ['Do you deliver?', 'Collection is always available, and delivery can be arranged in selected areas for a small fee. Choose delivery on the order form and we will confirm the cost.'],
            ['Can you cater for allergies?', 'Tell us about any allergies or dietary needs when you order and we will let you know what we can do. Our kitchen handles common allergens, so we cannot guarantee any product is allergen-free.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['answer' => $a, 'sort' => $i, 'is_published' => true]);
        }

        if (app()->environment('local')) {
            $this->demoOrders();
        }
    }

    /** Copy a file from database/seeders/media to the public disk (once) and return its stored path. */
    private function media(string $name, string $folder, string $ext = 'jpg'): string
    {
        $path = "{$folder}/{$name}.{$ext}";
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/media/{$name}.{$ext}")));
        }

        return $path;
    }

    /** A few sample orders so the dashboard and calendar have something to show locally. Not seeded in production. */
    private function demoOrders(): void
    {
        if (Order::exists()) {
            return;
        }
        $p = Product::pluck('id', 'slug');
        $demo = [
            ['Demo Customer A', '07700 900001', 'vintage-heart-cake', 'Vintage Heart Cake', '6 inch heart', 'Red velvet', 'Happy Birthday Love', 2, 'new', 'website', null, 0],
            ['Demo Customer B', '07700 900002', 'personalised-cupcakes', 'Personalised Cupcakes', 'Box of 12', 'Vanilla', null, 0, 'confirmed', 'instagram', 36, 15],
            ['Demo Customer C', '07700 900003', 'custom-celebration-cake', 'Custom Celebration Cake', '8 inch', 'Chocolate', 'Happy 40th', 5, 'baking', 'whatsapp', 85, 40],
            ['Demo Customer D', '07700 900004', 'bento-cake-cupcake-box', 'Bento Cake & Cupcake Box', null, 'Lemon', null, 9, 'confirmed', 'tiktok', 30, 30],
            ['Demo Customer E', '07700 900005', 'chocolate-drip-cake', 'Chocolate Drip Cake', '6 inch', 'Chocolate', 'Happy Birthday', -3, 'completed', 'website', 55, 55],
        ];
        foreach ($demo as [$name, $phone, $slug, $item, $size, $flavour, $message, $days, $status, $source, $total, $paid]) {
            Order::create([
                'name' => $name, 'phone' => $phone, 'product_id' => $p[$slug] ?? null, 'item' => $item, 'size' => $size, 'flavour' => $flavour,
                'cake_message' => $message, 'quantity' => 1, 'needed_on' => today()->addDays($days), 'fulfilment' => $days % 2 ? 'delivery' : 'collection',
                'delivery_address' => $days % 2 ? 'Sample address' : null, 'status' => $status, 'source' => $source, 'total' => $total, 'paid' => $paid,
                'occasion' => 'Birthday',
            ]);
        }
    }
}
