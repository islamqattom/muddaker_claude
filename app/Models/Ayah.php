<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    use HasFactory;

    protected $fillable = [
        'surah_id',
        'ayah_number',
        'text',
        'page_number',
        'juz_number',
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function getPageHtmlAttribute(): string
    {
        $pageNumber = $this->page_number;
        $currentAyahId = $this->id;

        $pageAyahs = Ayah::where('page_number', $pageNumber)
            ->with('surah')
            ->orderBy('id')
            ->get();

        if ($pageAyahs->isEmpty()) {
            return '<p>Page not found</p>';
        }

        $html = '<div class="quran-page" dir="rtl" style="font-family: Amiri, serif; font-size: 1.4rem; line-height: 2.2; text-align: justify; padding: 2rem;">';

        $currentSurah = null;
        foreach ($pageAyahs as $ayah) {
            if ($currentSurah !== $ayah->surah_id) {
                if ($currentSurah !== null) {
                    $html .= '</div>';
                }
                $html .= '<div class="surah-block" style="margin-bottom: 1.5rem;">';
                $html .= '<div class="surah-name" style="font-size: 1.6rem; font-weight: bold; margin-bottom: 0.5rem; color: #2c5282;">' . e($ayah->surah->name_ar) . '</div>';
                $currentSurah = $ayah->surah_id;
            }

            $highlightClass = $ayah->id === $currentAyahId ? 'highlighted-verse' : '';
            $html .= '<span class="ayah-text ' . $highlightClass . '" data-ayah-id="' . $ayah->id . '" data-surah="' . e($ayah->surah->name_ar) . '" data-ayah="' . $ayah->ayah_number . '" data-page="' . $ayah->page_number . '">';
            $html .= e($ayah->text) . ' ﴾' . $this->arabicNumeral($ayah->ayah_number) . '﴿ ';
            $html .= '</span>';
        }

        $html .= '</div></div>';

        return $html;
    }

    private function arabicNumeral(int $num): string
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        return implode('', array_map(fn($d) => $arabic[(int)$d], str_split($num)));
    }
}
