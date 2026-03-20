<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Fix enum to include prelim
            $table->enum('exam_type', ['prelim', 'midterm', 'final'])
                  ->default('prelim')
                  ->change();

            // Only add item_matrix_data — item_analysis_path already exists in this table
            $table->json('item_matrix_data')->nullable()->after('item_analysis_path');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('exam_type', ['midterm', 'final'])->change();
            $table->dropColumn('item_matrix_data');
        });
    }
};