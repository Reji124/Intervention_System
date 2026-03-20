<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('courses')
            ->withCount('subjects')
            ->orderBy('department_name')
            ->get();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_name'  => 'required|string|max:255|unique:departments',
            'courses'          => 'nullable|array',
            'courses.*'        => 'required|string|max:255',
        ]);

        $department = Department::create(['department_name' => $data['department_name']]);

        foreach (array_filter($data['courses'] ?? []) as $courseName) {
            $department->courses()->create(['course_name' => $courseName]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        $department->load('courses');
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'department_name'    => 'required|string|max:255|unique:departments,department_name,' . $department->id,
            'courses'            => 'nullable|array',
            'courses.*.id'       => 'nullable|integer|exists:courses,id',
            'courses.*.name'     => 'required|string|max:255',
        ]);

        $department->update(['department_name' => $data['department_name']]);

        $submittedIds = [];
        foreach ($data['courses'] ?? [] as $courseData) {
            if (!empty($courseData['id'])) {
                $course = $department->courses()->find($courseData['id']);
                $course?->update(['course_name' => $courseData['name']]);
                $submittedIds[] = $courseData['id'];
            } else {
                $new = $department->courses()->create(['course_name' => $courseData['name']]);
                $submittedIds[] = $new->id;
            }
        }

        // Delete removed courses
        $department->courses()->whereNotIn('id', $submittedIds)->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}