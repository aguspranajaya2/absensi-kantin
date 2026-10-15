<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['canteen_user_id', 'user_name', 'barcode_id', 'attendance_date', 'type', 'source', 'checked_at', 'legacy_id'])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'checked_at' => 'datetime',
        ];
    }

    public function canteenUser(): BelongsTo
    {
        return $this->belongsTo(CanteenUser::class);
    }
}
