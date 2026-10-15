<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\CanteenUser;
use OverflowException;

class BarcodeIdGenerator
{
    public function next(): string
    {
        $barcodeIds = CanteenUser::query()
            ->where('barcode_id', 'like', 'KNT-%')
            ->pluck('barcode_id')
            ->merge(
                Attendance::query()
                    ->select('barcode_id')
                    ->where('barcode_id', 'like', 'KNT-%')
                    ->distinct()
                    ->pluck('barcode_id'),
            );
        $highestSequence = '0';

        foreach ($barcodeIds as $barcodeId) {
            if (preg_match('/\AKNT-(\d+)\z/i', $barcodeId, $matches) !== 1) {
                continue;
            }

            $sequence = ltrim($matches[1], '0') ?: '0';

            if (
                strlen($sequence) > strlen($highestSequence)
                || (strlen($sequence) === strlen($highestSequence) && strcmp($sequence, $highestSequence) > 0)
            ) {
                $highestSequence = $sequence;
            }
        }

        $nextSequence = $this->increment($highestSequence);

        if (strlen($nextSequence) + 4 > 24) {
            throw new OverflowException('No generated barcode IDs remain within the supported length.');
        }

        return 'KNT-'.str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
    }

    private function increment(string $number): string
    {
        $digits = str_split($number);

        for ($index = count($digits) - 1; $index >= 0; $index--) {
            if ($digits[$index] !== '9') {
                $digits[$index] = (string) ((int) $digits[$index] + 1);

                return implode('', $digits);
            }

            $digits[$index] = '0';
        }

        return '1'.implode('', $digits);
    }
}
