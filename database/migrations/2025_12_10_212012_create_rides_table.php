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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ride_request_id')
                ->constrained('ride_requests')
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('start_lat', 10, 7);
            $table->decimal('start_lng', 10, 7);
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();

            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_minutes')->nullable();

            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');

            $table->string('code')->nullable();

            $table->foreignId('promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->nullOnDelete();

            $table->foreignId('cancellation_reason_id')
                ->nullable()
                ->constrained('cancellation_reasons')
                ->nullOnDelete();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
