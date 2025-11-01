<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'event_title',
        'event_date',
        'notes',
        'attachment_path',
        'attachment_name',
        'color',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
