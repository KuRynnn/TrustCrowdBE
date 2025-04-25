<?php

// app/Models/BugReport.php
namespace App\Models;

class BugReport extends BaseModel
{
    protected $primaryKey = 'bug_id';
    protected $fillable = [
        'task_id',
        'worker_id',
        'bug_description',
        'steps_to_reproduce',
        'severity',
        'screenshot_url'
    ];

    protected $casts = [
        'severity' => 'string'
    ];

    public function uatTask()
    {
        return $this->belongsTo(UATTask::class, 'task_id');
    }

    public function crowdworker()
    {
        return $this->belongsTo(Crowdworker::class, 'worker_id');
    }

    public function validation()
    {
        return $this->hasOne(BugValidation::class, 'bug_id');
    }
}