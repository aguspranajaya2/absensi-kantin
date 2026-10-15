<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 80);
            $table->string('barcode_id', 24)->index();
            $table->date('attendance_date');
            $table->string('type', 12);
            $table->string('source', 30);
            $table->dateTime('checked_at');
            $table->string('legacy_id', 80)->nullable()->unique();
            $table->unique(['canteen_user_id', 'attendance_date', 'type'], 'attendance_user_day_type_unique');
            $table->index(['attendance_date', 'checked_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
