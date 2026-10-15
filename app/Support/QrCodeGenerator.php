<?php

namespace App\Support;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeGenerator
{
    public function dataUri(string $value, int $size): string
    {
        $qrCode = QrCode::create($value)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize($size)
            ->setMargin(4);
        $svg = (new SvgWriter)->write(
            $qrCode,
            options: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
        )->getString();

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
