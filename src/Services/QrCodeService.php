<?php

namespace iEducar\Packages\AdvancedReports\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    public function pngDataUri(string $text, int $scale = 4): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => $scale,
            'imageBase64' => true,
        ]);

        // Retorna `data:image/png;base64,...`
        return (new QRCode($options))->render($text);
    }
}

