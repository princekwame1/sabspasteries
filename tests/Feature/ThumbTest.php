<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\Thumb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThumbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed();
    }

    public function test_cards_use_the_thumbnail_when_there_is_one_and_the_full_photo_otherwise(): void
    {
        $product = Product::whereNotNull('image_path')->first();
        $thumb = Thumb::pathFor($product->image_path);
        Storage::disk('public')->delete($thumb);

        $this->assertSame($product->imageUrl(), $product->thumbUrl());
        $this->get('/menu')->assertSee($product->imageUrl(), false);

        Storage::disk('public')->put($thumb, 'webp');
        $this->assertStringEndsWith('storage/thumbs/'.preg_replace('/\.\w+$/', '.webp', $product->image_path), $product->thumbUrl());
        $this->get('/menu')->assertSee($product->thumbUrl(), false);
    }

    public function test_deleting_a_product_removes_its_thumbnail(): void
    {
        $product = Product::whereNotNull('image_path')->first();
        Storage::disk('public')->put($thumb = Thumb::pathFor($product->image_path), 'webp');

        $product->delete();
        Storage::disk('public')->assertMissing($thumb);
    }

    public function test_thumbnail_command_reports_when_gd_cannot_help(): void
    {
        $code = $this->artisan('images:thumbs')->run();
        $this->assertSame(Thumb::supported() ? 0 : 1, $code);
    }
}
