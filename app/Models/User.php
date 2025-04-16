<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Define the table name (optional if using default 'users' table)
    protected $table = 'users';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    // Hide sensitive fields in JSON responses
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Cast fields to specific types (optional)
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}