<?php

namespace App\CustomJobs;

use Illuminate\Support\Facades\Log;

class ExampleUnauthorizedJob
{
    public $title = 'Example Unauthorized Job';

    public function execute(string $message): void
    {
        Log::info("Executing Example Unauthorized Job with message: $message");
    }
}
