<?php

// app/Models/Application.php
namespace App\Models;

class Application extends BaseModel
{
    protected $primaryKey = 'app_id';
    protected $fillable = ['client_id', 'app_name', 'app_url', 'platform', 'description', 'status', 'max_testers'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'app_id');
    }

    public function uatTasks()
    {
        return $this->hasMany(UATTask::class, 'app_id');
    }

    /**
     * Get unique crowdworkers count for this application
     */
    public function getUniqueCrowdworkersCountAttribute()
    {
        return $this->uatTasks()->distinct('worker_id')->count('worker_id');
    }
}