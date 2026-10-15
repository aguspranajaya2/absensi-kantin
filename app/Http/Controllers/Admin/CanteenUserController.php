<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportLegacyAttendanceRequest;
use App\Models\Attendance;
use App\Models\CanteenUser;
use App\Support\BarcodeIdGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CanteenUserController extends Controller
{
    public function store(Request $request, BarcodeIdGenerator $barcodeIds): RedirectResponse
    {
        $validated = $request->validate($this->profileRules());
        $validated['is_active'] = $request->boolean('is_active');
        $canteenUser = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $validated['barcode_id'] = $barcodeIds->next();

            try {
                $canteenUser = CanteenUser::query()->create($validated);

                break;
            } catch (QueryException $exception) {
                $sqlState = (string) $exception->getCode();

                if (! in_array($sqlState, ['23000', '23505'], true) || $attempt === 2) {
                    throw $exception;
                }
            }
        }

        if (! $canteenUser) {
            abort(409, __('ui.messages.id_in_use'));
        }

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('status', __('ui.messages.profile_created', ['id' => $canteenUser->barcode_id]));
    }

    public function update(Request $request, CanteenUser $canteenUser): RedirectResponse
    {
        $validated = $request->validate($this->profileRules());
        $validated['is_active'] = $request->boolean('is_active');
        $canteenUser->update($validated);

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('status', __('ui.messages.profile_updated'));
    }

    public function destroy(CanteenUser $canteenUser): RedirectResponse
    {
        $canteenUser->delete();

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('status', __('ui.messages.profile_deleted'));
    }

    public function importLegacy(ImportLegacyAttendanceRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $importedUsers = 0;
        $importedLogs = 0;

        DB::transaction(function () use ($validated, &$importedUsers, &$importedLogs): void {
            foreach ($validated['users'] ?? [] as $legacyUser) {
                $user = CanteenUser::query()->firstOrCreate(
                    ['barcode_id' => $legacyUser['id']],
                    [
                        'name' => trim($legacyUser['name']),
                        'department' => $legacyUser['department'] ?? null,
                        'phone' => $legacyUser['phone'] ?? null,
                        'email' => isset($legacyUser['email']) ? mb_strtolower($legacyUser['email']) : null,
                        'is_active' => true,
                    ],
                );

                if ($user->wasRecentlyCreated) {
                    $importedUsers++;
                }
            }

            foreach ($validated['logs'] ?? [] as $legacyLog) {
                $legacyId = $legacyLog['id'] ?? hash('sha256', json_encode($legacyLog, JSON_THROW_ON_ERROR));
                $legacyId = substr($legacyId, 0, 80);

                if (Attendance::query()->where('legacy_id', $legacyId)->exists()) {
                    continue;
                }

                $checkedAt = CarbonImmutable::parse($legacyLog['at'])->setTimezone(config('app.timezone'));
                $user = CanteenUser::query()->where('barcode_id', $legacyLog['userId'])->first();

                if ($user && Attendance::query()
                    ->where('canteen_user_id', $user->getKey())
                    ->whereDate('attendance_date', $checkedAt->toDateString())
                    ->where('type', $legacyLog['type'])
                    ->exists()) {
                    continue;
                }

                try {
                    Attendance::query()->create([
                        'canteen_user_id' => $user?->getKey(),
                        'user_name' => trim($legacyLog['name']),
                        'barcode_id' => $legacyLog['userId'],
                        'attendance_date' => $checkedAt->toDateString(),
                        'type' => $legacyLog['type'],
                        'source' => $legacyLog['source'] ?? 'Impor lama',
                        'checked_at' => $checkedAt,
                        'legacy_id' => $legacyId,
                    ]);
                    $importedLogs++;
                } catch (QueryException $exception) {
                    if (! Attendance::query()->where('legacy_id', $legacyId)->exists()) {
                        throw $exception;
                    }
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'imported_users' => $importedUsers,
                'imported_logs' => $importedLogs,
            ]);
        }

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('status', __('ui.messages.import_complete', [
                'users' => $importedUsers,
                'logs' => $importedLogs,
            ]));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'department' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
        ];
    }
}
