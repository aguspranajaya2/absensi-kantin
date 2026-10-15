<?php

namespace App\Models;

use Database\Factories\CanteenUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['barcode_id', 'name', 'department', 'phone', 'email', 'is_active'])]
class CanteenUser extends Model
{
    /** @use HasFactory<CanteenUserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
