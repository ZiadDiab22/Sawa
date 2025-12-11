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
        Schema::create('ride_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ride_id')
                ->constrained('rides')
                ->cascadeOnDelete();

            $table->string('old_status')->nullable();
            $table->string('new_status');

            $table->enum('changed_by_type', ['passenger', 'driver', 'system']);
            $table->foreignId('changed_by_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_status_histories');
    }
};
