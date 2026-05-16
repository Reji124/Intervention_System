<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('grading_method')->default('base_50')->after('exam_type');
            // 'base_50' → Final Grade = 50 + (score / total × 50)
            // 'base_20' → Final Grade = 20 + (score / total × 80)
            // 'base_0'  → Final Grade = score / total × 100
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('grading_method');
        });
    }
};
