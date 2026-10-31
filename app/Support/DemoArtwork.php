<?php

namespace App\Support;

class DemoArtwork
{
    /**
     * Prefer a real PNG/JPG/WebP already in public/, otherwise generate an SVG placeholder.
     */
    public static function resolve(string $relativeWithoutExtension, string $title, string $kicker, string $theme, bool $wide = false): string
    {
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $candidate = $relativeWithoutExtension.'.'.$extension;

            if (is_file(public_path($candidate))) {
                return $candidate;
            }
        }

        return self::write($relativeWithoutExtension.'.svg', $title, $kicker, $theme, $wide);
    }

    public static function write(string $relativePath, string $title, string $kicker, string $theme, bool $wide = false): string
    {
        $palettes = [
            'fashion' => ['#f6f1eb', '#e7d9cc', '#8e2f2f', '#1a1a1a'],
            'electronics' => ['#e7eef3', '#c9d7e2', '#087ea4', '#101820'],
            'home' => ['#f3efe8', '#e4d8c8', '#8d6a45', '#2c2825'],
            'market' => ['#eef4ec', '#d5e4d4', '#2f7d46', '#1c2b22'],
        ];

        [$a, $b, $accent, $ink] = $palettes[$theme] ?? $palettes['fashion'];
        $seed = abs(crc32($title.$kicker));
        $width = $wide ? 1600 : 900;
        $height = $wide ? 900 : 1120;
        $x = 80 + ($seed % 180);
        $y = 120 + ($seed % 220);
        $radius = 90 + ($seed % 140);
        $shapeX = $width - 280;
        $shapeY = $height - 260;
        $footerY = $height - 72;
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeKicker = htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8');
        $lines = explode("\n", wordwrap($safeTitle, $wide ? 28 : 16));
        $text = '';
        $textY = $wide ? 430 : 620;

        foreach ($lines as $index => $line) {
            $dy = $textY + ($index * ($wide ? 64 : 58));
            $text .= '<text x="72" y="'.$dy.'" fill="'.$ink.'" font-family="Georgia, serif" font-size="'.($wide ? 54 : 46).'" font-weight="600">'.$line.'</text>';
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="{$safeTitle}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$a}"/>
      <stop offset="1" stop-color="{$b}"/>
    </linearGradient>
  </defs>
  <rect width="{$width}" height="{$height}" fill="url(#bg)"/>
  <circle cx="{$x}" cy="{$y}" r="{$radius}" fill="{$accent}" opacity="0.18"/>
  <rect x="{$shapeX}" y="{$shapeY}" width="180" height="140" rx="8" fill="{$ink}" opacity="0.08"/>
  <text x="72" y="92" fill="{$accent}" font-family="Arial, sans-serif" font-size="18" letter-spacing="3">{$safeKicker}</text>
  {$text}
  <text x="72" y="{$footerY}" fill="{$ink}" opacity="0.55" font-family="Arial, sans-serif" font-size="16">SAMPLE PRODUCT · TANUI COMMERCE</text>
</svg>
SVG;

        $absolute = public_path($relativePath);
        $directory = dirname($absolute);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($absolute, $svg);

        return $relativePath;
    }
}
