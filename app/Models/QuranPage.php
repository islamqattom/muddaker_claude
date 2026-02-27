<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranPage extends Model
{
    protected $fillable = ['part_id', 'page_number', 'page_in_part', 'surah_name'];

    public function part()
    {
        return $this->belongsTo(QuranPart::class, 'part_id');
    }
}
