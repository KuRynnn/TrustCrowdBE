<?php

// app/Models/BugValidation.php
namespace App\Models;

class BugValidation extends BaseModel
{
    protected $primaryKey = 'validation_id';
    protected $fillable = [
        'bug_id',
        'qa_id',
        'validation_status',
        'comments',
        'validated_at'
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'validation_status' => 'string'
    ];

    public function bugReport()
    {
        return $this->belongsTo(BugReport::class, 'bug_id');
    }

    public function qaSpecialist()
    {
        return $this->belongsTo(QASpecialist::class, 'qa_id');
    }
}