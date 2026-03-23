<?php

// app/Services/ItemMatrixParser.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ItemMatrixParser
{
    const DISCRIMINATION_COLS = [
        '<.00',
        '.00-.14',
        '.15-.24',
        '.25-.29',
        '.30-.44',
        '.45 and above',
    ];

    const DIFFICULTY_BANDS = [
        '81-100%' => 'Very Easy',
        '61-80%'  => 'Easy (Good Items)',
        '41-60%'  => 'Average (Best Items)',
        '21-40%'  => 'Difficult (Good Items)',
        '0-20%'   => 'Very Difficult',
    ];

    public function parse(string $pdfPath): array
    {
        $python = $this->findPython();

        if (!$python) {
            Log::error('ItemMatrixParser: python3 not found');
            return $this->emptyResult();
        }

        $script = base_path('scripts/parse_item_matrix.py');

        if (!file_exists($script)) {
            Log::error("ItemMatrixParser: script not found at {$script}");
            return $this->emptyResult();
        }

        $process = proc_open(
            [$python, $script, $pdfPath],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            Log::error('ItemMatrixParser: proc_open failed');
            return $this->emptyResult();
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit !== 0 || empty(trim($stdout))) {
            Log::error("ItemMatrixParser: Python exited {$exit}. stderr: {$stderr}");
            return $this->emptyResult();
        }

        $raw = json_decode($stdout, true);

        if (!$raw || isset($raw['error'])) {
            Log::error('ItemMatrixParser: ' . ($raw['error'] ?? 'bad JSON: ' . $stdout));
            return $this->emptyResult();
        }

        return $this->buildResult($raw);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function findPython(): ?string
    {
        // ── Always check .env first ───────────────────────────────────────────
        $envPath = env('PYTHON_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            // Try where — may fail under Laragon's service PATH
            foreach (['python', 'python3'] as $bin) {
                $out = shell_exec('where ' . escapeshellarg($bin) . ' 2>NUL');
                if (!empty(trim($out ?? ''))) {
                    return trim(explode("\n", trim($out))[0]);
                }
            }

            // Common Windows install paths
            $username = get_current_user();
            $winPaths = [
                'C:\\Python312\\python.exe',
                'C:\\Python311\\python.exe',
                'C:\\Python310\\python.exe',
                'C:\\Python39\\python.exe',
                "C:\\Users\\{$username}\\AppData\\Local\\Programs\\Python\\Python312\\python.exe",
                "C:\\Users\\{$username}\\AppData\\Local\\Programs\\Python\\Python311\\python.exe",
                "C:\\Users\\{$username}\\AppData\\Local\\Programs\\Python\\Python310\\python.exe",
                "C:\\Users\\{$username}\\AppData\\Local\\Programs\\Python\\Python313\\python.exe",
                // Laragon bundled Python
                'C:\\laragon\\bin\\python\\python-3.12\\python.exe',
                'C:\\laragon\\bin\\python\\python-3.11\\python.exe',
                'C:\\laragon\\bin\\python\\python-3.10\\python.exe',
                // Windows Store Python
                "C:\\Users\\{$username}\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe",
                "C:\\Users\\{$username}\\AppData\\Local\\Microsoft\\WindowsApps\\python3.exe",
            ];

            foreach ($winPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }

            return null;
        }

        // Unix / Mac
        foreach (['python3', '/usr/bin/python3', '/usr/local/bin/python3', '/opt/homebrew/bin/python3'] as $bin) {
            $out = shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null');
            if (!empty(trim($out ?? ''))) {
                return trim($out);
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildResult(array $raw): array
    {
        $cells = $raw['cells'] ?? [];

        $reject = $needsRevision = $acceptable = [];
        foreach ($cells as $band => $cols) {
            foreach ($cols as $disc => $items) {
                if (in_array($disc, ['<.00', '.00-.14'])) {
                    $reject = array_merge($reject, $items);
                } elseif (in_array($disc, ['.15-.24', '.25-.29'])) {
                    $needsRevision = array_merge($needsRevision, $items);
                } else {
                    $acceptable = array_merge($acceptable, $items);
                }
            }
        }
        sort($reject);
        sort($needsRevision);
        sort($acceptable);

        $normCells = [];
        foreach (self::DIFFICULTY_BANDS as $band => $label) {
            $normCells[$band] = [];
            foreach (self::DISCRIMINATION_COLS as $col) {
                $normCells[$band][$col] = $cells[$band][$col] ?? [];
            }
        }

        $normRowTotals = [];
        foreach (array_keys(self::DIFFICULTY_BANDS) as $band) {
            $normRowTotals[$band] = $raw['row_totals'][$band] ?? 0;
        }

        $normColTotals = [];
        foreach (self::DISCRIMINATION_COLS as $col) {
            $normColTotals[$col] = $raw['col_totals'][$col] ?? 0;
        }

        return [
            'title'       => $raw['title']      ?? '',
            'module'      => $raw['module']      ?? '',
            'date'        => $raw['date']        ?? '',
            'total_items' => $raw['total_items'] ?? 0,
            'col_totals'  => $normColTotals,
            'row_totals'  => $normRowTotals,
            'cells'       => $normCells,
            'legend'      => [
                'reject'         => $reject,
                'needs_revision' => $needsRevision,
                'acceptable'     => $acceptable,
            ],
        ];
    }

    private function emptyResult(): array
    {
        $emptyCells = [];
        foreach (self::DIFFICULTY_BANDS as $band => $label) {
            foreach (self::DISCRIMINATION_COLS as $col) {
                $emptyCells[$band][$col] = [];
            }
        }

        return [
            'title'       => '',
            'module'      => '',
            'date'        => '',
            'total_items' => 0,
            'col_totals'  => array_fill_keys(self::DISCRIMINATION_COLS, 0),
            'row_totals'  => array_fill_keys(array_keys(self::DIFFICULTY_BANDS), 0),
            'cells'       => $emptyCells,
            'legend'      => ['reject' => [], 'needs_revision' => [], 'acceptable' => []],
        ];
    }
}