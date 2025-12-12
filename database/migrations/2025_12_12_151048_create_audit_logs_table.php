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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->enum('entity_type', ['user', 'ride', 'driver', 'promo_code', 'setting', 'other']);
            $table->unsignedBigInteger('entity_id');

            $table->enum('action', ['created', 'updated', 'deleted']);

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->enum('performed_by_type', ['admin', 'user', 'system'])
                ->default('system');

            $table->unsignedBigInteger('performed_by_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
