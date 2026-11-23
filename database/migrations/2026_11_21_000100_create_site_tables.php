<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        // Celebration cakes, cupcakes, bento boxes... groups the menu.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->text('description')->nullable();   // Markdown
            $table->decimal('price_from', 10, 2)->nullable(); // blank = price on request
            $table->string('price_note', 80)->nullable();   // e.g. "per box of 12"
            $table->text('sizes')->nullable();             // one option per line, e.g. "6 inch (10-12 servings)"
            $table->unsignedSmallInteger('lead_days')->nullable(); // overrides the default notice period
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Photo and video gallery.
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('caption')->nullable();
            $table->string('path');
            $table->string('kind', 10)->default('photo'); // photo | video
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Orders: from the website's order form, or added by hand from Instagram/TikTok/WhatsApp DMs.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item')->nullable();        // product name at the time, or a free description
            $table->string('occasion', 80)->nullable();
            $table->string('size', 120)->nullable();
            $table->string('flavour', 120)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('cake_message', 160)->nullable();
            $table->text('design_notes')->nullable();
            $table->string('allergies', 300)->nullable();
            $table->json('reference_images')->nullable();
            $table->date('needed_on')->index();
            $table->string('time_slot', 20)->nullable();
            $table->string('fulfilment', 20)->default('collection'); // collection | delivery
            $table->string('delivery_address', 300)->nullable();
            $table->string('source', 20)->default('website');
            $table->string('heard_from', 40)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('paid', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_published')->default(true);
            $table->string('source', 20)->default('admin'); // admin | website
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['faqs', 'testimonials', 'messages', 'orders', 'gallery_items', 'product_images', 'products', 'categories', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
