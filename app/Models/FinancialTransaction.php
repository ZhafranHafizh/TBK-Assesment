<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_date',
        'coa_id',
        'description',
        'debit',
        'credit',
        'original_currency',
        'original_amount',
        'exchange_rate',
    ];

    protected $appends = ['is_editable_full', 'is_edited', 'is_restored'];

    public function getIsEditableFullAttribute()
    {
        return $this->created_at ? $this->created_at->copy()->addHours(24)->isFuture() : true;
    }

    public function getIsRestoredAttribute()
    {
        return !is_null($this->restored_at);
    }

    public function getIsEditedAttribute()
    {
        if (!$this->updated_at) return false;
        
        $comparisonTime = $this->restored_at ?: $this->created_at;
        return $comparisonTime && $this->updated_at->gt($comparisonTime);
    }

    /**
     * Get the Chart of Account associated with the transaction.
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
