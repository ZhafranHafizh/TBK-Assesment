<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coa extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'coa_category_id'];

    protected $appends = ['is_editable_full'];

    public function getIsEditableFullAttribute()
    {
        return $this->created_at ? $this->created_at->copy()->addHours(24)->isFuture() : true;
    }

    /**
     * Get the category that owns the COA.
     */
    public function coaCategory()
    {
        return $this->belongsTo(CoaCategory::class);
    }

    /**
     * Get the financial transactions associated with this COA.
     */
    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
