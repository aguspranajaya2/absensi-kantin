<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MonthlyAttendanceSummary;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function export(Request $request, MonthlyAttendanceSummary $summaryService): StreamedResponse
    {
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $summary = $summaryService->forMonth($validated['month']);

        return response()->streamDownload(function () use ($summary): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Rekap Absensi Kantin - '.$summary['label']], ',', '"', '');
            fputcsv($stream, [], ',', '"', '');
            fputcsv($stream, ['Nama', 'ID', 'Bagian', 'Hari hadir', 'Masuk', 'Pulang', 'Rata-rata jam masuk', 'Persentase hari kerja'], ',', '"', '');

            foreach ($summary['people'] as $row) {
                fputcsv($stream, [
                    $this->spreadsheetSafe($row['user']->name),
                    $this->spreadsheetSafe($row['user']->barcode_id),
                    $this->spreadsheetSafe($row['user']->department ?? ''),
                    $row['days'],
                    $row['ins'],
                    $row['outs'],
                    $this->minuteString($row['average_minutes']),
                    $row['percentage'].'%',
                ], ',', '"', '');
            }

            fclose($stream);
        }, 'rekap-absensi-'.$validated['month'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }

    private function minuteString(?int $minute): string
    {
        if ($minute === null) {
            return '—';
        }

        return sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
    }
}
