<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'amount', 'currency', 'method', 'status',
        'checkout_link', 'payment_reference', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'paid_at' => 'datetime'];
    }
}
