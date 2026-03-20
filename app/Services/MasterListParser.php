<?php

// app/Services/MasterListParser.php

namespace App\Services;

use Smalot\PdfParser\Parser;

class MasterListParser
{
    /**
     * Parse a Master List PDF and return an array of student rows.
     *
     * Each row:
     * [
     *   'row_number'    => int,
     *   'student_name'  => string,
     *   'student_code'  => string,
     *   'raw_score'     => int,
     *   'percentage'    => float,
     *   'remark'        => 'pass'|'fail',
     *   'flagged'       => bool,   // true if name or code is missing
     * ]
     */
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

            // Match lines that start with a row number, e.g.:
            // "2. ABCEDE, MICHAEL JR G 59843918 24 68.57"
            // "1. 23 65.71"   ← missing name/code (flagged)
            if (! preg_match('/^(\d+)\.\s+(.*)$/', $line, $outer)) {
                continue;
            }

            $rowNumber = (int) $outer[1];
            $rest      = trim($outer[2]);

            // Try to extract trailing: raw_score percentage
            // e.g. "59843918 24 68.57"  or  "23 65.71"
            if (! preg_match('/^(.*?)\s+(\d+)\s+([\d.]+)\s*$/', $rest, $m)) {
                continue;
            }

            $before     = trim($m[1]); // everything before the last two numbers
            $rawScore   = (int)   $m[2];
            $percentage = (float) $m[3];
            $remark     = $percentage >= 75.0 ? 'pass' : 'fail';

            // Now try to split $before into NAME + CODE
            // Student code is an 8-digit number at the end of $before
            $studentName = '';
            $studentCode = '';
            $flagged     = false;

            if (preg_match('/^(.*?)\s+(\d{7,10})\s*$/', $before, $nc)) {
                $studentName = trim($nc[1]);
                $studentCode = trim($nc[2]);
            } else {
                // No recognisable code — $before may be empty (row 1 case) or name only
                $studentName = $before;
                $studentCode = '';
            }

            // Flag if either field is missing
            if (empty($studentName) || empty($studentCode)) {
                $flagged = true;
            }

            $rows[] = [
                'row_number'   => $rowNumber,
                'student_name' => $studentName,
                'student_code' => $studentCode,
                'raw_score'    => $rawScore,
                'percentage'   => $percentage,
                'remark'       => $remark,
                'flagged'      => $flagged,
            ];
        }

        return $rows;
    }
}