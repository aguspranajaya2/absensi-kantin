<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\CanteenUser;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class MonthlyAttendanceSummary
{
    /**
     * @return array{
     *     people: list<array{user: CanteenUser, days: int, ins: int, outs: int, average_minutes: ?int, percentage: int}>,
     *     present_users: int,
     *     attendance_days: int,
     *     total_checkins: int,
     *     average_minutes: ?int,
     *     weekdays: int,
     *     month: string,
     *     label: string,
     *     start: string,
     *     end: string
     * }
     */
    public function forMonth(string $month): array
    {
        $timezone = config('app.timezone');
        $start = CarbonImmutable::parse($month.'-01', $timezone)->startOfMonth();
        $currentMonth = CarbonImmutable::now($timezone)->startOfMonth();

        if ($start->greaterThan($currentMonth)) {
            throw ValidationException::withMessages(['month' => __('ui.messages.past_month_only')]);
        }

        $end = $start->isSameMonth($currentMonth)
            ? CarbonImmutable::now($timezone)->endOfDay()
            : $start->endOfMonth()->endOfDay();
        $people = [];

        foreach (CanteenUser::query()->orderBy('name')->get() as $user) {
            $people[$user->getKey()] = [
                'user' => $user,
                'dates' => [],
                'ins' => 0,
                'outs' => 0,
                'minutes' => [],
            ];
        }

        $records = Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('checked_at')
            ->get(['id', 'canteen_user_id', 'type', 'checked_at', 'attendance_date']);
        $allCheckinMinutes = [];

        foreach ($records as $record) {
            $minutes = ((int) $record->checked_at->format('H')) * 60 + (int) $record->checked_at->format('i');

            if ($record->type === 'Masuk') {
                $allCheckinMinutes[] = $minutes;
            }

            if ($record->canteen_user_id === null || ! isset($people[$record->canteen_user_id])) {
                continue;
            }

            if ($record->type === 'Masuk') {
                $people[$record->canteen_user_id]['ins']++;
                $people[$record->canteen_user_id]['minutes'][] = $minutes;
                $people[$record->canteen_user_id]['dates'][$record->attendance_date->toDateString()] = true;
            } elseif ($record->type === 'Pulang') {
                $people[$record->canteen_user_id]['outs']++;
            }
        }

        $weekdays = 0;
        for ($date = $start->startOfDay(); $date->lessThanOrEqualTo($end); $date = $date->addDay()) {
            if ($date->dayOfWeekIso <= 5) {
                $weekdays++;
            }
        }

        $rows = [];
        $presentUsers = 0;
        $attendanceDays = 0;

        foreach ($people as $person) {
            $days = count($person['dates']);
            $presentUsers += $days > 0 ? 1 : 0;
            $attendanceDays += $days;
            $minutes = $person['minutes'];

            $rows[] = [
                'user' => $person['user'],
                'days' => $days,
                'ins' => $person['ins'],
                'outs' => $person['outs'],
                'average_minutes' => count($minutes) > 0 ? (int) round(array_sum($minutes) / count($minutes)) : null,
                'percentage' => $weekdays > 0 ? min(100, (int) round($days / $weekdays * 100)) : 0,
            ];
        }

        return [
            'people' => $rows,
            'present_users' => $presentUsers,
            'attendance_days' => $attendanceDays,
            'total_checkins' => $records->count(),
            'average_minutes' => count($allCheckinMinutes) > 0
                ? (int) round(array_sum($allCheckinMinutes) / count($allCheckinMinutes))
                : null,
            'weekdays' => $weekdays,
            'month' => $month,
            'label' => $start->locale(app()->currentLocale())->translatedFormat('F Y'),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }
}
