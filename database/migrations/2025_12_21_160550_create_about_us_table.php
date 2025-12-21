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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('phone')->nullable();

            $table->text('description')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('whatsapp_url')->nullable();

            // icons (paths)
            $table->string('facebook_icon')->nullable();
            $table->string('instagram_icon')->nullable();
            $table->string('website_icon')->nullable();
            $table->string('whatsapp_icon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
