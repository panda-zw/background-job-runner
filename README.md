# Background Job Runner for Laravel

This repository provides a custom background job runner system for Laravel applications. It allows PHP classes to execute as background jobs, independent of Laravel's built-in queue system. The solution is scalable, handles job priority, and supports error handling and retry mechanisms.

---

## Features

- Execute PHP classes as background jobs.
- Supports job priority (higher priority jobs are executed first).
- Configurable retry attempts and delays for jobs.
- Error handling with detailed logging.
- Secure execution of pre-approved classes and methods.
- Live Background Job Runner Status Indicator (green/red dot).

---

## Requirements

- PHP 8.0 or higher
- Laravel 9.x or higher
- Composer
- SQLite, MySQL, or PostgreSQL (configurable in `.env`)

---

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/panda-zw/background-job-runner.git
   cd your-repo

2. **Install Dependencies**
    ```bash
    composer install

3. Setup **Environment Copy** the .env.example file and configure your database and application details:
    ```bash
    cp .env.example .env
    php artisan key:generate

4. **Run Migrations** Run the migrations to set up the database tables and also seed the default user:
    ```bash
    php artisan migrate --seed

5. **Approve Job Classes** Add your approved job classes and methods to the `config/jobs.php` configuration file:
    ```php
    return [
        'approved_classes' => [
            \App\Jobs\ExampleJob::class,
            \App\Jobs\HighPriorityJob::class,
            # add others
        ],
        'approved_methods' => [
            'execute',
        ],
        'max_retries' => 3,
    ];

---

## Usage

#### Queue a job

- You can queue a job using the following function

    ```php
    runBackgroundJob(
        class: \App\Jobs\ExampleJob::class,
        method: 'execute',
        params: ['param1' => 'value1', 'param2' => 'value2'],
        priority: 5,
        delay: 0
    );

##### Run the Background Job Script

- To run the script (indepedently) use the following command

    ```bash
    php scripts/background_runner.php 

- This script runs continuously, fetching and executing pending jobs.

##### Run with composer (Recommended)

- To run the application use the following command

    ```bash
    composer run dev

---

## Dashboard

A real-time dashboard is available to monitor and manage jobs:

1. **Login/Register**: Access the dashboard at [http://localhost:8000/login](http://localhost:8000/login) to login with an existing user or register a new account.
    ```test
    Email Address: text@example.com
    Password: password

2. **View Jobs**: Access the dashboard at [http://localhost:8000/jobs](http://localhost:8000/jobs) to view queued, running, and completed jobs.

3. **Trigger a Job**: Use the "Trigger Job" form to manually add a new job to the queue.

4. **Cancel Jobs**: Cancel pending jobs directly from the dashboard.

5. **Background Job Runner Status Indicator**:

    - A green dot indicates that the background runner is active and running.
    - A red dot indicates that the background runner is inactive.
    The status is updated every 5 seconds using a simple polling mechanism.

---

## Development Workflow

### Start Development Server and Background Runner

- Use the following command to start both the Laravel development server and the background job runner:

    ```bash
    composer run dev

This will
* Start the Laravel server at http://127.0.0.1:8000
* Run the background job runner in parallel.

### Configuration

##### Retry Mechanism
- Configure the maximum number of retries in `config/jobs.php`:

    ```php
    'max_retries' => 3,

##### Logs
Successful jobs are logged in `storage/logs/background_jobs`.log.
Errors are logged in `storage/logs/background_jobs_errors.log`.

### Testing
#### Run Tests
- This repository includes PEST tests to ensure the background job runner operates correctly:

    ```bash
    php artisan test

#### Example Tests
**Queues Jobs Successfully**: Ensures valid jobs are added to the queue.
**Priority Execution**: Verifies jobs are executed in the correct priority order.
**Unauthorized Job Handling**: Tests that unauthorized classes or methods are rejected.
    


## License

This project is licensed under the [MIT License](LICENSE).

