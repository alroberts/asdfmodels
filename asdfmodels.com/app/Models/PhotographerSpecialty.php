<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotographerSpecialty extends Model
{
    protected $fillable = [
        'key',
        'label',
        'display_order',
        'is_active',
        'applies_to_photographers',
        'applies_to_models',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'applies_to_photographers' => 'boolean',
        'applies_to_models' => 'boolean',
    ];

    /**
     * Get all active specialties ordered by display order.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Get all active specialties for a specific profile role.
     */
    public static function getActiveForRole(?string $role = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::where('is_active', true);

        if ($role === 'photographer') {
            $query->where('applies_to_photographers', true);
        } elseif ($role === 'model') {
            $query->where('applies_to_models', true);
        }

        return $query->orderBy('display_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Get specialties as key-value array for forms.
     */
    public static function getOptions(?string $role = null): array
    {
        return static::getActiveForRole($role)
            ->pluck('label', 'key')
            ->toArray();
    }
}
