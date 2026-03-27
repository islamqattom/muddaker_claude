<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'category_id', 'page_number', 'verse_number',
        'surah_name', 'note', 'title',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'verse_number' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(BookmarkCategory::class, 'category_id');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
