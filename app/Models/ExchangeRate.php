<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['currency_code', 'rate_to_idr', 'fetched_at'];

    protected $casts = [
        'fetched_at' => 'datetime',
        'rate_to_idr' => 'decimal:6',
    ];
}
