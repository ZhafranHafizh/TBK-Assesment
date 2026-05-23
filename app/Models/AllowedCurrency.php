<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedCurrency extends Model
{
    protected $fillable = ['code', 'name'];

    /**
     * Get the latest exchange rate for this currency.
     */
    public function latestRate()
    {
        return $this->hasOne(ExchangeRate::class, 'currency_code', 'code')
                    ->latestOfMany('fetched_at');
    }
}
