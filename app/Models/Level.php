<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Level extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name', 'level_number', 'new_memorization_pages', 'review_pages', 'description'
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
