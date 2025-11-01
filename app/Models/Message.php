<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_read',
        'attachment_path',
        'attachment_name',
        'attachment_type',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the ticket that owns the message.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that created the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
