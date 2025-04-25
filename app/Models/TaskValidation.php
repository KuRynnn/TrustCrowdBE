<?php

namespace App\Models;

class TaskValidation extends BaseModel
{
    protected $primaryKey = 'validation_id';
    protected $fillable = [
        'task_id',
        'qa_id',
        'validation_status',
        'comments',
        'validated_at'
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'validation_status' => 'string'
    ];

    public function uatTask()
    {
        return $this->belongsTo(UATTask::class, 'task_id');
    }

    public function qaSpecialist()
    {
        return $this->belongsTo(QASpecialist::class, 'qa_id');
    }
}
