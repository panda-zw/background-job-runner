<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_class',
        'method',
        'params',
        'status',
        'priority',
        'retry_count',
        'available_at',
    ];

    protected $casts = [
        'params' => 'array',
        'available_at' => 'datetime',
    ];

    // Define a method to get the name
    public function getNameAttribute()
    {
        // Check if the 'title' property exists and is not null
        if (property_exists($this->job_class, 'title') && !is_null((app($this->job_class))->title)) {
            return (app($this->job_class))->title;
        }

        // Fallback: Extract the name from the class name
        $className = str_replace('App\\Jobs\\', '', $this->job_class);
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $className); // Add spaces to camel case
    }
}

