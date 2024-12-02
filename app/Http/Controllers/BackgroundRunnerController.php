<?php

namespace App\Http\Controllers;

class BackgroundRunnerController extends Controller
{
    public function status()
    {
        // Command to check if background_runner.php is running
        $isRunning = false;

        // For Unix-based systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $result = shell_exec('ps aux | grep "background_runner.php" | grep -v "grep"');
            $isRunning = !empty($result);
        }

        // For Windows (alternative command)
        else {
            $result = shell_exec('tasklist /FI "IMAGENAME eq php.exe" | findstr "background_runner.php"');
            $isRunning = !empty($result);
        }

        return response()->json(['isRunning' => $isRunning]);
    }
}
