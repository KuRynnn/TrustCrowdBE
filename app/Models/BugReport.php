<?php

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
        'original_bug_id',
        'is_revision',
        'revision_number'
    ];

    protected $casts = [
        'severity' => 'string',
        'is_revision' => 'boolean',
        'revision_number' => 'integer'
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

    // Get original bug report if this is a revision
    public function originalBugReport()
    {
        return $this->belongsTo(BugReport::class, 'original_bug_id');
    }

    // Get revisions of this bug report
    public function revisions()
    {
        return $this->hasMany(BugReport::class, 'original_bug_id');
    }

    // Add this relationship for test evidence (screenshots)
    public function evidence()
    {
        return $this->hasMany(TestEvidence::class, 'bug_id');
    }
}