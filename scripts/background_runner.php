<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CustomJob;
use Illuminate\Support\Facades\Log;

while (true) { // Continuous loop
    // Fetch the next job
    $job = CustomJob::where('status', 'pending')
              ->where(function ($query) {
                  $query->whereNull('available_at')
                        ->orWhere('available_at', '<=', now());
              })
              ->orderBy('priority', 'desc')
              ->orderBy('available_at', 'asc')
              ->first();

    if (!$job) {
        // Sleep for a few seconds if no jobs are found to avoid high CPU usage
        sleep(5);
        continue;
    }

    // Validate the class and method against the whitelist
    $approvedClasses = config('jobs.approved_classes');
    $approvedMethods = config('jobs.approved_methods');

    if (!in_array($job->job_class, $approvedClasses)) {
        $job->update(['status' => 'failed']);
        file_put_contents(
            __DIR__ . '/../storage/logs/background_jobs_errors.log',
            "[{$job->job_class}::{$job->method}] Unauthorized class at " . now() . "\n",
            FILE_APPEND
        );
        continue;
    }

    if (!in_array($job->method, $approvedMethods)) {
        $job->update(['status' => 'failed']);
        file_put_contents(
            __DIR__ . '/../storage/logs/background_jobs_errors.log',
            "[{$job->job_class}::{$job->method}] Unauthorized method at " . now() . "\n",
            FILE_APPEND
        );
        Log::info("Unauthorized method: {$job->method}");
        continue;
    }

    // Execute the job
    $job->update(['status' => 'running']);

    try {
        $instance = app($job->job_class);

        if (!method_exists($instance, $job->method)) {
            throw new Exception("Method {$job->method} does not exist in class {$job->job_class}");
        }

        $instance->{$job->method}(...$job->params);

        // Mark as completed
        $job->update(['status' => 'completed']);
        file_put_contents(
            __DIR__ . '/../storage/logs/background_jobs.log',
            "[{$job->job_class}::{$job->method}] Completed at " . now() . "\n",
            FILE_APPEND
        );
        Log::info("Job completed: {$job->job_class}::{$job->method}");

    } catch (Exception $e) {
        $job->increment('retry_count');

        if ($job->retry_count >= config('jobs.max_retries', 3)) {
            $job->update(['status' => 'failed']);
            file_put_contents(
                __DIR__ . '/../storage/logs/background_jobs.log',
                "[{$job->job_class}::{$job->method}] Failed after {$job->retry_count} attempts at " . now() . "\n",
                FILE_APPEND
            );
            Log::error("Job failed: {$job->job_class}::{$job->method}");
        } else {
            $job->update(['status' => 'pending']);
        }

        file_put_contents(
            __DIR__ . '/../storage/logs/background_jobs_errors.log',
            "[{$job->job_class}::{$job->method}] Error: {$e->getMessage()} at " . now() . "\n",
            FILE_APPEND
        );
        Log::error("Job failed: {$job->job_class}::{$job->method} - {$e->getMessage()}");
    }
}
