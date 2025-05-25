<?php

// app/Models/UATTask.php
namespace App\Models;

class UATTask extends BaseModel
{
    protected $table = 'uat_tasks';
    protected $primaryKey = 'task_id';
    protected $fillable = [
        'app_id',
        'test_id',
        'worker_id',
        'status',
        'revision_count',
        'revision_status',
        'revision_comments',
        'last_revised_at',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_revised_at' => 'datetime',
        'status' => 'string',
        'revision_status' => 'string',
        'revision_count' => 'integer'
    ];

    // Define status constants
    const STATUS_ASSIGNED = 'Assigned';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_REVISION_REQUIRED = 'Revision Required';
    const STATUS_VERIFIED = 'Verified';
    const STATUS_REJECTED = 'Rejected';

    // Define revision status constants
    const REVISION_NONE = 'None';
    const REVISION_REQUESTED = 'Requested';
    const REVISION_IN_PROGRESS = 'In Progress';
    const REVISION_COMPLETED = 'Completed';

    public function application()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function testCase()
    {
        return $this->belongsTo(TestCase::class, 'test_id');
    }

    public function crowdworker()
    {
        return $this->belongsTo(Crowdworker::class, 'worker_id');
    }

    public function bugReports()
    {
        return $this->hasMany(BugReport::class, 'task_id');
    }

    public function taskValidation()
    {
        return $this->hasOne(TaskValidation::class, 'task_id');
    }

    // Get original bug reports (not revisions)
    public function originalBugReports()
    {
        return $this->bugReports()->where('is_revision', false);
    }

    // Get bug reports that are revisions
    public function revisedBugReports()
    {
        return $this->bugReports()->where('is_revision', true);
    }

    // Add this new relationship
    public function evidence()
    {
        return $this->hasMany(TestEvidence::class, 'task_id');
    }
}
