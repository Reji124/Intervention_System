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

            if (! preg_match('/^(\d+)\.\s+(.*)$/', $line, $outer)) {
                continue;
            }

            $rowNumber = (int) $outer[1];
            $rest      = trim($outer[2]);

            // Grab only the first two standalone numbers we care about: [T] and Grade.
            // Everything after (PR, Passed/Failed, or nothing) is ignored.
            if (! preg_match(
                '/^(.*?)\s+(\d+)\s+(\d+\.\d+)(?:\s+.*)?$/i',
                $rest,
                $m
            )) {
                continue;
            }

            $before     = trim($m[1]);  // name + optional student code
            $rawScore   = (int)   $m[2]; // plain integer just before the decimal
            $percentage = (float) $m[3]; // always has decimal point e.g. 92.00
            $remark     = $percentage >= 75.0 ? 'pass' : 'fail';

            $studentName = '';
            $studentCode = '';
            $flagged     = false;

            if (preg_match('/^(.*?)\s+(\d{7,10})\s*$/', $before, $nc)) {
                $studentName = trim($nc[1]);
                $studentCode = trim($nc[2]);
            } else {
                $studentName = $before;
                $studentCode = '';
            }

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