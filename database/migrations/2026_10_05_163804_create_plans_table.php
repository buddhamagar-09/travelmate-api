<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Optional user-given name
            $table->string('title')->nullable();

            // Preferences (all nullable — user might skip any)
            $table->string('category')->nullable();    // trekking | tour | wildlife
            $table->string('location')->nullable();
            $table->integer('budget_min')->nullable();
            $table->integer('budget_max')->nullable();
            $table->string('duration')->nullable();    // short | medium | long
            $table->string('difficulty')->nullable();  // Easy | Moderate | Hard
            $table->integer('group_size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
