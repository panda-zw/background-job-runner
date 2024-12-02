<?php

namespace App\CustomJobs;

use Illuminate\Support\Facades\Log;

class ExampleJob
{
    public $title = 'Example Job';

    public function execute(string $message = "This job will run successfully"): void
    {
        Log::info("Executing Example Job with message: $message");
    }
}
