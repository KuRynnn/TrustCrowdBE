<?php

// app/Models/QASpecialist.php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class QASpecialist extends BaseModel
{
    use HasApiTokens;
    protected $table = 'qa_specialists';
    protected $primaryKey = 'qa_id';
    protected $fillable = ['name', 'email', 'password', 'expertise'];
    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'qa_id');
    }

    public function bugValidations()
    {
        return $this->hasMany(BugValidation::class, 'qa_id');
    }
}