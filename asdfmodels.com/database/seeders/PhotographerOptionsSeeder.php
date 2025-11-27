<?php

namespace Database\Seeders;

use App\Models\PhotographerSpecialty;
use App\Models\PhotographerService;
use Illuminate\Database\Seeder;

class PhotographerOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Specialties
        $specialties = [
            ['key' => 'fashion', 'label' => 'Fashion', 'display_order' => 1],
            ['key' => 'portrait', 'label' => 'Portrait', 'display_order' => 2],
            ['key' => 'wedding', 'label' => 'Wedding', 'display_order' => 3],
            ['key' => 'commercial', 'label' => 'Commercial', 'display_order' => 4],
            ['key' => 'editorial', 'label' => 'Editorial', 'display_order' => 5],
            ['key' => 'beauty', 'label' => 'Beauty', 'display_order' => 6],
            ['key' => 'boudoir', 'label' => 'Boudoir', 'display_order' => 7],
            ['key' => 'fine-art', 'label' => 'Fine Art', 'display_order' => 8],
            ['key' => 'landscape', 'label' => 'Landscape', 'display_order' => 9],
            ['key' => 'street', 'label' => 'Street', 'display_order' => 10],
            ['key' => 'sports', 'label' => 'Sports', 'display_order' => 11],
            ['key' => 'wildlife', 'label' => 'Wildlife', 'display_order' => 12],
            ['key' => 'architecture', 'label' => 'Architecture', 'display_order' => 13],
            ['key' => 'product', 'label' => 'Product', 'display_order' => 14],
            ['key' => 'food', 'label' => 'Food', 'display_order' => 15],
            ['key' => 'event', 'label' => 'Event', 'display_order' => 16],
            ['key' => 'corporate', 'label' => 'Corporate', 'display_order' => 17],
            ['key' => 'headshot', 'label' => 'Headshot', 'display_order' => 18],
            ['key' => 'maternity', 'label' => 'Maternity', 'display_order' => 19],
            ['key' => 'newborn', 'label' => 'Newborn', 'display_order' => 20],
            ['key' => 'family', 'label' => 'Family', 'display_order' => 21],
            ['key' => 'lifestyle', 'label' => 'Lifestyle', 'display_order' => 22],
            ['key' => 'travel', 'label' => 'Travel', 'display_order' => 23],
            ['key' => 'underwater', 'label' => 'Underwater', 'display_order' => 24],
            ['key' => 'aerial', 'label' => 'Aerial', 'display_order' => 25],
        ];

        foreach ($specialties as $specialty) {
            PhotographerSpecialty::updateOrCreate(
                ['key' => $specialty['key']],
                $specialty
            );
        }

        // Seed Services
        $services = [
            ['key' => 'headshots', 'label' => 'Headshots', 'display_order' => 1],
            ['key' => 'portraits', 'label' => 'Portraits', 'display_order' => 2],
            ['key' => 'wedding-photography', 'label' => 'Wedding Photography', 'display_order' => 3],
            ['key' => 'event-photography', 'label' => 'Event Photography', 'display_order' => 4],
            ['key' => 'commercial-photography', 'label' => 'Commercial Photography', 'display_order' => 5],
            ['key' => 'fashion-photography', 'label' => 'Fashion Photography', 'display_order' => 6],
            ['key' => 'editorial-photography', 'label' => 'Editorial Photography', 'display_order' => 7],
            ['key' => 'product-photography', 'label' => 'Product Photography', 'display_order' => 8],
            ['key' => 'real-estate-photography', 'label' => 'Real Estate Photography', 'display_order' => 9],
            ['key' => 'food-photography', 'label' => 'Food Photography', 'display_order' => 10],
            ['key' => 'boudoir-photography', 'label' => 'Boudoir Photography', 'display_order' => 11],
            ['key' => 'maternity-photography', 'label' => 'Maternity Photography', 'display_order' => 12],
            ['key' => 'newborn-photography', 'label' => 'Newborn Photography', 'display_order' => 13],
            ['key' => 'family-photography', 'label' => 'Family Photography', 'display_order' => 14],
            ['key' => 'corporate-headshots', 'label' => 'Corporate Headshots', 'display_order' => 15],
            ['key' => 'studio-rental', 'label' => 'Studio Rental', 'display_order' => 16],
            ['key' => 'photo-editing', 'label' => 'Photo Editing', 'display_order' => 17],
            ['key' => 'retouching', 'label' => 'Retouching', 'display_order' => 18],
            ['key' => 'video-production', 'label' => 'Video Production', 'display_order' => 19],
            ['key' => 'drone-photography', 'label' => 'Drone Photography', 'display_order' => 20],
        ];

        foreach ($services as $service) {
            PhotographerService::updateOrCreate(
                ['key' => $service['key']],
                $service
            );
        }
    }
}

