<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', 'in:Masuk,Pulang'],
        ]);
        $search = trim($filters['q'] ?? '');
        $query = Attendance::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('user_name', 'like', '%'.$search.'%')
                        ->orWhere('barcode_id', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['date']), fn (Builder $query): Builder => $query->whereDate('attendance_date', $filters['date']))
            ->when(isset($filters['type']), fn (Builder $query): Builder => $query->where('type', $filters['type']));

        return response()->streamDownload(function () use ($query): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Tanggal', 'Waktu WITA', 'Nama', 'ID', 'Jenis', 'Sumber'], ',', '"', '');

            $query->orderBy('checked_at')->chunkById(500, function (EloquentCollection $records) use ($stream): void {
                foreach ($records as $record) {
                    fputcsv($stream, [
                        $record->attendance_date->format('d/m/Y'),
                        $record->checked_at->format('H:i'),
                        $this->spreadsheetSafe($record->user_name),
                        $this->spreadsheetSafe($record->barcode_id),
                        $record->type,
                        $this->spreadsheetSafe($record->source),
                    ], ',', '"', '');
                }
            });

            fclose($stream);
        }, 'riwayat-absensi.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
