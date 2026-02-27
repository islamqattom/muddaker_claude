<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuranPart extends Model
{
    use HasFactory;

    protected $fillable = ['part_number', 'name', 'pages_count', 'start_page', 'end_page'];

    public function pages()
    {
        return $this->hasMany(QuranPage::class, 'part_id');
    }

    public function recitationSessions()
    {
        return $this->hasMany(RecitationSession::class, 'part_id');
    }
}
