<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['courses.department'])
                        ->orderBy('subject_name')
                        ->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        $departments = Department::with('courses')->orderBy('department_name')->get();

        return view('admin.subjects.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_code'   => 'required|string|max:50|unique:subjects,subject_code',
            'subject_name'   => 'required|string|max:255',
            'category'       => 'required|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            // assignments: array of {department_id, course_id} pairs
            'assignments'            => 'required|array|min:1',
            'assignments.*.department_id' => 'required|exists:departments,id',
            'assignments.*.course_id'     => 'required|exists:courses,id',
        ]);

        // Validate no duplicate dept+course pairs within the submission itself
        $pairs = collect($data['assignments'])->map(fn($a) => $a['department_id'].'-'.$a['course_id']);
        if ($pairs->count() !== $pairs->unique()->count()) {
            return back()->withInput()
                ->withErrors(['assignments' => 'Duplicate department + course combination in your selections.']);
        }

        $subject = Subject::create([
            'subject_code' => $data['subject_code'],
            'subject_name' => $data['subject_name'],
            'category'     => $data['category'],
            'year_level'   => $data['year_level'],
        ]);

        // Attach each dept+course assignment
        foreach ($data['assignments'] as $assignment) {
            $subject->courses()->attach($assignment['course_id'], [
                'department_id' => $assignment['department_id'],
            ]);
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        $departments = Department::with('courses')->orderBy('department_name')->get();
        $subject->load('courses'); // includes pivot department_id

        return view('admin.subjects.edit', compact('subject', 'departments'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'subject_code'   => ['required','string','max:50', Rule::unique('subjects','subject_code')->ignore($subject->id)],
            'subject_name'   => 'required|string|max:255',
            'category'       => 'required|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'assignments'            => 'required|array|min:1',
            'assignments.*.department_id' => 'required|exists:departments,id',
            'assignments.*.course_id'     => 'required|exists:courses,id',
        ]);

        $pairs = collect($data['assignments'])->map(fn($a) => $a['department_id'].'-'.$a['course_id']);
        if ($pairs->count() !== $pairs->unique()->count()) {
            return back()->withInput()
                ->withErrors(['assignments' => 'Duplicate department + course combination in your selections.']);
        }

        $subject->update([
            'subject_code' => $data['subject_code'],
            'subject_name' => $data['subject_name'],
            'category'     => $data['category'],
            'year_level'   => $data['year_level'],
        ]);

        // Rebuild pivot: detach all, then re-attach
        $subject->courses()->detach();
        foreach ($data['assignments'] as $assignment) {
            $subject->courses()->attach($assignment['course_id'], [
                'department_id' => $assignment['department_id'],
            ]);
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete(); // pivot rows cascade via DB constraint
        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted.');
    }
}