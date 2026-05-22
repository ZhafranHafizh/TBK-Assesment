<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type'];

    protected $appends = ['is_editable_full'];

    public function getIsEditableFullAttribute()
    {
        return $this->created_at ? $this->created_at->copy()->addHours(24)->isFuture() : true;
    }

    /**
     * Get the chart of accounts for this category.
     */
    public function coas()
    {
        return $this->hasMany(Coa::class);
    }
}
