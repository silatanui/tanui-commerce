<?php

namespace App\Services\Ai;

class TextSanitizer
{
    /** Strip emoji, HTML, and noisy whitespace before embedding / prompting (thesis §4.3.2). */
    public function sanitize(string $text): string
    {
        $clean = strip_tags($text);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Emoji / symbol blocks
        $clean = preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{200D}]/u', '', $clean) ?? $clean;
        $clean = preg_replace('/[^\P{C}\n\t]+/u', '', $clean) ?? $clean;
        $clean = preg_replace('/[ \t]+/', ' ', $clean) ?? $clean;
        $clean = preg_replace("/\n{3,}/", "\n\n", $clean) ?? $clean;

        return trim(mb_strtolower($clean));
    }

    public function sanitizeDisplay(string $text): string
    {
        $clean = strip_tags($text);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $clean) ?? $clean;
        $clean = preg_replace('/[ \t]+/', ' ', $clean) ?? $clean;

        return trim($clean);
    }
}
