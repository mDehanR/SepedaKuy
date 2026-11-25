<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['bike_id']);
            $table->unsignedBigInteger('bike_id')->nullable()->change();
            $table->foreign('bike_id')
                ->references('id')
                ->on('bikes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['bike_id']);
            $table->unsignedBigInteger('bike_id')->nullable(false)->change();
            $table->foreign('bike_id')
                ->references('id')
                ->on('bikes')
                ->cascadeOnDelete();
        });
    }
};
