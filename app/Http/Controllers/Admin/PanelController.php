<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CanteenUser;
use App\Support\BarcodeIdGenerator;
use App\Support\MonthlyAttendanceSummary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(
        Request $request,
        MonthlyAttendanceSummary $summaryService,
        BarcodeIdGenerator $barcodeIds,
    ): View {
        $validated = $request->validate([
            'tab' => ['nullable', 'in:users,history,recap'],
            'q' => ['nullable', 'string', 'max:80'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', 'in:Masuk,Pulang'],
            'month' => ['nullable', 'date_format:Y-m'],
            'edit' => ['nullable', 'integer'],
        ]);
        $tab = $validated['tab'] ?? 'users';
        $search = trim($validated['q'] ?? '');
        $users = null;
        $attendances = null;
        $summary = null;
        $editing = null;

        if ($tab === 'users') {
            $users = CanteenUser::query()
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('barcode_id', 'like', '%'.$search.'%')
                            ->orWhere('department', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString();

            if (isset($validated['edit'])) {
                $editing = CanteenUser::query()->findOrFail($validated['edit']);
            }
        } elseif ($tab === 'history') {
            $attendances = $this->attendanceQuery($validated, $search)
                ->orderByDesc('checked_at')
                ->paginate(50)
                ->withQueryString();
        } else {
            $month = $validated['month'] ?? CarbonImmutable::now(config('app.timezone'))->format('Y-m');
            $summary = $summaryService->forMonth($month);
        }

        return view('admin.index', [
            'tab' => $tab,
            'search' => $search,
            'users' => $users,
            'attendances' => $attendances,
            'summary' => $summary,
            'editing' => $editing,
            'createModal' => $request->boolean('create'),
            'dateFilter' => $validated['date'] ?? '',
            'typeFilter' => $validated['type'] ?? '',
            'month' => $validated['month'] ?? CarbonImmutable::now(config('app.timezone'))->format('Y-m'),
            'userCount' => CanteenUser::query()->count(),
            'newBarcodeId' => $tab === 'users' ? $barcodeIds->next() : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function attendanceQuery(array $filters, string $search): Builder
    {
        return Attendance::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('user_name', 'like', '%'.$search.'%')
                        ->orWhere('barcode_id', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['date']), fn (Builder $query): Builder => $query->whereDate('attendance_date', $filters['date']))
            ->when(isset($filters['type']), fn (Builder $query): Builder => $query->where('type', $filters['type']));
    }
}
