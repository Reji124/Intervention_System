<?php

// routes/web.php — COMPLETE FILE

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('assistant.dashboard');
    }
    return redirect()->route('login');
});

// ── ADMIN ROUTES ──────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
             ->name('dashboard');

        Route::resource('school-years', \App\Http\Controllers\Admin\SchoolYearController::class);
        Route::resource('departments',  \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('subjects',     \App\Http\Controllers\Admin\SubjectController::class);
        Route::resource('teachers',     \App\Http\Controllers\Admin\TeacherController::class);
        Route::resource('users',        \App\Http\Controllers\Admin\UserController::class);

        // Profile
        Route::get('profile',
            [\App\Http\Controllers\Admin\ProfileController::class, 'index'])
            ->name('profile');
        Route::patch('profile',
            [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
            ->name('profile.update');
        Route::post('profile/admins',
            [\App\Http\Controllers\Admin\ProfileController::class, 'storeAdmin'])
            ->name('profile.admins.store');
        Route::delete('profile/admins/{user}',
            [\App\Http\Controllers\Admin\ProfileController::class, 'destroyAdmin'])
            ->name('profile.admins.destroy');

        // Interventions — main view
        Route::get('interventions',
            [\App\Http\Controllers\Admin\InterventionController::class, 'index'])
            ->name('interventions.index');

        // Exam result CRUD
        Route::patch('exam-results/{examResult}',
            [\App\Http\Controllers\Admin\InterventionController::class, 'updateResult'])
            ->name('exam-results.update');
        Route::delete('exam-results/{examResult}',
            [\App\Http\Controllers\Admin\InterventionController::class, 'destroyResult'])
            ->name('exam-results.destroy');
        Route::delete('exams/{exam}',
            [\App\Http\Controllers\Admin\InterventionController::class, 'destroyExam'])
            ->name('exams.destroy');

        // Teacher notes (upsert = create or update)
        Route::post('teachers/{teacher}/note',
            [\App\Http\Controllers\Admin\InterventionController::class, 'upsertNote'])
            ->name('teachers.note.upsert');

        // Mass delete (filtered)
        Route::delete('interventions/mass-delete',
            [\App\Http\Controllers\Admin\InterventionController::class, 'massDelete'])
            ->name('interventions.mass-delete');

        // CSV export (filtered)
        Route::get('interventions/export',
            [\App\Http\Controllers\Admin\InterventionController::class, 'exportCsv'])
            ->name('interventions.export');

        Route::post('teachers/{teacher}/assign-subject',
            [\App\Http\Controllers\Admin\TeacherController::class, 'assignSubject'])
            ->name('teachers.assign-subject');
        Route::delete('teacher-subjects/{teacherSubject}/remove',
            [\App\Http\Controllers\Admin\TeacherController::class, 'removeSubject'])
            ->name('teachers.remove-subject');
    });

// ── ASSISTANT ROUTES ──────────────────────────────────────────────────────────
Route::prefix('assistant')
    ->name('assistant.')
    ->middleware(['auth', 'role:assistant'])
    ->group(function () {

        Route::get('/',
            [\App\Http\Controllers\Assistant\DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('subjects',
            [\App\Http\Controllers\Assistant\SubjectController::class, 'index'])
            ->name('subjects.index');
        Route::get('subjects/{teacherSubject}',
            [\App\Http\Controllers\Assistant\SubjectController::class, 'show'])
            ->name('subjects.show');

        Route::get('upload',
            [\App\Http\Controllers\Assistant\PdfUploadController::class, 'index'])
            ->name('upload.index');
        Route::get('upload/subjects-for-teacher',
            [\App\Http\Controllers\Assistant\PdfUploadController::class, 'subjectsForTeacher'])
            ->name('upload.subjects-for-teacher');
        Route::post('upload/parse',
            [\App\Http\Controllers\Assistant\PdfUploadController::class, 'parse'])
            ->name('upload.parse');
        Route::post('upload/store',
            [\App\Http\Controllers\Assistant\PdfUploadController::class, 'store'])
            ->name('upload.store');

        Route::get('interventions',
            [\App\Http\Controllers\Assistant\InterventionController::class, 'index'])
            ->name('interventions.index');
        Route::patch('exam-results/{examResult}',
            [\App\Http\Controllers\Assistant\InterventionController::class, 'updateResult'])
            ->name('exam-results.update');
        Route::delete('exam-results/{examResult}',
            [\App\Http\Controllers\Assistant\InterventionController::class, 'destroyResult'])
            ->name('exam-results.destroy');
    });

Route::get('/dashboard', function () {
    return Auth::user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('assistant.dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';