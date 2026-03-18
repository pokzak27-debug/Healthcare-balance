<?php

namespace App\Services\PatientSchedule;

use Illuminate\Support\Str;

class DiagnosisSummarizer
{
    /**
     * Résume un diagnostic en une phrase courte (100% local).
     */
    public function summarize(?string $diagnostic, int $maxLen = 160): ?string
    {
        $text = trim((string) $diagnostic);
        if ($text === '') {
            return null;
        }

        // Normaliser espaces et enlever retours.
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        // Prendre la première phrase si possible.
        if (preg_match('/^(.+?[.!?])\s+/', $text, $m)) {
            $text = trim($m[1]);
        }

        // Nettoyage léger (éviter listes trop longues).
        $text = preg_replace('/\s*[-•]\s*/', ' - ', $text) ?? $text;
        $text = trim($text, " \t\n\r\0\x0B-");

        return Str::limit($text, $maxLen, '…');
    }
}

