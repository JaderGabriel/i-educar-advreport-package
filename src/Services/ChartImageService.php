<?php

namespace iEducar\Packages\AdvancedReports\Services;

class ChartImageService
{
    /**
     * Retorna um data-URI PNG com um gráfico de barras simples.
     *
     * @param array<string, int|float> $series label => value
     */
    public function barPngDataUri(array $series, string $title = '', int $width = 900, int $height = 320): string
    {
        if (!extension_loaded('gd')) {
            return '';
        }

        $img = imagecreatetruecolor($width, $height);
        if (!$img) {
            return '';
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 20, 20, 20);
        $grid = imagecolorallocate($img, 235, 235, 235);
        $bar = imagecolorallocate($img, 54, 162, 235);

        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $paddingTop = 28;
        $paddingLeft = 18;
        $paddingRight = 14;
        $paddingBottom = 22;

        if ($title !== '') {
            imagestring($img, 5, $paddingLeft, 6, $this->truncate($title, 90), $black);
        }

        $plotX1 = $paddingLeft;
        $plotY1 = $paddingTop;
        $plotX2 = $width - $paddingRight;
        $plotY2 = $height - $paddingBottom;

        $values = array_values($series);
        $max = 0.0;
        foreach ($values as $v) {
            $max = max($max, (float) $v);
        }
        if ($max <= 0) {
            $max = 1.0;
        }

        // grid
        $gridLines = 4;
        for ($i = 0; $i <= $gridLines; $i++) {
            $y = (int) ($plotY1 + (($plotY2 - $plotY1) * $i / $gridLines));
            imageline($img, $plotX1, $y, $plotX2, $y, $grid);
        }

        $count = max(1, count($series));
        $gap = 10;
        $barWidth = (int) floor((($plotX2 - $plotX1) - ($gap * ($count + 1))) / $count);
        $barWidth = max(6, $barWidth);

        $i = 0;
        foreach ($series as $label => $value) {
            $x = (int) ($plotX1 + $gap + $i * ($barWidth + $gap));
            $h = (int) round((($plotY2 - $plotY1) * ((float) $value / $max)));
            $y1 = $plotY2 - $h;
            $y2 = $plotY2;

            imagefilledrectangle($img, $x, $y1, $x + $barWidth, $y2, $bar);

            // value
            $valStr = (string) (int) $value;
            imagestring($img, 2, $x + 2, max($plotY1, $y1 - 14), $valStr, $black);

            // label (truncated)
            imagestring($img, 2, $x + 2, $plotY2 + 4, $this->truncate($label, 14), $black);

            $i++;
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        if (!$png) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode($png);
    }

    private function truncate(string $text, int $max): string
    {
        $t = trim($text);
        if (mb_strlen($t) <= $max) {
            return $t;
        }

        return mb_substr($t, 0, $max - 1) . '…';
    }
}

