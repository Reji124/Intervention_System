<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE exams MODIFY COLUMN exam_type ENUM('prelim', 'midterm', 'prefinal', 'final') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE exams MODIFY COLUMN exam_type ENUM('midterm', 'final') NOT NULL");
    }
};