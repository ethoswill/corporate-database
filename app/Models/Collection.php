<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'color',
        'cover_image_path',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Collection::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'parent_id');
    }
}
