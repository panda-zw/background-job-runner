<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_class'); // Fully qualified class name
            $table->string('method'); // Method to execute
            $table->json('params')->nullable(); // JSON-encoded parameters
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->integer('priority')->default(0); // Higher priority runs first
            $table->integer('retry_count')->default(0);
            $table->timestamp('available_at')->nullable(); // Delayed execution
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_jobs');
    }
};
