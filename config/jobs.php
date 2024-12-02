<?php

return [
    'max_retries' => 3, // Maximum retry attempts before marking a job as failed
    'approved_classes' => [
        'App\\CustomJobs\\ExampleJob',
        'App\\CustomJobs\\ExampleFailingJob',
        'App\\CustomJobs\\HighPriorityJob',
        'App\\CustomJobs\\LowPriorityJob',
        'App\\CustomJobs\\MediumPriorityJob',
    ],
    'approved_methods' => [
        'execute',
    ],
];
