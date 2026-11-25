<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bikes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: MTB-01, CTY-02
            $table->string('name'); // Contoh: Polygon Cascade 4
            $table->string('type'); // MTB, City Bike, Folding Bike, E-Bike, Kids Bike
            $table->unsignedInteger('price_per_hour'); // Contoh: 30000 untuk MTB
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('stock_available')->default(0);
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bikes');
    }
};
