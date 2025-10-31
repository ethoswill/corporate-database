<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'logo_file_url',
        'owner_name',
        'studio_anniversary',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'notes',
        'lockup_file_1',
        'lockup_file_2',
        'lockup_file_3',
        'lockup_file_4',
        'lockup_file_5',
    ];

    protected $casts = [
        'studio_anniversary' => 'date',
    ];

    /**
     * Get the user that owns the location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
