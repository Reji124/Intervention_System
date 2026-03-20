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
            'year_start'   => 'required|digits:4|integer',
            'year_end'     => 'required|digits:4|integer|gt:year_start',
            'semesters'    => 'required|array|min:1',
            'semesters.*' => 'in:1st,2nd',
        ]);

        $sy = SchoolYear::create([
            'year_start' => $request->year_start,
            'year_end'   => $request->year_end,
        ]);

        foreach ($request->semesters as $sem) {
            Semester::create([
                'school_year_id' => $sy->id,
                'semester_name'  => $sem,
            ]);
        }

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$sy->year_start}–{$sy->year_end} created.");
    }

    public function destroy(SchoolYear $schoolYear)
    {
        $schoolYear->delete();
        return redirect()->route('admin.school-years.index')
            ->with('success', 'School year deleted.');
    }
}