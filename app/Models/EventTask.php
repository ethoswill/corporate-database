<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTask extends Model
{
    protected $table = 'event_tasks';
    
    protected $fillable = [
        'calendar_event_id',
        'assigned_to_id',
        'task_title',
        'task_description',
        'due_date',
        'completed',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed' => 'boolean',
    ];

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
