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
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // $table->foreignId('city_id')
            //     ->nullable()
            //     ->constrained('cities')
            //     ->nullOnDelete();

            $table->foreignId('vehicle_type_id')
                ->nullable()
                ->constrained('vehicle_types')
                ->nullOnDelete();


            $table->foreignId('vehicle_make_id')
                ->nullable()
                ->constrained('vehicle_makes')
                ->nullOnDelete();

            // $table->string('residence_location')->nullable();
            $table->string('vehicle_model');
            $table->smallInteger('vehicle_year');
            $table->string('vehicle_color');
            $table->string('vehicle_plate_number')->unique();
            $table->string('vehicle_document')->nullable();
            $table->string('license_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->json('vehicle_images')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->enum('is_status', ['active', 'inactive', 'banned'])->default('inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
