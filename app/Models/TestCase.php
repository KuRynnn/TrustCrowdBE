<?php

namespace App\Models;

class TestCase extends BaseModel
{
    protected $primaryKey = 'test_id';
    protected $fillable = [
        'app_id',
        'qa_id',
        'test_title',
        'given_context',
        'when_action',
        'then_result',
        'priority'
    ];

    protected $casts = [
        'priority' => 'string'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function qaSpecialist()
    {
        return $this->belongsTo(QASpecialist::class, 'qa_id');
    }

    public function uatTasks()
    {
        return $this->hasMany(UATTask::class, 'test_id');
    }
}