<?php

namespace App\Services\Reports;

class ReportChartRenderer
{
    /**
     * @param  list<array{label: string, value: float|int}>  $points
     */
    public function barSvg(array $points, string $accent = '#c45a3a', int $width = 640, int $height = 220): string
    {
        if ($points === []) {
            return $this->emptySvg($width, $height);
        }

        $max = max(1, max(array_map(fn ($p) => (float) $p['value'], $points)));
        $count = count($points);
        $gap = 8;
        $left = 36;
        $bottom = 36;
        $top = 16;
        $plotW = $width - $left - 12;
        $plotH = $height - $bottom - $top;
        $barW = max(8, ($plotW - ($gap * ($count - 1))) / $count);

        $bars = '';
        foreach ($points as $i => $point) {
            $value = (float) $point['value'];
            $h = ($value / $max) * $plotH;
            $x = $left + ($i * ($barW + $gap));
            $y = $top + ($plotH - $h);
            $label = htmlspecialchars(mb_strimwidth((string) $point['label'], 0, 10, '…'), ENT_QUOTES, 'UTF-8');
            $bars .= sprintf(
                '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" opacity="0.92"/>',
                $x,
                $y,
                $barW,
                max(1, $h),
                $accent
            );
            $bars .= sprintf(
                '<text x="%.1f" y="%d" text-anchor="middle" font-size="9" fill="#666">%s</text>',
                $x + ($barW / 2),
                $height - 12,
                $label
            );
        }

        return $this->wrapSvg($width, $height, $bars.'<line x1="'.$left.'" y1="'.($top + $plotH).'" x2="'.($width - 12).'" y2="'.($top + $plotH).'" stroke="#ddd"/>');
    }

    /**
     * @param  list<array{label: string, value: float|int}>  $actual
     * @param  list<array{label: string, value: float|int}>  $projected
     */
    public function lineSvg(array $actual, array $projected = [], string $accent = '#1a1a1a', string $proj = '#c45a3a', int $width = 640, int $height = 220): string
    {
        $series = array_merge($actual, $projected);
        if ($series === []) {
            return $this->emptySvg($width, $height);
        }

        $max = max(1, max(array_map(fn ($p) => (float) $p['value'], $series)));
        $left = 36;
        $top = 16;
        $bottom = 36;
        $plotW = $width - $left - 16;
        $plotH = $height - $bottom - $top;
        $n = max(1, count($series) - 1);

        $coords = [];
        foreach ($series as $i => $point) {
            $x = $left + (($i / $n) * $plotW);
            $y = $top + $plotH - (((float) $point['value'] / $max) * $plotH);
            $coords[] = [$x, $y, $i < count($actual)];
        }

        $actualPath = '';
        $projPath = '';
        foreach ($coords as $i => [$x, $y, $isActual]) {
            $cmd = $i === 0 ? 'M' : 'L';
            if ($isActual || ($i > 0 && $coords[$i - 1][2])) {
                $actualPath .= sprintf('%s%.1f,%.1f ', $cmd === 'M' && $actualPath === '' ? 'M' : ($actualPath === '' ? 'M' : 'L'), $x, $y);
            }
            if (! $isActual || ($i + 1 < count($coords) && ! $coords[$i + 1][2])) {
                if ($projPath === '' && $i > 0) {
                    $projPath .= sprintf('M%.1f,%.1f ', $coords[$i - 1][0] ?? $x, $coords[$i - 1][1] ?? $y);
                }
                $projPath .= sprintf('L%.1f,%.1f ', $x, $y);
            }
        }

        // Rebuild paths more reliably
        $actualPts = array_values(array_filter($coords, fn ($c) => $c[2]));
        $projPts = array_values(array_filter($coords, fn ($c) => ! $c[2]));
        $actualPath = $this->pathFromPoints($actualPts);
        $projPath = '';
        if ($projPts !== []) {
            $bridge = $actualPts !== [] ? [end($actualPts)] : [];
            $projPath = $this->pathFromPoints(array_merge($bridge, $projPts));
        }

        $dots = '';
        foreach ($actualPts as [$x, $y]) {
            $dots .= sprintf('<circle cx="%.1f" cy="%.1f" r="2.4" fill="%s"/>', $x, $y, $accent);
        }

        $labels = '';
        $step = max(1, (int) floor(count($series) / 6));
        foreach ($series as $i => $point) {
            if ($i % $step !== 0 && $i !== count($series) - 1) {
                continue;
            }
            $x = $left + (($i / $n) * $plotW);
            $label = htmlspecialchars(mb_strimwidth((string) $point['label'], 0, 8, ''), ENT_QUOTES, 'UTF-8');
            $labels .= sprintf('<text x="%.1f" y="%d" text-anchor="middle" font-size="8" fill="#777">%s</text>', $x, $height - 10, $label);
        }

        $body = sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#ececec"/><path d="%s" fill="none" stroke="%s" stroke-width="2.4"/><path d="%s" fill="none" stroke="%s" stroke-width="2.2" stroke-dasharray="5 4"/>%s%s',
            $left,
            $top + $plotH,
            $width - 16,
            $top + $plotH,
            $actualPath,
            $accent,
            $projPath,
            $proj,
            $dots,
            $labels
        );

