<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::with('semesters')->latest()->get();
        return view('admin.school-years.index', compact('schoolYears'));
    }

    public function create()
    {
        return view('admin.school-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_start'  => 'required|digits:4|integer',
            'year_end'    => 'required|digits:4|integer|gt:year_start',
            'semesters'   => 'required|array|min:1',
            'semesters.*' => 'in:1st Semester,2nd Semester,Summer',
        ]);

        $sy = SchoolYear::create([
            'year_start' => $request->year_start,
            'year_end'   => $request->year_end,
        ]);

        // Preserve a consistent display order: 1st → 2nd → Summer
        $order = ['1st Semester' => 1, '2nd Semester' => 2, 'Summer' => 3];
        $semesters = collect($request->semesters)
            ->sortBy(fn($s) => $order[$s] ?? 99);

        foreach ($semesters as $sem) {
            Semester::create([
                'school_year_id' => $sy->id,
                'semester_name'  => $sem,
            ]);
        }

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$sy->year_start}–{$sy->year_end} created.");
    }

    public function show(SchoolYear $schoolYear)
    {
        $schoolYear->load('semesters');
        return view('admin.school-years.show', compact('schoolYear'));
    }

    public function edit(SchoolYear $schoolYear)
    {
        $schoolYear->load('semesters');
        return view('admin.school-years.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $request->validate([
            'year_start'  => 'required|digits:4|integer',
            'year_end'    => 'required|digits:4|integer|gt:year_start',
            'semesters'   => 'required|array|min:1',
            'semesters.*' => 'in:1st Semester,2nd Semester,Summer',
        ]);

        $schoolYear->update([
            'year_start' => $request->year_start,
            'year_end'   => $request->year_end,
        ]);

        // Sync semesters: delete removed ones, create new ones, leave existing untouched
        $existing  = $schoolYear->semesters->pluck('semester_name')->toArray();
        $submitted = $request->semesters;

        $toDelete = array_diff($existing, $submitted);
        $toAdd    = array_diff($submitted, $existing);

        $schoolYear->semesters()
            ->whereIn('semester_name', $toDelete)
            ->delete();

        $order = ['1st Semester' => 1, '2nd Semester' => 2, 'Summer' => 3];
        collect($toAdd)
            ->sortBy(fn($s) => $order[$s] ?? 99)
            ->each(fn($sem) => Semester::create([
                'school_year_id' => $schoolYear->id,
                'semester_name'  => $sem,
            ]));

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$schoolYear->year_start}–{$schoolYear->year_end} updated.");
    }

    public function destroy(SchoolYear $schoolYear)
    {
        $schoolYear->delete();
        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year deleted.');
    }
}