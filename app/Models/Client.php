<?php

// app/Models/Client.php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class Client extends BaseModel implements Authenticatable
{
    use HasApiTokens, AuthenticatableTrait;

    protected $primaryKey = 'client_id';
    protected $fillable = ['name', 'email', 'password', 'company'];
    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'client_id');
    }
}