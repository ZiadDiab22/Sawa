<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->enum('discount_type', ['percentage', 'fixed'])
                ->default('percentage');

            $table->decimal('amount', 8, 2)->unsigned();

            $table->unsignedInteger('max_uses')->default(1);

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
