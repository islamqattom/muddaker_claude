<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookmarkCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'name', 'color', 'icon', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(StudentBookmark::class, 'category_id');
    }
}
