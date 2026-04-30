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

        $fontPath = $this->resolveFontPath();
        $useTtf = $fontPath !== null && function_exists('imagettftext');

        $img = imagecreatetruecolor($width, $height);
        if (!$img) {
            return '';
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 20, 20, 20);
        $grid = imagecolorallocate($img, 235, 235, 235);
        $palette = [
            [37, 99, 235],   // azul
            [22, 163, 74],   // verde
            [234, 88, 12],   // laranja
            [220, 38, 38],   // vermelho
            [124, 58, 237],  // roxo
            [14, 116, 144],  // ciano
            [190, 24, 93],   // rosa
            [161, 98, 7],    // amarelo/mostarda
        ];

        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        $paddingTop = 28;
        $paddingLeft = 18;
        $paddingRight = 14;
        $paddingBottom = 22;

        if ($title !== '') {
            $t = $this->truncate($title, 90);
            if ($useTtf) {
                // 12px aprox.
                imagettftext($img, 12, 0, $paddingLeft, 18, $black, $fontPath, $t);
            } else {
                imagestring($img, 5, $paddingLeft, 6, $this->toAsciiFallback($t), $black);
            }
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

            $rgb = $palette[$i % count($palette)];
            $barColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle($img, $x, $y1, $x + $barWidth, $y2, $barColor);

            // value
            $valStr = (string) (int) $value;
            if ($useTtf) {
                imagettftext($img, 10, 0, $x + 2, max($plotY1 + 10, $y1 - 4), $black, $fontPath, $valStr);
            } else {
                imagestring($img, 2, $x + 2, max($plotY1, $y1 - 14), $valStr, $black);
            }

            // label (truncated)
            $lbl = $this->truncate($label, 14);
            if ($useTtf) {
                imagettftext($img, 9, 0, $x + 2, $plotY2 + 14, $black, $fontPath, $lbl);
            } else {
                imagestring($img, 2, $x + 2, $plotY2 + 4, $this->toAsciiFallback($lbl), $black);
            }

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

    private function resolveFontPath(): ?string
    {
        $candidates = [
            // distros comuns
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $p) {
            if (is_string($p) && $p !== '' && file_exists($p)) {
                return $p;
            }
        }

        return null;
    }

    private function toAsciiFallback(string $text): string
    {
        // Quando TTF não estiver disponível, evita “texto quebrado” removendo acentos.
        $t = $text;
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return $t;
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

