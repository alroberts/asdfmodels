<?php

namespace App\Helpers;

use App\Models\PhotographerSpecialty;
use App\Models\PhotographerService;

class PhotographerOptions
{
    /**
     * Get predefined photography specialties from database, with fallback to hardcoded values
     */
    public static function specialties(): array
    {
        try {
            $specialties = PhotographerSpecialty::getOptions();
            if (!empty($specialties)) {
                return $specialties;
            }
        } catch (\Exception $e) {
            // Table might not exist yet, fall back to hardcoded
        }

        // Fallback to hardcoded values (focused on modeling/photographer networking)
        return [
            'fashion' => 'Fashion',
            'portrait' => 'Portrait',
            'commercial' => 'Commercial',
            'editorial' => 'Editorial',
            'beauty' => 'Beauty',
            'boudoir' => 'Boudoir',
            'nude' => 'Nude',
            'fine-art' => 'Fine Art',
            'product' => 'Product',
            'event' => 'Event',
            'headshot' => 'Headshot',
            'maternity' => 'Maternity',
            'lifestyle' => 'Lifestyle',
            'underwater' => 'Underwater',
        ];
    }

    /**
     * Get predefined services offered from database, with fallback to hardcoded values
     */
    public static function services(): array
    {
        try {
            $services = PhotographerService::getOptions();
            if (!empty($services)) {
                return $services;
            }
        } catch (\Exception $e) {
            // Table might not exist yet, fall back to hardcoded
        }

        // Fallback to hardcoded values (services offered by photographers)
        return [
            'studio-photo-sessions' => 'Studio Photo Sessions',
            'on-location-photo-sessions' => 'On-Location Photo Sessions',
            'studio-rental' => 'Studio Rental',
            'photo-editing' => 'Photo Editing',
            'retouching' => 'Retouching',
            'video-production' => 'Video Production',
            'consultation' => 'Consultation',
            'workshop' => 'Workshop',
        ];
    }

    /**
     * Get common camera brands
     */
    public static function cameraBrands(): array
    {
        return [
            'Canon',
            'Nikon',
            'Sony',
            'Fujifilm',
            'Panasonic',
            'Olympus',
            'Pentax',
            'Leica',
            'Hasselblad',
            'Phase One',
            'Other',
        ];
    }

    /**
     * Get common lens types
     */
    public static function lensTypes(): array
    {
        return [
            'Prime Lenses',
            'Zoom Lenses',
            'Wide Angle',
            'Telephoto',
            'Macro',
            'Fisheye',
            'Tilt-Shift',
            'Other',
        ];
    }

    /**
     * Get lighting equipment types
     */
    public static function lightingTypes(): array
    {
        return [
            'Studio Strobes',
            'Continuous Lighting',
            'Speedlights',
            'LED Panels',
            'Ring Lights',
            'Softboxes',
            'Umbrellas',
            'Reflectors',
            'Diffusers',
            'Other',
        ];
    }
}

