<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'address')) {
                $table->string('address', 100)->nullable()->after('email_verified_at');
            }

            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'birth_date']);
        });
    }
};
