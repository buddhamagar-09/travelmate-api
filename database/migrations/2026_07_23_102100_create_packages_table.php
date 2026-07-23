<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            // Basic package info
            $table->string('title');
            $table->string('slug')->unique();

            // Descriptions
            $table->string('short_description', 255);
            $table->longText('long_description');

            // Pricing
            $table->decimal('price', 10, 2);

            // Trip details
            $table->string('duration');
            $table->string('difficulty');

            // Extra information
            $table->string('max_altitude')->nullable();
            $table->string('group_size')->nullable();
            $table->string('best_season')->nullable();
            $table->string('location')->nullable();

            // Main cover image
            $table->string('featured_image')->nullable();

            // Homepage featured package
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
