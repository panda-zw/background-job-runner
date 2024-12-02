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

### Function Signature
```php
runBackgroundJob(string $class, string $method, array $params = [], int $priority = 0, int $delay = 0): void