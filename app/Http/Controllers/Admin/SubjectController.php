<?php

// app/Http/Controllers/Admin/SubjectController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subject;
use App\Models\SubjectCategory;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['department', 'course'])->orderBy('subject_name')->get();
return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
       $departments = Department::with('courses')->orderBy('department_name')->get();
        $allCourses  = \App\Models\Course::orderBy('course_name')->get();
return view('admin.subjects.create', compact('departments', 'allCourses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'course_id'     => 'required|exists:courses,id',
            'category'      => 'required|string|max:255',
            'subject_code'  => 'required|string|max:50|unique:subjects',
            'year_level'    => 'required|integer|min:1|max:5',
            'subject_name'  => 'required|string|max:255',
        ]);

        Subject::create($request->only([
            'department_id','course_id','category',
            'subject_code','year_level','subject_name',
        ]));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        $departments = Department::with('courses')->orderBy('department_name')->get();
return view('admin.subjects.edit', compact('subject', 'departments'));
        $categories  = SubjectCategory::orderBy('category_name')->get();
        return view('admin.subjects.edit', compact('subject', 'departments', 'categories'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'course_id'     => 'required|exists:courses,id',
            'category'      => 'required|string|max:255',
            'subject_code'  => 'required|string|max:50|unique:subjects',
            'year_level'    => 'required|integer|min:1|max:5',
            'subject_name'  => 'required|string|max:255',
        ]);

        Subject::create($request->only([
            'department_id','course_id','category',
            'subject_code','year_level','subject_name',
        ]));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted.');
    }
}