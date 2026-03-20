<?php

// ─────────────────────────────────────────────────────────────────────────────
// TEMPORARY DIAGNOSTIC ROUTE — add to routes/web.php, visit /matrix-debug
// Remove this after the issue is fixed.
// ─────────────────────────────────────────────────────────────────────────────
//
// In routes/web.php add:
//   require __DIR__ . '/../diagnostic_matrix.php';
//
// Or paste the Route::get(...) block directly into routes/web.php

use Illuminate\Support\Facades\Route;

Route::get('/matrix-debug', function () {
    $out = [];

    // 1. PHP disabled functions
    $disabled = ini_get('disable_functions');
    $out['disabled_functions'] = $disabled ?: '(none)';
    $out['proc_open_available'] = function_exists('proc_open') ? 'YES' : 'NO';
    $out['shell_exec_available'] = function_exists('shell_exec') ? 'YES' : 'NO';

    // 2. Find python3
    $pythonPaths = [
        'python3',
        '/usr/bin/python3',
        '/usr/local/bin/python3',
        '/opt/homebrew/bin/python3',
    ];
    $foundPython = null;
    foreach ($pythonPaths as $p) {
        $result = shell_exec('command -v ' . escapeshellarg($p) . ' 2>/dev/null');
        if (!empty(trim($result ?? ''))) {
            $foundPython = trim($result);
            break;
        }
    }
    $out['python3_path'] = $foundPython ?? 'NOT FOUND';

    // 3. Python version
    if ($foundPython) {
        $ver = shell_exec($foundPython . ' --version 2>&1');
        $out['python3_version'] = trim($ver ?? '');
    }

    // 4. pdfplumber installed?
    if ($foundPython) {
        $check = shell_exec($foundPython . ' -c "import pdfplumber; print(pdfplumber.__version__)" 2>&1');
        $out['pdfplumber_version'] = trim($check ?? '');
    }

    // 5. Script exists?
    $script = base_path('scripts/parse_item_matrix.py');
    $out['script_path']   = $script;
    $out['script_exists'] = file_exists($script) ? 'YES' : 'NO';

    // 6. Try proc_open with a trivial python command
    if ($foundPython && function_exists('proc_open')) {
        $process = proc_open(
            [$foundPython, '-c', 'import sys; print("proc_open works"); sys.exit(0)'],
            [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']],
            $pipes
        );
        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($process);
            $out['proc_open_test'] = "exit={$exit} stdout=" . trim($stdout) . " stderr=" . trim($stderr);
        } else {
            $out['proc_open_test'] = 'proc_open() returned false';
        }
    }

    // 7. If script exists, run it against a test (no PDF, just check it loads)
    if ($foundPython && file_exists($script) && function_exists('proc_open')) {
        $process = proc_open(
            [$foundPython, $script],   // no PDF arg — will print error JSON, not crash
            [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']],
            $pipes
        );
        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($process);
            $out['script_dry_run'] = "exit={$exit} stdout=" . trim($stdout) . " stderr=" . trim(substr($stderr, 0, 300));
        }
    }

    return response('<pre>' . htmlspecialchars(print_r($out, true)) . '</pre>');
});