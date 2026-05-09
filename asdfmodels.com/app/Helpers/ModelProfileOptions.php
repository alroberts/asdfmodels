<?php

namespace App\Helpers;

use App\Models\Setting;

class ModelProfileOptions
{
    public static function hairColors(): array
    {
        return self::settingList('model_hair_color_options', [
            'Black',
            'Brown',
            'Dark Blonde',
            'Blonde',
            'Auburn',
            'Red',
            'Grey',
            'White',
            'Bald',
            'Other',
        ]);
    }

    public static function eyeColors(): array
    {
        return self::settingList('model_eye_color_options', [
            'Brown',
            'Hazel',
            'Blue',
            'Green',
            'Grey',
            'Amber',
            'Other',
        ]);
    }

    public static function measurementSystems(): array
    {
        return [
            'metric' => 'Metric',
            'us_customary' => 'US / Imperial',
            'mixed_uk' => 'UK (ft/in, st/lb)',
            'mixed_ca' => 'Canada (ft/in, lb)',
            'mixed_metric_default' => 'Metric with imperial familiarity',
        ];
    }

    public static function displayNameFormats(): array
    {
        return [
            'full_name' => 'First Name Last Name',
            'first_name_last_initial' => 'First Name + Last Initial',
            'first_name' => 'First Name Only',
            'initials' => 'Initials',
        ];
    }

    public static function displayNameFormatOptionsForNames(string $firstName, string $lastName, bool $isVerified): array
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) . '.' : '';
        $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) . '.' : '';
        $lastFull = $lastName !== '' ? ' ' . $lastName : '';

        $options = [
            [
                'value' => 'first_name_last_initial',
                'label' => trim($firstName . ' ' . $lastInitial) ?: 'First Last initial',
                'description' => 'Show your first name with just the first letter of your last name.',
            ],
            [
                'value' => 'first_name',
                'label' => $firstName !== '' ? $firstName : 'First name only',
                'description' => 'Show only your first name.',
            ],
            [
                'value' => 'initials',
                'label' => trim($firstInitial . ($lastInitial !== '' ? $lastInitial : '')) ?: 'Initials',
                'description' => 'Show just your initials.',
            ],
        ];

        $fullNameOption = [
            'value' => 'full_name',
            'label' => trim($firstName . $lastFull) ?: 'Full name',
            'description' => 'Show your full name.',
            'locked' => !$isVerified,
        ];

        if ($isVerified) {
            array_unshift($options, $fullNameOption);
            return $options;
        }

        array_unshift($options, $fullNameOption);

        return $options;
    }

    public static function defaultMeasurementSystemForCountry(?string $countryCode): string
    {
        $countryCode = strtoupper((string) $countryCode);

        return self::measurementSystemCountryDefaults()[$countryCode] ?? 'metric';
    }

    public static function measurementSystemChoicesForCountry(?string $countryCode): array
    {
        $default = self::defaultMeasurementSystemForCountry($countryCode);

        return match ($default) {
            'us_customary' => ['us_customary', 'metric'],
            'mixed_uk' => ['mixed_uk', 'metric'],
            'mixed_ca' => ['mixed_ca', 'metric'],
            'mixed_metric_default' => ['mixed_metric_default', 'us_customary'],
            default => ['metric', 'us_customary'],
        };
    }

    public static function measurementSystemCountryDefaults(): array
    {
        return [
            'US' => 'us_customary',
            'GB' => 'mixed_uk',
            'CA' => 'mixed_ca',
            'AU' => 'mixed_metric_default',
            'BS' => 'mixed_metric_default',
            'BZ' => 'mixed_metric_default',
            'IE' => 'mixed_metric_default',
            'LR' => 'mixed_metric_default',
            'MM' => 'mixed_metric_default',
            'NZ' => 'mixed_metric_default',
        ];
    }

    public static function shoeSizeRegions(): array
    {
        return [
            'eu' => 'EU',
            'uk' => 'UK',
            'us_women' => 'US Women',
            'us_men' => 'US Men',
        ];
    }

    public static function shoeSizes(): array
    {
        return [
            'eu' => self::rangeStrings(35, 46),
            'uk' => self::halfStepRange(2, 13),
            'us_women' => self::halfStepRange(4, 12),
            'us_men' => self::halfStepRange(5, 14),
        ];
    }

    public static function dressSizeRegions(): array
    {
        return [
            'eu' => 'EU',
            'uk' => 'UK',
            'us' => 'US',
        ];
    }

    public static function dressSizes(): array
    {
        return [
            'eu' => self::rangeStrings(32, 50, 2),
            'uk' => self::rangeStrings(4, 22, 2),
            'us' => self::rangeStrings(0, 18, 2),
        ];
    }

    public static function isValidHairColor(?string $value): bool
    {
        return $value === null || $value === '' || in_array($value, self::hairColors(), true);
    }

    public static function isValidEyeColor(?string $value): bool
    {
        return $value === null || $value === '' || in_array($value, self::eyeColors(), true);
    }

    public static function isValidShoeSize(?string $region, ?string $value): bool
    {
        if (blank($region) && blank($value)) {
            return true;
        }

        if (blank($region) || blank($value)) {
            return false;
        }

        return in_array($value, self::shoeSizes()[$region] ?? [], true);
    }

    public static function isValidDressSize(?string $region, ?string $value): bool
    {
        if (blank($region) && blank($value)) {
            return true;
        }

        if (blank($region) || blank($value)) {
            return false;
        }

        return in_array($value, self::dressSizes()[$region] ?? [], true);
    }

    private static function settingList(string $key, array $default): array
    {
        $value = Setting::getValue($key);

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $cleaned = array_values(array_filter(array_map(fn ($item) => trim((string) $item), $decoded)));
                if ($cleaned !== []) {
                    return $cleaned;
                }
            }
        }

        return $default;
    }

    private static function halfStepRange(float $start, float $end): array
    {
        $values = [];

        for ($value = $start; $value <= $end + 0.001; $value += 0.5) {
            $values[] = fmod($value, 1.0) === 0.0 ? (string) (int) $value : number_format($value, 1, '.', '');
        }

        return $values;
    }

    private static function rangeStrings(int $start, int $end, int $step = 1): array
    {
        $values = [];

        for ($value = $start; $value <= $end; $value += $step) {
            $values[] = (string) $value;
        }

        return $values;
    }
}
