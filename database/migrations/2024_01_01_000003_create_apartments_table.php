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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('apartment_number', 20);
            $table->integer('floor')->nullable();
            $table->string('block', 10)->nullable();
            $table->decimal('area', 8, 2);
            $table->string('rooms', 10)->nullable();
            $table->decimal('ownership_share', 5, 4);
            $table->decimal('monthly_aidat', 10, 2);
            $table->boolean('is_occupied')->default(false);
            $table->string('owner_name')->nullable();
            $table->string('owner_phone', 20)->nullable();
            $table->string('owner_email')->nullable();
            $table->timestamps();
            
            $table->unique(['site_id', 'apartment_number', 'block']);
            $table->index(['site_id', 'is_occupied']);
            $table->index(['site_id', 'block', 'floor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};