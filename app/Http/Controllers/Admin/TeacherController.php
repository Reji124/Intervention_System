<?php

// app/Http/Controllers/Admin/TeacherController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::withCount('teacherSubjects')
            ->with(['teacherSubjects.subject', 'teacherSubjects.semester.schoolYear'])
            ->latest()
            ->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $subjects  = Subject::with('department')->orderBy('subject_code')->get();
        $semesters = Semester::with('schoolYear')->latest()->get();
        return view('admin.teachers.create', compact('subjects', 'semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_name'           => 'required|string|max:255',
            'teacher_code'           => 'required|digits_between:4,10|unique:teachers,teacher_code',
            'email'                  => 'nullable|email|unique:teachers,email',
            'subjects'               => 'nullable|array',
            'subjects.*.subject_id'  => 'nullable|exists:subjects,id',
            'subjects.*.semester_id' => 'nullable|exists:semesters,id',
            'subjects.*.section'     => 'nullable|string|max:100',
        ]);

        $teacher = Teacher::create([
            'teacher_name' => $request->teacher_name,
            'teacher_code' => $request->teacher_code,
            'email'        => $request->email,
        ]);

        if ($request->subjects) {
            foreach ($request->subjects as $row) {
                if (
                    ! empty($row['subject_id']) &&
                    ! empty($row['semester_id']) &&
                    ! empty($row['section'])
                ) {
                    TeacherSubject::create([
                        'teacher_id'  => $teacher->id,
                        'subject_id'  => $row['subject_id'],
                        'semester_id' => $row['semester_id'],
                        'section'     => $row['section'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher {$teacher->teacher_name} created successfully.");
    }

    public function show(Teacher $teacher)
    {
        $teacher->load([
            'teacherSubjects.subject.department',
            'teacherSubjects.semester.schoolYear',
        ]);

        $subjects  = Subject::with('department')->orderBy('subject_code')->get();
        $semesters = Semester::with('schoolYear')->latest()->get();

        return view('admin.teachers.show', compact('teacher', 'subjects', 'semesters'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load(['teacherSubjects.subject', 'teacherSubjects.semester.schoolYear']);
        $subjects  = Subject::with('department')->orderBy('subject_code')->get();
        $semesters = Semester::with('schoolYear')->latest()->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects', 'semesters'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'teacher_name' => 'required|string|max:255',
            'teacher_code' => 'required|digits_between:4,10|unique:teachers,teacher_code,' . $teacher->id,
            'email'        => 'nullable|email|unique:teachers,email,' . $teacher->id,
        ]);

        $teacher->update([
            'teacher_name' => $request->teacher_name,
            'teacher_code' => $request->teacher_code,
            'email'        => $request->email,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher deleted.');
    }

    public function assignSubject(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'semester_id' => 'required|exists:semesters,id',
            'section'     => 'required|string|max:100',
        ]);

        $exists = TeacherSubject::where('teacher_id',  $teacher->id)
            ->where('subject_id',  $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->where('section',     $request->section)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This subject and section is already assigned to this teacher.');
        }

        TeacherSubject::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $request->subject_id,
            'semester_id' => $request->semester_id,
            'section'     => $request->section,
        ]);

        return back()->with('success', 'Subject assigned successfully.');
    }

    public function removeSubject(TeacherSubject $teacherSubject)
    {
        $teacherSubject->delete();
        return back()->with('success', 'Subject removed.');
    }
}