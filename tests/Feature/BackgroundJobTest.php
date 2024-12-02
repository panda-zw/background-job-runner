<?php

use Illuminate\Support\Facades\Log;
use Mockery;

it('queues a valid job successfully', function () {
    // Arrange
    $class = \App\CustomJobs\ExampleJob::class;
    $priority = 5;
    $delay = 0;

    // Act
    runBackgroundJob($class, 'execute', [], $priority, $delay);

    // Assert
    $job = \App\Models\CustomJob::where('job_class', $class)->first();
    expect($job)->not->toBeNull();
    expect($job->priority)->toBe($priority);
    expect($job->status)->toBe('pending');
});

it('throws an error for unauthorized job class', function () {
    // Arrange
    $class = 'App\\CustomJobs\\ExampleUnauthorizedJob';

    // Expect false
    $this->assertFalse(runBackgroundJob($class, 'execute', []));
});

it('throws an error for unauthorized method', function () {
    // Arrange
    $class = \App\CustomJobs\ExampleJob::class;
    $method = 'unauthorizedMethod';

    // Expect exception
    $this->assertFalse(runBackgroundJob($class, $method, []));
});

it('processes jobs in the correct priority order', function () {
    // Arrange
    runBackgroundJob(\App\CustomJobs\LowPriorityJob::class, 'execute', [], 1, 0);
    runBackgroundJob(\App\CustomJobs\MediumPriorityJob::class, 'execute', [], 5, 0);
    runBackgroundJob(\App\CustomJobs\HighPriorityJob::class, 'execute', [], 10, 0);

    // Act
    $jobs = \App\Models\CustomJob::orderBy('priority', 'desc')->get();

    // Assert
    expect($jobs[0]->job_class)->toBe(\App\CustomJobs\HighPriorityJob::class);
    expect($jobs[1]->job_class)->toBe(\App\CustomJobs\MediumPriorityJob::class);
    expect($jobs[2]->job_class)->toBe(\App\CustomJobs\LowPriorityJob::class);
});


it('processes delayed high-priority jobs after lower-priority immediate jobs', function () {
    // Arrange
    runBackgroundJob(\App\CustomJobs\HighPriorityJob::class, 'execute', [], 10, 10); // Delayed 10 seconds
    runBackgroundJob(\App\CustomJobs\LowPriorityJob::class, 'execute', [], 1, 0);   // Immediate

    // Act
    $immediateJob = \App\Models\CustomJob::where('job_class', \App\CustomJobs\LowPriorityJob::class)->first();
    $delayedJob = \App\Models\CustomJob::where('job_class', \App\CustomJobs\HighPriorityJob::class)->first();

    // Assert
    expect($immediateJob->available_at)->toBeLessThan($delayedJob->available_at);
    expect($immediateJob->priority)->toBeLessThan($delayedJob->priority);
});


it('logs unauthorized job attempts', function () {
    // Arrange
    $class = 'App\\CustomJobs\\ExampleUnauthorizedJob';
    $method = 'execute';

    // Mock the Log facade
    Log::shouldReceive('warning')
        ->once()
        ->with("Unauthorized job class: $class attempted.");

    // Act
    $result = runBackgroundJob($class, $method, []);

    // Assert
    expect($result)->toBeFalse();
});
