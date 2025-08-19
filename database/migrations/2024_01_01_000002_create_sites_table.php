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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('city', 100);
            $table->string('district', 100);
            $table->string('postal_code', 10);
            $table->string('tax_number', 20)->nullable();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('total_apartments');
            $table->decimal('total_area', 10, 2);
            $table->decimal('common_area_ratio', 5, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index(['city', 'district']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};