<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->decimal('drop_lat', 10, 7)->nullable();
            $table->decimal('drop_lng', 10, 7)->nullable();

            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('price')->nullable();
            $table->integer('duration_minutes')->nullable();

            $table->foreignId('pickup_zone_id')
                ->nullable()
                ->constrained('zones')
                ->nullOnDelete();

            $table->foreignId('drop_zone_id')
                ->nullable()
                ->constrained('zones')
                ->nullOnDelete();

            $table->foreignId('vehicle_type_id')
                ->nullable()
                ->constrained('vehicle_types')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'searching_driver',
                'driver_assigned',
                'cancelled',
                'expired',
                'completed'
            ])->default('pending');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
