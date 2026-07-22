<?php

declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

final class QrCodeService
{
    public function png(string $text): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($text)
            ->size(320)
            ->margin(10)
            ->build();

        return $result->getString();
    }
}
