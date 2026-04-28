<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class MasterListParser
{
    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);
        $text   = $pdf->getText();

        return $this->extractRows($text);
    }

    private function extractRows(string $text): array
    {
        $rows  = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Must start with a row number e.g. "1. ..."
            if (! preg_match('/^(\d+)\.\s+(.*)$/', $line, $outer)) {
                continue;
            }

            $rowNumber = (int) $outer[1];
            $rest      = trim($outer[2]);

            // ── Match both PDF types ──────────────────────────────────────
            // Type A: Name  Code  [T]  Grade
            // Type B: Name  Code  [T]  Grade  PR  Remarks
            //
            // Pattern: everything up to the last two standalone numbers
            // before any trailing text (PR + Remarks or nothing)
            //
            // (\d+)        → [T]  raw score  (integer)
            // (\d+\.\d+)   → Grade           (decimal like 92.00)
            // (?:\s+.*)?   → optional trailing columns (PR, Remarks)

            if (! preg_match(
                '/^(.*?)\s+(\d+)\s+(\d+\.\d+)(?:\s+.*)?$/i',
                $rest,
                $m
            )) {
                continue;
            }

            $before     = trim($m[1]); // Name + Code portion
            $rawScore   = (int)   $m[2];
            $pdfGrade   = (float) $m[3]; // original PDF grade (used to derive total)

            // ── Derive total items from PDF grade ─────────────────────────
            // PDF used: Grade = (T / total) * 100
            // So:       total = T / (Grade / 100)
            $totalItems = 0;
            if ($pdfGrade > 0 && $rawScore > 0) {
                $totalItems = (int) round($rawScore / ($pdfGrade / 100));
            }

            // ── Split name and student code from $before ──────────────────
            $studentName = '';
            $studentCode = '';

            // Student code = 7–10 digit number at the END of $before
            if (preg_match('/^(.*?)\s+(\d{7,10})\s*$/', $before, $nc)) {
                $studentName = trim($nc[1]);
                $studentCode = trim($nc[2]);
            } else {
                $studentName = $before;
                $studentCode = '';
            }

            $flagged = empty($studentName) || empty($studentCode);

            $rows[] = [
                'row_number'   => $rowNumber,
                'student_name' => $studentName,
                'student_code' => $studentCode,
                'raw_score'    => $rawScore,
                'total_items'  => $totalItems, // derived — used by controller to recalc
                'percentage'   => $pdfGrade,   // original PDF grade (overwritten by controller)
                'remark'       => $pdfGrade >= 75.0 ? 'pass' : 'fail',
                'flagged'      => $flagged,
            ];
        }

        return $rows;
    }
}