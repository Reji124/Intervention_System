<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Support\Collection;

/**
 * YearOverYearComparisonService
 * 
 * Compares teacher performance across consecutive school years.
 * Aggregates pass rates across all subjects for overall comparison.
 * Shows 4 data series: 2 sections × 2 years.
 */
class YearOverYearComparisonService
{
    private const EXAM_TYPES = [
        'prelim' => 'Prelim',
        'midterm' => 'Midterm',
        'prefinal' => 'Prefinal',
        'final' => 'Final',
    ];

    /**
     * Get year-over-year comparison data for a teacher.
     * 
     * Compares current school year with previous year, same semester type.
     * If no subject specified, aggregates across all teacher's subjects.
     * 
     * Returns array with structure:
     * [
     *   'current_year' => [
     *     'school_year' => 'SY 2025–2026',
     *     'semester_name' => '1st Semester',
     *     'sections' => [
     *       'A' => ['Prelim' => 85, 'Midterm' => 87, ...],
     *       'B' => ['Prelim' => 75, 'Midterm' => 78, ...],
     *     ]
     *   ],
     *   'previous_year' => [...],
     *   'exam_types' => ['Prelim', 'Midterm', 'Prefinal', 'Final'],
     *   'sections' => ['A', 'B'],
     * ]
     */
    public function getTeacherComparison(
        Teacher $teacher,
        Semester $currentSemester,
        ?Subject $subject = null
    ): array {
        // Get previous year's semester (same type)
        $previousSemester = $this->getPreviousSemester($currentSemester);

        if (!$previousSemester) {
            // No previous year data available
            return [
                'current_year' => $this->buildYearData($teacher, $currentSemester, $subject),
                'previous_year' => null,
                'exam_types' => array_values(self::EXAM_TYPES),
                'sections' => $this->getTeacherSections($teacher, $currentSemester),
            ];
        }

        return [
            'current_year' => $this->buildYearData($teacher, $currentSemester, $subject),
            'previous_year' => $this->buildYearData($teacher, $previousSemester, $subject),
            'exam_types' => array_values(self::EXAM_TYPES),
            'sections' => array_unique(array_merge(
                $this->getTeacherSections($teacher, $currentSemester),
                $this->getTeacherSections($teacher, $previousSemester)
            )),
        ];
    }

    /**
     * Build year data structure for a semester.
     */
    private function buildYearData(Teacher $teacher, Semester $semester, ?Subject $subject): array
    {
        $query = $teacher->teacherSubjects()
            ->where('semester_id', $semester->id)
            ->with(['exams.examResults', 'subject']);

        if ($subject) {
            $query->where('subject_id', $subject->id);
        }

        $teacherSubjects = $query->get();

        if ($teacherSubjects->isEmpty()) {
            return [
                'school_year' => $semester->schoolYear->year_label ?? 'N/A',
                'semester_name' => $semester->semester_name,
                'sections' => [],
            ];
        }

        // Group by section and calculate pass rates per exam type
        $sectionData = [];
        foreach ($teacherSubjects as $ts) {
            if (!isset($sectionData[$ts->section])) {
                $sectionData[$ts->section] = array_fill_keys(
                    array_keys(self::EXAM_TYPES),
                    null
                );
            }

            // Calculate pass rates for each exam type
            $examPassRates = $this->getExamPassRates($ts);
            foreach ($examPassRates as $examType => $passRate) {
                if ($sectionData[$ts->section][$examType] === null) {
                    $sectionData[$ts->section][$examType] = $passRate;
                } else {
                    // Average if multiple teacher-subjects for same section
                    $sectionData[$ts->section][$examType] = (
                        ($sectionData[$ts->section][$examType] + $passRate) / 2
                    );
                }
            }
        }

        // Convert exam type keys to labels for UI
        $formattedSections = [];
        foreach ($sectionData as $section => $examData) {
            $formattedSections[$section] = [];
            foreach (self::EXAM_TYPES as $examType => $label) {
                $formattedSections[$section][$label] = isset($examData[$examType])
                    ? (int) round($examData[$examType])
                    : null;
            }
        }

        return [
            'school_year' => $semester->schoolYear->year_label ?? 'N/A',
            'semester_name' => $semester->semester_name,
            'sections' => $formattedSections,
        ];
    }

    /**
     * Calculate pass rate for each exam type for a teacher-subject.
     */
    private function getExamPassRates($teacherSubject): array
    {
        $passRates = [];

        foreach (self::EXAM_TYPES as $examType => $label) {
            $exams = $teacherSubject->exams
                ->where('exam_type', $examType);

            if ($exams->isEmpty()) {
                $passRates[$examType] = 0;
                continue;
            }

            $totalResults = 0;
            $passCount = 0;

            foreach ($exams as $exam) {
                foreach ($exam->examResults as $result) {
                    $totalResults++;
                    if ($result->remark === 'pass') {
                        $passCount++;
                    }
                }
            }

            $passRates[$examType] = $totalResults > 0
                ? ($passCount / $totalResults) * 100
                : 0;
        }

        return $passRates;
    }

    /**
     * Get unique sections a teacher has in a semester.
     */
    private function getTeacherSections(Teacher $teacher, Semester $semester): array
    {
        return $teacher->teacherSubjects()
            ->where('semester_id', $semester->id)
            ->distinct('section')
            ->pluck('section')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Find the previous semester of same type in previous school year.
     * E.g., if current is 1st Semester 2025-2026, returns 1st Semester 2024-2025.
     */
    private function getPreviousSemester(Semester $currentSemester): ?Semester
    {
        // Get the previous school year
        $previousYear = $currentSemester->schoolYear()
            ->where('year_end', $currentSemester->schoolYear->year_start - 1)
            ->first();

        if (!$previousYear) {
            return null;
        }

        // Find same semester type in previous year
        return Semester::where('school_year_id', $previousYear->id)
            ->where('semester_name', $currentSemester->semester_name)
            ->first();
    }
}
