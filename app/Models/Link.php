<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Link extends Model
{
    protected $primaryKey = 'id_link';

    protected $fillable = [
        'name',
        'true_link',
        'new_link',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to filter links by search keyword and status.
     */
    public function scopeFilter(Builder $query, ?string $search = null, ?string $status = null): Builder
    {
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('new_link', 'like', "%{$search}%")
                    ->orWhere('true_link', 'like', "%{$search}%");
            });
        }

        if (! empty($status) && $status !== 'all') {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query;
    }

    /**
     * Retrieve all links filtered by search keyword and status, ordered by newest first.
     */
    public static function getFilteredLinks(?string $search = null, ?string $status = null): Collection
    {
        return static::filter($search, $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new shortened link record.
     */
    public static function createShortenedLink(string $trueLink, ?string $newLink = null, bool $isActive = true, ?string $name = null): self
    {
        $newLink = $newLink ?? Str::random(6);
        while (static::where('new_link', $newLink)->exists()) {
            $newLink = Str::random(6);
        }

        return static::create([
            'name' => $name ?? 'Public Link',
            'true_link' => $trueLink,
            'new_link' => $newLink,
            'is_active' => $isActive,
        ]);
    }

    /**
     * Update link details from request payload.
     */
    public function updateLink(array $data): bool
    {
        return $this->update([
            'true_link' => $data['target_url'],
            'new_link' => $data['custom_alias'],
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? false,
        ]);
    }

    /**
     * Get count of active links.
     */
    public static function getActiveLinksCount(): int
    {
        return static::where('is_active', true)->count();
    }
}
