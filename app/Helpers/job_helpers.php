<?php

use App\Models\CustomJob;
use Illuminate\Support\Facades\Log;

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob(string $class, string $method = "execute", array $params = [], int $priority = 0, int $delay = 0): bool
    {
        // Validate the class and method
        $approvedClasses = config('jobs.approved_classes');
        $approvedMethods = config('jobs.approved_methods');

        if (!in_array($class, $approvedClasses)) {
            Log::warning("Unauthorized job class: $class attempted.");
            session()->flash('error', "The job class '$class' is not authorized.");
            return false; // Indicate failure
        }

        if (!in_array($method, $approvedMethods)) {
            Log::warning("Unauthorized job method: $method attempted in class $class.");
            session()->flash('error', "The method '$method' is not authorized for the job class '$class'.");
            return false; // Indicate failure
        }

        $availableAt = $delay ? now()->addSeconds($delay) : now();

        // Queue the job
        CustomJob::create([
            'job_class' => $class,
            'method' => $method,
            'params' => $params,
            'priority' => $priority,
            'available_at' => $availableAt,
        ]);

        Log::info("Job queued: $class::$method with priority $priority and delay $delay seconds.");
        session()->flash('success', "The job '$class::$method' has been queued successfully.");
        return true; // Indicate success
    }
}

