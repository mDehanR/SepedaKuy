<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            if (!Schema::hasColumn('bikes', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('bikes', 'longitude')) {
                $table->decimal('longitude', 11, 7)->nullable();
            }

            if (!Schema::hasColumn('bikes', 'gps_updated_at')) {
                $table->timestamp('gps_updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'gps_updated_at']);
        });
    }
};
