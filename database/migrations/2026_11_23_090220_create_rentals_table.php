<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('duration_hours'); // Durasi sewa dalam jam
            $table->unsignedInteger('locked_price_per_hour'); // Snapshot harga/jam saat transaksi dibuat
            $table->unsignedInteger('rental_fee'); // duration_hours * locked_price_per_hour

            $table->dateTime('start_time')->nullable(); // Diisi saat admin approve
            $table->dateTime('due_time')->nullable(); // start_time + duration_hours
            $table->dateTime('returned_at')->nullable(); // Waktu sepeda dikembalikan

            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('fine_amount')->default(0); // Denda 10% per 10 menit
            $table->boolean('fine_paid')->default(false);

            $table->string('payment_proof')->nullable(); // Bukti bayar awal
            $table->string('fine_payment_proof')->nullable(); // Bukti bayar denda (jika transfer)
            $table->enum('bike_condition_on_return', ['good', 'damaged', 'lost'])->nullable();
            $table->text('admin_note')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'active',
                'rejected',
                'returned',
                'completed',
            ])->default('pending');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
