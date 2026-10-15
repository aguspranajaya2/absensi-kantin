<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CanteenUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scan' => ['required', 'string', 'max:24'],
            'source' => ['required', 'string', 'in:Scanner,QR / input,Kamera,Halaman absensi'],
        ]);
        $barcodeId = Str::upper(trim($validated['scan']));
        $today = CarbonImmutable::today(config('app.timezone'));
        $user = CanteenUser::query()
            ->where('barcode_id', $barcodeId)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'scan' => __('ui.messages.user_not_found'),
            ]);
        }

        try {
            $attendance = DB::transaction(function () use ($user, $today, $validated): Attendance {
                $lockedUser = CanteenUser::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

                if (! $lockedUser->is_active) {
                    throw ValidationException::withMessages([
                        'scan' => __('ui.messages.user_not_found'),
                    ]);
                }

                $events = $lockedUser->attendances()
                    ->whereDate('attendance_date', $today)
                    ->lockForUpdate()
                    ->get(['type']);

                if ($events->contains('type', 'Pulang')) {
                    throw ValidationException::withMessages([
                        'scan' => __('ui.messages.attendance_finished', ['name' => $lockedUser->name]),
                    ]);
                }

                $type = $events->contains('type', 'Masuk') ? 'Pulang' : 'Masuk';
                $now = CarbonImmutable::now(config('app.timezone'));

                return $lockedUser->attendances()->create([
                    'user_name' => $lockedUser->name,
                    'barcode_id' => $lockedUser->barcode_id,
                    'attendance_date' => $today->toDateString(),
                    'type' => $type,
                    'source' => $validated['source'],
                    'checked_at' => $now,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $hasRecordedToday = Attendance::query()
                ->where('canteen_user_id', $user->getKey())
                ->whereDate('attendance_date', $today)
                ->exists();

            if (! $hasRecordedToday) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'scan' => __('ui.messages.attendance_duplicate'),
            ]);
        }

        return redirect()->route('home')
            ->with('status', __('ui.messages.attendance_success', [
                'type' => __('ui.status.'.($attendance->type === 'Masuk' ? 'check_in' : 'check_out')),
                'name' => $attendance->user_name,
            ]));
    }
}
