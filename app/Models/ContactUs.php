<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    // Define the fillable fields
    protected $fillable = ['name', 'email', 'phone','message'];

    // Define the timestamps (optional if your table does not have created_at/updated_at)
    public $timestamps = true;

}
