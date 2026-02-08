<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('ride_id')
                ->nullable()
                ->constrained('rides')
                ->nullOnDelete();

            $table->enum('type', ['credit', 'debit']);

            $table->enum('reason', [
                'ride_commission',
                'wallet_charge',
                'cancellation_penalty',
                'manual_adjustment',
            ]);

            $table->decimal('amount', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
