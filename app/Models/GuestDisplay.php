<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestDisplay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'institution',
        'email',
        'pin',
        'access_token',
        'valid_until',
        'is_active',
        'last_accessed_at',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'is_active' => 'boolean',
            'last_accessed_at' => 'datetime',
        ];
    }

    /**
     * Check if the access has expired based on valid_until date.
     */
    public function isExpired(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        return Carbon::now()->startOfDay()->greaterThan(Carbon::parse($this->valid_until)->endOfDay());
    }

    /**
     * Check whether the guest can currently access the display.
     */
    public function isAccessible(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * User who created this guest access.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper to get full access URL.
     */
    public function getAccessUrl(): string
    {
        return route('display.access', ['token' => $this->access_token]);
    }
}
