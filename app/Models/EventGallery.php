<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventGallery extends Model
{
    use HasFactory;

    protected $table = 'event_gallery';

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'images',
    ];

    protected $casts = [
        'images' => 'array', // Automatically cast JSON to array
    ];

    // Relationship with Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
