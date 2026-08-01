<?php

namespace App\Support;

final class SiteSeoMetrics
{
    /**
     * @return array<string, string>
     */
    public static function definitions(): array
    {
        return [
            'da' => 'DA',
            'pa' => 'PA',
            'spam_score' => 'Spam Score',
            'moz_rank' => 'Moz Rank',
            'majestic_cf' => 'Majestic CF',
            'majestic_tf' => 'Majestic TF',
            'ahrefs_dr' => 'Ahrefs DR',
            'ahrefs_keywords' => 'Ahrefs Kelime',
            'semrush_authority_score' => 'Semrush Authority Score',
            'monthly_traffic' => 'Monthly Traffic',
            'backlinks' => 'Backlinks',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }
}
