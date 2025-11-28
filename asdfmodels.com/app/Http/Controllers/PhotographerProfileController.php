<?php

namespace App\Http\Controllers;

use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PhotographerProfileController extends Controller
{
    /**
     * Display a photographer's profile (public view).
     */
    public function show(string $id): View
    {
        $user = User::with('photographerProfile')->findOrFail($id);

        if (!$user->is_photographer) {
            abort(404);
        }

        $profile = $user->photographerProfile;
        
        // If profile doesn't exist or isn't public, show 404
        if (!$profile) {
            abort(404, 'Photographer profile not found.');
        }
        
        if (!$profile->is_public) {
            abort(404, 'This profile is not public.');
        }

        $featuredImages = \App\Models\PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->where('is_featured', true)
            ->where('is_public', true)
            ->limit(12)
            ->get();

        $portfolioImages = \App\Models\PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->where('is_public', true)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('photographers.show', [
            'user' => $user,
            'profile' => $profile,
            'featuredImages' => $featuredImages,
            'portfolioImages' => $portfolioImages,
        ]);
    }

    /**
     * Show the form for editing the authenticated user's photographer profile.
     */
    public function edit(Request $request): View
    {
        $user = Auth::user();
        
        // Ensure user is a photographer
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        $profile = $user->photographerProfile ?? new PhotographerProfile(['user_id' => $user->id]);

        // Use wizard view for new profiles (no existing profile data) or if wizard parameter is set
        $hasExistingProfile = $user->photographerProfile && $user->photographerProfile->exists;
        $useWizard = $request->has('wizard') || !$hasExistingProfile;
        
        return view($useWizard ? 'photographers.edit-wizard' : 'photographers.edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Update the authenticated user's photographer profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        // Parse equipment JSON if it's a string
        $equipmentData = $request->input('equipment');
        if (is_string($equipmentData)) {
            $equipmentData = json_decode($equipmentData, true);
        }
        
        // Validate equipment structure
        if ($equipmentData && is_array($equipmentData)) {
            $equipmentData = [
                'cameras' => $equipmentData['cameras'] ?? [],
                'lenses' => $equipmentData['lenses'] ?? [],
                'lighting' => $equipmentData['lighting'] ?? [],
                'other' => $equipmentData['other'] ?? []
            ];
        } else {
            $equipmentData = ['cameras' => [], 'lenses' => [], 'lighting' => [], 'other' => []];
        }

        // Get specialties - prefer JSON from hidden input, fallback to array
        $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
        $specialtiesJson = $request->input('specialties_json');
        if ($specialtiesJson) {
            $specialties = json_decode($specialtiesJson, true) ?? [];
        } else {
            $specialties = $request->input('specialties', []);
        }
        $specialties = array_intersect($specialties, array_keys($specialtiesOptions));

        // Get services - prefer JSON from hidden input, fallback to array
        $servicesOptions = \App\Helpers\PhotographerOptions::services();
        $servicesJson = $request->input('services_json');
        if ($servicesJson) {
            $services = json_decode($servicesJson, true) ?? [];
        } else {
            $services = $request->input('services_offered', []);
        }
        $services = array_intersect($services, array_keys($servicesOptions));

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:2000'],
            'gender' => ['nullable', 'in:male,female,other'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_country' => ['nullable', 'string', 'max:255'],
            'location_geoname_id' => ['nullable', 'integer', 'exists:geonames_locations,geoname_id'],
            'location_country_code' => ['nullable', 'string', 'size:2'],
            
            // Professional
            'experience_level' => ['nullable', 'string', 'max:50'],
            'experience_start_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'], // Lenient - we filter invalid ones below
            'services_offered' => ['nullable', 'array'],
            'services_offered.*' => ['string', 'max:100'], // Lenient - we filter invalid ones below
            'studio_location' => ['nullable', 'string', 'max:255'],
            'studio_location_city' => ['nullable', 'string', 'max:255'],
            'studio_location_country' => ['nullable', 'string', 'max:255'],
            'studio_location_geoname_id' => ['nullable', 'integer', 'exists:geonames_locations,geoname_id'],
            'studio_location_country_code' => ['nullable', 'string', 'size:2'],
            'available_for_travel' => ['boolean'],
            'pricing_info' => ['nullable', 'string', 'max:1000'],
            
            // Contact
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'portfolio_website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            
            // Images
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240'], // 10MB max (handles HEIC conversion)
            'profile_photo_crop_data' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'], // 2MB max for logo
            
            // Settings
            'is_public' => ['boolean'],
            'contains_nudity' => ['boolean'],
        ]);

        // Replace validated specialties and services with filtered arrays
        $validated['specialties'] = $specialties;
        $validated['services_offered'] = $services;
        $validated['equipment'] = $equipmentData;

        // If geoname_id is provided, fetch and populate city/country from GeoNames
        if (isset($validated['location_geoname_id']) && $validated['location_geoname_id']) {
            $location = \App\Models\GeoNameLocation::find($validated['location_geoname_id']);
            if ($location) {
                $validated['location_city'] = $location->name;
                $countries = config('countries', []);
                $validated['location_country'] = $countries[$location->country_code] ?? $location->country_code;
            }
        }

        // Handle studio location - build from city and country if provided
        if (isset($validated['studio_location_geoname_id']) && $validated['studio_location_geoname_id']) {
            $studioLocation = \App\Models\GeoNameLocation::find($validated['studio_location_geoname_id']);
            if ($studioLocation) {
                $validated['studio_location_city'] = $studioLocation->name;
                $countries = config('countries', []);
                $validated['studio_location_country'] = $countries[$studioLocation->country_code] ?? $studioLocation->country_code;
            }
        }
        
        // Build studio_location string from city and country
        if (isset($validated['studio_location_city']) && isset($validated['studio_location_country'])) {
            $validated['studio_location'] = $validated['studio_location_city'] . ', ' . $validated['studio_location_country'];
        } elseif (isset($validated['studio_location_city']) || isset($validated['studio_location_country'])) {
            $validated['studio_location'] = trim(($validated['studio_location_city'] ?? '') . ', ' . ($validated['studio_location_country'] ?? ''));
        }

        // Check if this is a new profile (wizard completion)
        $isNewProfile = !$user->photographerProfile || !$user->photographerProfile->exists;
        $isWizardCompletion = $request->has('wizard_completion') && $isNewProfile;

        $profile = $user->photographerProfile ?? new PhotographerProfile();
        $profile->user_id = $user->id;
        $profile->fill($validated);
        
        // Handle profile photo upload with cropping
        if ($request->hasFile('profile_photo')) {
            $cropData = $request->input('profile_photo_crop_data');
            
            // Delete old profile photo if exists
            if ($profile->profile_photo_path) {
                \App\Services\ImageProcessingService::deleteImage($profile->profile_photo_path);
            }
            
            try {
                $profilePhotoPath = \App\Services\ImageProcessingService::processProfilePhoto(
                    $request->file('profile_photo'),
                    $cropData,
                    $user->id
                );
                $profile->profile_photo_path = $profilePhotoPath;
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['profile_photo' => 'Failed to process profile photo: ' . $e->getMessage()])
                    ->withInput();
            }
        }
        
        // Handle logo upload (only if professional_name is set)
        if ($request->hasFile('logo') && $profile->professional_name) {
            // Delete old logo if exists
            if ($profile->logo_path) {
                \App\Services\ImageProcessingService::deleteImage($profile->logo_path);
            }
            
            try {
                $logoPath = \App\Services\ImageProcessingService::processLogo(
                    $request->file('logo'),
                    $user->id
                );
                $profile->logo_path = $logoPath;
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['logo' => 'Failed to process logo: ' . $e->getMessage()])
                    ->withInput();
            }
        }
        
        $profile->save();

        if ($isWizardCompletion) {
            return redirect()->route('photographers.profile.photos')
                ->with('status', 'Profile created successfully! Now add your photos.');
        }

        return redirect()->route('photographers.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Show the photo/logo upload page after wizard completion.
     */
    public function photos(): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can access this page.');
        }

        $profile = $user->photographerProfile;
        
        if (!$profile) {
            return redirect()->route('photographers.profile.edit', ['wizard' => true])
                ->with('error', 'Please complete your profile first.');
        }

        return view('photographers.photos', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Handle photo and logo uploads.
     */
    public function uploadPhotos(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can upload photos.');
        }

        $profile = $user->photographerProfile;
        
        if (!$profile) {
            return redirect()->route('photographers.profile.edit', ['wizard' => true])
                ->with('error', 'Please complete your profile first.');
        }

        $validated = $request->validate([
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'], // 5MB max
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'], // 2MB max for logo
        ]);

        $userFolder = public_path("uploads/photographers/{$user->id}");

        // Create directories if they don't exist
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0755, true);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = 'profile_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = "uploads/photographers/{$user->id}/{$filename}";
            
            // Delete old profile photo if exists
            if ($profile->profile_photo_path && file_exists(public_path($profile->profile_photo_path))) {
                unlink(public_path($profile->profile_photo_path));
            }
            
            $file->move($userFolder, $filename);
            $profile->profile_photo_path = $path;
        }

        // Handle logo upload (only if professional_name is set)
        if ($request->hasFile('logo') && $profile->professional_name) {
            $file = $request->file('logo');
            $filename = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = "uploads/photographers/{$user->id}/{$filename}";
            
            // Delete old logo if exists
            if ($profile->logo_path && file_exists(public_path($profile->logo_path))) {
                unlink(public_path($profile->logo_path));
            }
            
            $file->move($userFolder, $filename);
            $profile->logo_path = $path;
        }

        $profile->save();

        return redirect()->route('photographers.portfolio.create')
            ->with('status', 'Photos uploaded successfully! Now create your portfolio.');
    }
}
