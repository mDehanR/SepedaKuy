<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bike extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'price_per_hour',
        'stock_total',
        'stock_available',
        'status',
        'image',
        'description',
    ];

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
