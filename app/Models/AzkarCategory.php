<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AzkarCategory extends Model
{
    protected $fillable = ['name', 'icon', 'color', 'sort_order'];

    public function adhkar(): HasMany
    {
        return $this->hasMany(Adhkar::class, 'azkar_category_id');
    }
}