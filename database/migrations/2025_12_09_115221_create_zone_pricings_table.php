<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zone_pricings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('zone_id')
                ->constrained('zones')
                ->cascadeOnDelete();

            $table->decimal('base_fare', 10, 2);
            $table->decimal('per_km', 10, 2);
            $table->decimal('per_minute', 10, 2);
            $table->decimal('minimum_fare', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_pricings');
    }
};
