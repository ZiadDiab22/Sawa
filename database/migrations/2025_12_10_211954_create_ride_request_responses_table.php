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
        Schema::create('ride_request_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ride_request_id')
                ->constrained('ride_requests')
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('status', ['pending', 'accepted', 'skipped'])
                ->default('pending');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_request_responses');
    }
};
