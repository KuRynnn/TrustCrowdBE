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
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'string'
    ];

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
}
