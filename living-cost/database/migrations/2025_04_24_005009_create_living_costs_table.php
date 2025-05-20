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
        Schema::create('living_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->decimal('housing', 12, 2)->nullable();
            $table->decimal('food', 12, 2)->nullable();
            $table->decimal('transportation', 12, 2)->nullable();
            $table->decimal('utilities', 12, 2)->nullable();
            $table->decimal('healthcare', 12, 2)->nullable();
            $table->decimal('entertainment', 12, 2)->nullable();
            $table->decimal('other', 12, 2)->nullable();
            $table->decimal('total_estimation', 14, 2)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('living_costs');
    }
};
