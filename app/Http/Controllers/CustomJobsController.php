<?php

namespace App\Http\Controllers;

use App\Models\CustomJob;
use Illuminate\Support\Facades\File;

class CustomJobsController extends Controller
{
    public function index()
    {
        $jobs = \App\Models\CustomJob::orderBy('created_at', 'desc')->orderBy('priority', 'desc')->paginate(10);

        // Dynamically retrieve all classes in the App\Jobs namespace
        $jobClasses = [];
        $jobFiles = File::allFiles(app_path('CustomJobs'));

        foreach ($jobFiles as $file) {
            $class = 'App\\CustomJobs\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (class_exists($class)) {
                $instance = app($class);

                // Check if the class has a title property
                $title = property_exists($instance, 'title') && !is_null($instance->title)
                    ? $instance->title
                    : preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace('App\\Jobs\\', '', $class)); // Fallback to class name

                $jobClasses[] = [
                    'class' => $class,
                    'title' => $title . ' (' . $class . ')',
                ];
            }
        }

        return view('jobs.index', compact('jobs', 'jobClasses'));
    }


    public function cancel($id)
    {
        $job = CustomJob::findOrFail($id);

        if ($job->status === 'processing') {
            return redirect()->back()->withErrors('Cannot cancel a running job.');
        }

        $job->delete();
        return redirect()->back()->withSuccess('Job canceled successfully.');
    }

    public function triggerJob()
    {
        // Trigger the job
        runBackgroundJob(\App\CustomJobs\ExampleJob::class, 'execute', ['This is a test message from a test job']);

        return redirect()->route('jobs.index')->withSuccess('Job has been triggered!');
    }

    public function triggerFailingJob()
    {
        // Trigger the job
        runBackgroundJob(\App\CustomJobs\ExampleFailingJob::class, 'execute', [], 10, 0);

        return redirect()->route('jobs.index')->withSuccess('Failing job has been triggered!');
    }

    public function triggerUnauthorizedJob()
    {
        // Trigger the job
        runBackgroundJob(\App\CustomJobs\ExampleUnauthorizedJob::class, 'unauthorizedMethod');

        return redirect()->route('jobs.index')->withSuccess('Unauthorized job has been triggered!');
    }

    public function runJob()
    {
        $validated = request()->validate([
            'job_class' => 'required|string',
            'priority' => 'required|integer|min:0',
            'retry_count' => 'required|integer|min:0',
            'delay' => 'required|integer|min:0',
        ]);

        // Run the background job
        $success = runBackgroundJob(
            class: $validated['job_class'],
            priority: $validated['priority'],
            delay: $validated['delay']
        );

        // Redirect based on success or failure
        if (!$success) {
            return redirect()->route('jobs.index'); // Flash message is already set in runBackgroundJob
        }

        return redirect()->route('jobs.index')->withSuccess('Job triggered successfully.');
    }
}
