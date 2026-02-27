<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'level_number', 'new_memorization_pages', 'review_pages', 'description'
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
