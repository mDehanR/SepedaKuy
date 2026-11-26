<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'birth_place') && ! Schema::hasColumn('users', 'address')) {
                $table->renameColumn('birth_place', 'address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'address') && ! Schema::hasColumn('users', 'birth_place')) {
                $table->renameColumn('address', 'birth_place');
            }
        });
    }
};
