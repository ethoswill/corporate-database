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
        'notes',
        'event_date',
        'start_time',
        'end_time',
        'attachments',
    ];

    protected $casts = [
        'event_date' => 'date',
        'attachments' => 'array',
    ];
}
