<?php

namespace App\CustomJobs;

class HighPriorityJob
{
    public $title = 'High Priority Job';

    public function execute()
    {
        // Simulate job processing
        file_put_contents(storage_path('logs/job_execution.log'), "High Priority Job executed at " . now() . "\n", FILE_APPEND);
    }
}
