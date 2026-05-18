<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordConcordance extends Model
{
    protected $table = 'word_concordance';

    protected $fillable = [
        'surah_number', 'ayah_number', 'word_position', 'word_text',
        'root', 'part_of_speech', 'morphology', 'gender', 'number',
        'person', 'tense', 'voice', 'case', 'lemma', 'translation',
    ];

    public $timestamps = true;

    /**
     * Get concordance for a specific word
     */
    public static function getForWord(int $surah, int $ayah, int $wordPosition): ?self
    {
        return self::where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->where('word_position', $wordPosition)
            ->first();
    }

    /**
     * Get all concordance for a verse
     */
    public static function getForVerse(int $surah, int $ayah): array
    {
        return self::where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->orderBy('word_position')
            ->get()
            ->toArray();
    }

    /**
     * Find words with same root
     */
    public static function getByRoot(string $root): array
    {
        return self::where('root', $root)
            ->orderBy('surah_number')
            ->orderBy('ayah_number')
            ->orderBy('word_position')
            ->get()
            ->toArray();
    }

    /**
     * Arabic labels for parts of speech
     */
    public static function posLabels(): array
    {
        return [
            'noun' => 'اسم',
            'verb' => 'فعل',
            'particle' => 'حرف',
            'pronoun' => 'ضمير',
            'adjective' => 'صفة',
            'adverb' => 'ظرف',
            'preposition' => 'حرف جر',
            'conjunction' => 'حرف عطف',
            'interrogative' => 'استفهام',
            'demonstrative' => 'اسم إشارة',
            'relative' => 'اسم موصول',
            'proper_noun' => 'علم',
        ];
    }

    public function getPosLabel(): string
    {
        return self::posLabels()[$this->part_of_speech] ?? $this->part_of_speech ?? '';
    }
}
