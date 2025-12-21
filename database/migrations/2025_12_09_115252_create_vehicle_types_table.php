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
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->decimal('base_fare', 10, 2)->nullable();
            $table->decimal('per_km', 10, 2)->nullable();
            $table->decimal('per_minute', 10, 2)->nullable();
            $table->decimal('minimum_fare', 10, 2)->nullable();
            $table->decimal('cost_increase_percentage', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
