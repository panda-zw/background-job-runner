<?php

namespace App\CustomJobs;

use Illuminate\Support\Facades\Log;

class ExampleFailingJob
{
    public $title = 'Example Failing Job';

    public function execute(): void
    {
        throw new \Exception("Simulated job failure");
    }
}
