<?php

namespace App\CustomJobs;

class LowPriorityJob
{
    public $title = 'Low Priority Job';

    public function execute()
    {
        // Simulate job processing
        file_put_contents(storage_path('logs/job_execution.log'), "Low Priority Job executed at " . now() . "\n", FILE_APPEND);
    }
}
