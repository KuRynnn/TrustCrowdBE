<?php

// app/Models/Crowdworker.php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Crowdworker extends BaseModel
{
    use HasApiTokens;

    protected $primaryKey = 'worker_id';
    protected $fillable = ['name', 'email', 'password', 'skills'];
    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function uatTasks()
    {
        return $this->hasMany(UATTask::class, 'worker_id');
    }

    public function bugReports()
    {
        return $this->hasMany(BugReport::class, 'worker_id');
    }
}
