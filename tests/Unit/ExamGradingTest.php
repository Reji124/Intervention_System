<?php

namespace Tests\Unit;

use App\Models\Exam;
use PHPUnit\Framework\TestCase;

class ExamGradingTest extends TestCase
{
    public function test_base_zero_grading_uses_raw_percentage(): void
    {
        $exam = new Exam(['grading_method' => Exam::GRADING_METHOD_BASE_0]);

        $this->assertSame(75.0, $exam->computeFinalGrade(30, 40));
    }

    public function test_existing_grading_methods_still_compute_expected_scores(): void
    {
        $this->assertSame(87.5, Exam::computeGradeForMethod(Exam::GRADING_METHOD_BASE_50, 30, 40));
        $this->assertSame(80.0, Exam::computeGradeForMethod(Exam::GRADING_METHOD_BASE_20, 30, 40));
    }
}
