<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement('DROP TABLE IF EXISTS exam_results CASCADE');
        DB::statement('DROP TABLE IF EXISTS students CASCADE');
        DB::statement('DROP TABLE IF EXISTS exams CASCADE');
        DB::statement('DROP TABLE IF EXISTS teacher_subjects CASCADE');
        DB::statement('DROP TABLE IF EXISTS teachers CASCADE');
        DB::statement('DROP TABLE IF EXISTS subjects CASCADE');
        DB::statement('DROP TABLE IF EXISTS courses CASCADE');
        DB::statement('DROP TABLE IF EXISTS departments CASCADE');
        DB::statement('DROP TABLE IF EXISTS semesters CASCADE');
        DB::statement('DROP TABLE IF EXISTS school_years CASCADE');

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->year('year_start');
            $table->year('year_end');
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('semester_name');
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('semester_name'); // '1st' or '2nd'
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('course_name');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_code');
            $table->string('subject_name');
            $table->string('year_level');
            $table->string('category'); // General, Major, ReEd
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('teacher_name');
            $table->string('teacher_code')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_subject_id')->constrained()->cascadeOnDelete();
            $table->string('exam_type'); // 'prelim', 'midterm', 'final'
            $table->string('item_analysis_path')->nullable();
            $table->json('item_matrix_data')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_subject_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');
            $table->string('student_code')->unique();
            $table->timestamps();
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->integer('raw_score');
            $table->decimal('percentage', 5, 2);
            $table->string('remark'); // 'pass' or 'fail'
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('students');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('school_years');
    }
};