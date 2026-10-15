<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CanteenUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'user' => ['nullable', 'integer'],
        ]);
        $today = CarbonImmutable::today(config('app.timezone'));
        $search = trim($validated['q'] ?? '');

        $users = CanteenUser::query()
            ->where('is_active', true)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('barcode_id', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $selectedUser = isset($validated['user'])
            ? CanteenUser::query()->where('is_active', true)->find($validated['user'])
            : null;

        $selectedEvents = $selectedUser
            ? $selectedUser->attendances()->whereDate('attendance_date', $today)->orderBy('checked_at')->get()
            : collect();
        $hasCheckedIn = $selectedEvents->contains('type', 'Masuk');
        $hasCheckedOut = $selectedEvents->contains('type', 'Pulang');
        $selectedStatus = ! $hasCheckedIn ? 'Belum hadir' : (! $hasCheckedOut ? 'Sudah masuk' : 'Selesai');

        $presentCount = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->where('type', 'Masuk')
            ->whereNotNull('canteen_user_id')
            ->distinct()
            ->count('canteen_user_id');
        $totalUsers = CanteenUser::query()->where('is_active', true)->count();
        $todayLogs = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->orderByDesc('checked_at')
            ->limit(7)
            ->get();

        return view('home', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'selectedStatus' => $selectedStatus,
            'today' => $today,
            'todayLogs' => $todayLogs,
            'presentCount' => $presentCount,
            'awayCount' => max(0, $totalUsers - $presentCount),
            'totalUsers' => $totalUsers,
            'lastAttendance' => $todayLogs->first(),
            'search' => $search,
        ]);
    }
}
