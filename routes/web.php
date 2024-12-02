<?php

use App\Http\Controllers\BackgroundRunnerController;
use App\Http\Controllers\CustomJobsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // routes for jobs
    Route::get('/jobs', [CustomJobsController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create', [CustomJobsController::class, 'triggerJob'])->name('jobs.create');
    Route::get('/jobs/fail', [CustomJobsController::class, 'triggerFailingJob'])->name('jobs.fail');
    Route::get('/jobs/unauthorized', [CustomJobsController::class, 'triggerUnauthorizedJob'])->name('jobs.unauthorized');
    Route::post('/jobs/{id}/cancel', [CustomJobsController::class, 'cancel'])->name('jobs.cancel');
    Route::get('/background-runner/status', [BackgroundRunnerController::class, 'status'])->name('background.runner.status');
    Route::post('/jobs/run', [CustomJobsController::class, 'runJob'])->name('jobs.run');

});

require __DIR__.'/auth.php';
