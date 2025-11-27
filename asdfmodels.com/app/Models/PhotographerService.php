<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotographerService extends Model
{
    protected $fillable = [
        'key',
        'label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get all active services ordered by display order.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Get services as key-value array for forms.
     */
    public static function getOptions(): array
    {
        return static::getActive()
            ->pluck('label', 'key')
            ->toArray();
    }
}

