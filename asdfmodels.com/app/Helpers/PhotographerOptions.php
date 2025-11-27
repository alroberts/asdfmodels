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

        // Fallback to hardcoded values
        return [
            'fashion' => 'Fashion',
            'portrait' => 'Portrait',
            'wedding' => 'Wedding',
            'commercial' => 'Commercial',
            'editorial' => 'Editorial',
            'beauty' => 'Beauty',
            'boudoir' => 'Boudoir',
            'fine-art' => 'Fine Art',
            'landscape' => 'Landscape',
            'street' => 'Street',
            'sports' => 'Sports',
            'wildlife' => 'Wildlife',
            'architecture' => 'Architecture',
            'product' => 'Product',
            'food' => 'Food',
            'event' => 'Event',
            'corporate' => 'Corporate',
            'headshot' => 'Headshot',
            'maternity' => 'Maternity',
            'newborn' => 'Newborn',
            'family' => 'Family',
            'lifestyle' => 'Lifestyle',
            'travel' => 'Travel',
            'underwater' => 'Underwater',
            'aerial' => 'Aerial',
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

        // Fallback to hardcoded values
        return [
            'headshots' => 'Headshots',
            'portraits' => 'Portraits',
            'wedding-photography' => 'Wedding Photography',
            'event-photography' => 'Event Photography',
            'commercial-photography' => 'Commercial Photography',
            'fashion-photography' => 'Fashion Photography',
            'editorial-photography' => 'Editorial Photography',
            'product-photography' => 'Product Photography',
            'real-estate-photography' => 'Real Estate Photography',
            'food-photography' => 'Food Photography',
            'boudoir-photography' => 'Boudoir Photography',
            'maternity-photography' => 'Maternity Photography',
            'newborn-photography' => 'Newborn Photography',
            'family-photography' => 'Family Photography',
            'corporate-headshots' => 'Corporate Headshots',
            'studio-rental' => 'Studio Rental',
            'photo-editing' => 'Photo Editing',
            'retouching' => 'Retouching',
            'video-production' => 'Video Production',
            'drone-photography' => 'Drone Photography',
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

