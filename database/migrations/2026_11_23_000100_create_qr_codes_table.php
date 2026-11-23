<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // QR codes made in Admin → QR codes. The images live on the public disk under qr/.
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('url');
            $table->string('png_path');
            $table->string('svg_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
