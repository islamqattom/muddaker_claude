<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = ['user_id', 'national_id', 'phone', 'relationship'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function getRelationshipLabelAttribute(): string
    {
        return match($this->relationship) {
            'father' => 'الأب',
            'mother' => 'الأم',
            'guardian' => 'ولي الأمر',
            default => $this->relationship,
        };
    }
}
