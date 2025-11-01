<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'description',
        'status',
        'priority',
        'last_message_at',
        'is_unread',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_unread' => 'boolean',
    ];

    /**
     * Get the user that created the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location associated with the ticket.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the messages for the ticket.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message for the ticket.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Scope to get only unread tickets.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope to get only awaiting reply tickets.
     */
    public function scopeAwaitingReply($query)
    {
        return $query->where('status', 'awaiting_reply');
    }

    /**
     * Scope to get only archived tickets.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Archive the ticket.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Mark ticket as awaiting reply.
     */
    public function markAsAwaitingReply(): void
    {
        $this->update(['status' => 'awaiting_reply']);
    }

    /**
     * Mark ticket as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['status' => 'unread']);
    }
}
