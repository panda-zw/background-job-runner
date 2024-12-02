# Background Job Runner Documentation

The `runBackgroundJob` function is a custom helper designed to queue and execute PHP classes as background jobs within a Laravel application. It supports features like retry attempts, delays, and job prioritization.

---

## How `runBackgroundJob` Works

### Purpose
The `runBackgroundJob` function queues PHP classes and their methods for execution as background jobs. Jobs are stored in a database table (`jobs`) and processed asynchronously using a background runner script (`background_runner.php`).

### Workflow
1. **Validation**:
   - Validates the provided class and method against a pre-approved whitelist to prevent unauthorized execution.

2. **Job Queuing**:
   - Stores the job details (class, method, parameters, priority, and delay) in the `jobs` database table.

3. **Execution**:
   - The `background_runner.php` script periodically retrieves and executes queued jobs based on priority and delay.

4. **Error Handling**:
   - If a job fails, it retries a configurable number of times (`max_retries`).
   - Failed jobs are logged with detailed error messages.

---

## How to Use `runBackgroundJob`

1. **Function Signature**

    ```php
    runBackgroundJob(string $class, string $method, array $params = [], int $priority = 0, int $delay = 0): bool
    ```

    | Parameter | Type   | Default | Description                                      |
    |-----------|--------|---------|--------------------------------------------------|
    | `$class`  | string | N/A     | Fully qualified class name of the job `(e.g., App\CustomJobs\ExampleJob)`.        |
    | `$method` | string | execute | Method to call on the class `(e.g., execute)`.        |
    | `$params` | array  | []      | Parameters to pass to the method (array, optional).   |
    | `$priority` | int  | 0       | Priority of the job (integer, higher values indicate higher priority; default is `0`).                         |
    | `$delay`  | int    | 0       | Delay in seconds before the job becomes available for execution (default is `0`).|

2. **Prerequisites**
    1. Approved Classes and Methods:

        - Add valid job classes and methods to the config/jobs.php file.

        ```php
        return [
            'approved_classes' => [
                'App\\CustomJobs\\ExampleJob',
                'App\\CustomJobs\\AnotherJob',
            ],
            'approved_methods' => [
                'execute',
            ],
            'max_retries' => 3, // Maximum retry attempts for failed jobs
        ];

    2. **Migration** for `jobs` **Table**: Ensure the jobs table exists in the database to store queued jobs.

        ````php
        Schema::create('custom_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_class');
            $table->string('method');
            $table->json('params')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('priority')->default(0);
            $table->integer('retry_count')->default(0);
            $table->timestamp('available_at')->nullable();
            $table->timestamps();
        });

    3. **Configure Background Runner**: Set up the background_runner.php script to process jobs

        ```bash
        * * * * * php scripts/background_runner.php

    - you can also add it to the composer.json file under `scripts` as follows

        ```json
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" \"php scripts/background_runner.php\" --names=server,queue,logs,vite"
        ]

    - this will allow the script to get executed when `composer run dev` is run


3. **Basic Example**

    - *Queue a Job*
    Queue a simple job with no parameters, default priority, and no delay:

        ```php
        use App\CustomJobs\ExampleJob;

        runBackgroundJob(ExampleJob::class, 'execute');

4. **Advanced Examples**

    - *Job with Parameters*
    Queue a job and pass parameters to the method:

        ```php
        use App\CustomJobs\ExampleJob;

        runBackgroundJob(ExampleJob::class, 'execute', ['message' => 'Hello, World!']);

    - *Delayed Job*
    Queue a job with a delay of 30 seconds before it becomes available for execution:

        ```php
        runBackgroundJob(App\CustomJobs\ExampleJob::class, 'execute', [], 0, 30);
        
    
    - High-Priority Job
    Queue a job with a high priority (e.g., 10) to ensure it executes before lower-priority jobs:

        ```php
        runBackgroundJob(App\CustomJobs\ExampleJob::class, 'execute', [], 10);

    - *Combined Example*
    Queue a job with parameters, a delay of 60 seconds, and a high priority:

        ```php
        runBackgroundJob(App\CustomJobs\ExampleJob::class, 'execute', ['key' => 'value'], 20, 60);
    
5. **Monitoring Job Status**
    Job statuses can be monitored in the database or through a web-based dashboard. Common statuses include:

    - `pending`: The job is queued but not yet processed.
    - `running`: The job is being executed.
    - `completed`: The job executed successfully.
    - `failed`: The job failed after exhausting retries.

6. **Configuring Retry Attempts**
    - The maximum number of retry attempts can be set in `config/jobs.php`:

        ```php
        return [
            'max_retries' => 3, // Default is 3 retries
        ];

7. **Configuring delays and priorities**
    - **Delays**: Specify a delay in seconds when calling runBackgroundJob:

        ```php
        runBackgroundJob(App\CustomJobs\ExampleJob::class, 'execute', [], 0, 30); // 30-second delay
    
    - **Priorities**: Assign a priority (default is 0):

        ```php
        runBackgroundJob(App\CustomJobs\ExampleJob::class, 'execute', [], 10); // High-priority job

    Jobs with higher priorities are processed first, followed by delayed jobs.

8. **Logging**

    All job activities are logged in the following files:

    1. Success Logs:

        - File: storage/logs/background_jobs.log
        - Logs successful completions and retries.

    2. Error Logs:

        - File: storage/logs/background_jobs_errors.log
        - Logs exceptions and failures.
