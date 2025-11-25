<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        switch ($setting->setting_type) {
            case 'integer':
                return (int) $setting->setting_value;
            case 'boolean':
                return filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN);
            default:
                return $setting->setting_value;
        }
    }

    /**
     * Set a setting value.
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'setting_type' => $type,
                'description' => $description,
            ]
        );
    }
}

