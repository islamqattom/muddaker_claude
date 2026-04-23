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

    protected static array $pageHtmlCache = [];

    private const RE_HIGHLIGHT_CLEAR = '/class="ayah-text highlighted-verse"/';
    private const RE_AYAH_MARKER = '/(data-ayah-id="%s")/';

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function getPageHtmlAttribute(): string
    {
        $pageNumber = $this->page_number;
        $currentAyahId = $this->id;

        if (isset(self::$pageHtmlCache[$pageNumber])) {
            return $this->injectHighlight(self::$pageHtmlCache[$pageNumber], $currentAyahId);
        }

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

            $html .= '<span class="ayah-text" data-ayah-id="' . $ayah->id . '" data-surah="' . e($ayah->surah->name_ar) . '" data-ayah="' . $ayah->ayah_number . '" data-page="' . $ayah->page_number . '">';
            $html .= e($ayah->text) . ' ﴾' . $this->arabicNumeral($ayah->ayah_number) . '﴿ ';
            $html .= '</span>';
        }

        $html .= '</div></div>';

        self::$pageHtmlCache[$pageNumber] = $html;

        return $this->injectHighlight($html, $currentAyahId);
    }

    private function injectHighlight(string $cachedHtml, int $currentAyahId): string
    {
        $html = preg_replace(self::RE_HIGHLIGHT_CLEAR, 'class="ayah-text"', $cachedHtml);
        $pattern = sprintf(self::RE_AYAH_MARKER, $currentAyahId);
        return preg_replace(
            $pattern,
            '$1 class="ayah-text highlighted-verse"',
            $html,
            1
        );
    }

    private function arabicNumeral(int $num): string
    {
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        return implode('', array_map(fn($d) => $arabic[(int)$d], str_split($num)));
    }
}
