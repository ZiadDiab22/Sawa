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
        Schema::create('driver_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                ->constrained('driver_profiles')
                ->cascadeOnDelete();

            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('fixed_fee', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_commissions');
    }
};
