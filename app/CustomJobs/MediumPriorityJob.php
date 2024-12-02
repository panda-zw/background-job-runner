<?php

namespace App\CustomJobs;

class MediumPriorityJob
{
    public $title = 'Medium Priority Job';

    public function execute()
    {
        // Simulate job processing
        file_put_contents(storage_path('logs/job_execution.log'), "Medium Priority Job executed at " . now() . "\n", FILE_APPEND);
    }
}
