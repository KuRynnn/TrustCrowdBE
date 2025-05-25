<?php

namespace App\Models;

class TestEvidence extends BaseModel
{
    protected $primaryKey = 'evidence_id';

    // Add 'context' to fillable array
    protected $fillable = [
        'bug_id',
        'task_id',
        'step_number',
        'step_description',
        'screenshot_url',
        'notes',
        'context'
    ];

    protected $casts = [
        'step_number' => 'integer',
        'bug_id' => 'string',
        'task_id' => 'string'
    ];

    // A piece of evidence can belong to either a bug report or directly to a task
    public function bugReport()
    {
        return $this->belongsTo(BugReport::class, 'bug_id');
    }

    public function uatTask()
    {
        return $this->belongsTo(UATTask::class, 'task_id');
    }
}