        return $this->wrapSvg($width, $height, $body);
    }

    /**
     * @param  list<array{label: string, value: float|int}>  $slices
     */
    public function donutSvg(array $slices, array $colors, int $size = 220): string
    {
        if ($slices === []) {
            return $this->emptySvg($size, $size);
        }

        $total = max(1, array_sum(array_map(fn ($s) => (float) $s['value'], $slices)));
        $cx = $size / 2;
        $cy = $size / 2;
        $r = ($size / 2) - 18;
        $inner = $r * 0.58;
        $angle = -90;
        $paths = '';

        foreach ($slices as $i => $slice) {
            $value = (float) $slice['value'];
            $sweep = ($value / $total) * 360;
            $color = $colors[$i % count($colors)];
            $paths .= $this->donutSegment($cx, $cy, $r, $inner, $angle, $angle + $sweep, $color);
            $angle += $sweep;
        }

        return $this->wrapSvg($size, $size, $paths);
    }

    /**
     * @param  list<array{0: float, 1: float}>  $points
     */
    private function pathFromPoints(array $points): string
    {
        if ($points === []) {
            return '';
        }
        $d = '';
        foreach ($points as $i => [$x, $y]) {
            $d .= sprintf('%s%.1f,%.1f ', $i === 0 ? 'M' : 'L', $x, $y);
        }

        return trim($d);
    }

    private function donutSegment(float $cx, float $cy, float $r, float $inner, float $start, float $end, string $color): string
    {
        if ($end - $start < 0.01) {
            return '';
        }
        $large = ($end - $start) > 180 ? 1 : 0;
        $p = fn ($radius, $deg) => [
            $cx + ($radius * cos(deg2rad($deg))),
            $cy + ($radius * sin(deg2rad($deg))),
        ];
        [$x1, $y1] = $p($r, $start);
        [$x2, $y2] = $p($r, $end);
        [$x3, $y3] = $p($inner, $end);
        [$x4, $y4] = $p($inner, $start);

        return sprintf(
            '<path d="M%.2f,%.2f A%.2f,%.2f 0 %d 1 %.2f,%.2f L%.2f,%.2f A%.2f,%.2f 0 %d 0 %.2f,%.2f Z" fill="%s"/>',
            $x1,
            $y1,
            $r,
            $r,
            $large,
            $x2,
            $y2,
            $x3,
            $y3,
            $inner,
            $inner,
            $large,
            $x4,
            $y4,
            $color
        );
    }

    private function wrapSvg(int $width, int $height, string $body): string
    {
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">%s</svg>',
            $width,
            $height,
            $width,
            $height,
            $body
        );
    }

    private function emptySvg(int $width, int $height): string
    {
        return $this->wrapSvg(
            $width,
            $height,
            '<rect width="100%" height="100%" fill="#f7f7f7"/><text x="50%" y="50%" text-anchor="middle" fill="#999" font-size="12">No chart data</text>'
        );
    }
}
