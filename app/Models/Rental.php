<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bike_id',
        'payment_method_id',
        'duration_hours',
        'locked_price_per_hour',
        'rental_fee',
        'start_time',
        'due_time',
        'returned_at',
        'late_minutes',
        'fine_amount',
        'fine_paid',
        'payment_proof',
        'fine_payment_proof',
        'bike_condition_on_return',
        'admin_note',
        'status',
        'approved_by',
        'returned_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'due_time' => 'datetime',
        'returned_at' => 'datetime',
        'fine_paid' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
