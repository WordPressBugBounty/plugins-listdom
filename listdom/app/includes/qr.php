<?php

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/** Local, reusable QR rendering without a graphics extension or external service. */
class LSD_QR
{
    public static function data_uri(string $content): string
    {
        if ($content === '' || !class_exists(Encoder::class)) return '';
        try
        {
            $matrix = Encoder::encode($content, ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        }
        catch (Throwable $exception)
        {
            // A QR image is optional; enrollment still supports the setup key and URI.
            return '';
        }
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();
        $path = '';
        // Four modules of white space on every side form the required quiet zone.
        for ($y = 0; $y < $height; $y++)
        {
            for ($x = 0; $x < $width; $x++)
            {
                if ($matrix->get($x, $y) !== 1) continue;
                $start = $x;
                while ($x + 1 < $width && $matrix->get($x + 1, $y) === 1) $x++;
                $length = $x - $start + 1;
                $path .= 'M' . ($start + 4) . ' ' . ($y + 4) . 'h' . $length . 'v1h-' . $length . 'z';
            }
        }
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 ' . ($width + 8) . ' ' . ($height + 8) . '" shape-rendering="crispEdges"><rect width="100%" height="100%" fill="#fff"></rect><path fill="#000" d="' . $path . '"></path></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
