<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Structural: category driver + future destination link
            $table->string('category')->default('trekking')->after('slug');
            $table->unsignedBigInteger('destination_id')->nullable()->after('category');

            // Relax trek-only field so other categories don't need it
            $table->string('difficulty')->nullable()->change();

            // Category-specific fields (tour + wildlife)
            $table->string('tour_type')->nullable()->after('difficulty');
            $table->string('vehicle_type')->nullable()->after('tour_type');
            $table->string('park_name')->nullable()->after('vehicle_type');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'destination_id',
                'tour_type',
                'vehicle_type',
                'park_name',
            ]);
            // difficulty stays nullable on rollback
        });
    }
};