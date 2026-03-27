<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add uploaded_by to exams if missing
        if (Schema::hasTable('exams') && !Schema::hasColumn('exams', 'uploaded_by')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->foreignId('uploaded_by')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
            });
        }

        // Create teacher_notes if missing
        if (!Schema::hasTable('teacher_notes')) {
            Schema::create('teacher_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')
                      ->constrained()
                      ->cascadeOnDelete();
                $table->foreignId('semester_id')
                      ->nullable()
                      ->constrained()
                      ->nullOnDelete();
                $table->string('status')->default('no_status');
                $table->text('notes')->nullable();
                $table->foreignId('updated_by')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
                $table->timestamps();
                $table->unique(['teacher_id', 'semester_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_notes');

        if (Schema::hasTable('exams') && Schema::hasColumn('exams', 'uploaded_by')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropConstrainedForeignId('uploaded_by');
            });
        }
    }
};