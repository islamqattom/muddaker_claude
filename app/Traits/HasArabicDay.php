<?php

namespace App\Traits;

trait HasArabicDay
{
    private const ARABIC_DAYS = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    public static function getArabicDay($date): string
    {
        $dateObj = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return self::ARABIC_DAYS[$dateObj->dayOfWeek];
    }
}
