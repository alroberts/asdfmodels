<?php

namespace App\Http\Controllers;

use App\Models\PhotographerProfile;
use App\Models\PhotographerPortfolioImage;
use App\Models\Connection;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PhotographerProfileController extends Controller
{
    /**
     * Display a photographer's profile (public view).
     */
    public function show(string $username): View
    {
        $user = $this->resolveProfileUser($username, 'photographerProfile');

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

        $publicGalleries = PortfolioAlbum::query()
            ->where('user_id', $user->id)
            ->where('owner_role', 'photographer')
            ->where('is_public', true)
            ->select('portfolio_albums.*')
            ->selectSub(function ($query) use ($user) {
                $query->from('photographer_portfolio_images')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('photographer_portfolio_images.album_id', 'portfolio_albums.id')
                    ->where('photographer_portfolio_images.photographer_id', $user->id)
                    ->where('photographer_portfolio_images.is_public', true);
            }, 'images_count')
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        $visibleCredits = PortfolioCredit::visibleOnProfile($user, 'photographer')
            ->with(['creditable', 'owner'])
            ->latest()
            ->limit(40)
            ->get();
        $featuredAlbumCredits = $visibleCredits
            ->filter(fn (PortfolioCredit $credit) => $credit->creditable instanceof PortfolioAlbum && $credit->creditable->is_public)
            ->values();
        $featuredImageCredits = $visibleCredits
            ->filter(function (PortfolioCredit $credit) {
                $image = $credit->creditable;

                return ($image instanceof PortfolioImage || $image instanceof PhotographerPortfolioImage)
                    && $image->is_public;
            })
            ->values();
        $pendingCredits = (Auth::check() && Auth::id() === $user->id)
            ? PortfolioCredit::awaitingResponse($user, 'photographer')->with(['creditable', 'owner'])->latest()->get()
            : collect();
        $connections = $this->profileConnections($user);
        $viewerConnection = Auth::check() && Auth::id() !== $user->id
            ? Connection::with(['requester', 'recipient'])->between(Auth::id(), $user->id)->first()
            : null;

        $portfolioMediaGroups = collect();

        if (Auth::check() && Auth::id() === $user->id) {
            $allPortfolioImages = PhotographerPortfolioImage::where('photographer_id', $user->id)
                ->orderBy('display_order')
                ->orderByDesc('uploaded_at')
                ->orderByDesc('created_at')
                ->get();

            $ungroupedImages = $allPortfolioImages->whereNull('album_id')->values();

            if ($ungroupedImages->isNotEmpty()) {
                $portfolioMediaGroups->push([
                    'id' => 'ungrouped',
                    'label' => 'Ungrouped',
                    'count' => $ungroupedImages->count(),
                    'cover' => $ungroupedImages->first()->thumbnail_path,
                    'images' => $ungroupedImages,
                ]);
            }

            PortfolioAlbum::where('user_id', $user->id)
                ->where('owner_role', 'photographer')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get()
                ->each(function (PortfolioAlbum $album) use ($portfolioMediaGroups, $allPortfolioImages) {
                    $images = $allPortfolioImages->where('album_id', $album->id)->values();

                    if ($images->isEmpty()) {
                        return;
                    }

                    $coverImage = $images->firstWhere('id', $album->cover_image_id) ?: $images->first();

                    $portfolioMediaGroups->push([
                        'id' => 'album-' . $album->id,
                        'label' => $album->name,
                        'count' => $images->count(),
                        'cover' => $coverImage?->thumbnail_path,
                        'images' => $images,
                    ]);
                });
        }

        return view('photographers.show', [
            'user' => $user,
            'profile' => $profile,
            'featuredImages' => $featuredImages,
            'portfolioImages' => $portfolioImages,
            'publicGalleries' => $publicGalleries,
            'taggedImages' => $featuredImageCredits,
            'featuredAlbumCredits' => $featuredAlbumCredits,
            'featuredImageCredits' => $featuredImageCredits,
            'pendingCredits' => $pendingCredits,
            'portfolioMediaGroups' => $portfolioMediaGroups,
            'ownerCanManage' => Auth::check() && Auth::id() === $user->id,
            'connections' => $connections,
            'viewerConnection' => $viewerConnection,
        ]);
    }

    private function profileConnections(User $user): \Illuminate\Support\Collection
    {
        return Connection::acceptedFor($user)
            ->with(['requester.modelProfile', 'requester.photographerProfile', 'recipient.modelProfile', 'recipient.photographerProfile'])
            ->latest('responded_at')
            ->get()
            ->map(fn (Connection $connection) => $connection->otherUser($user))
            ->filter()
            ->groupBy(fn (User $connectedUser) => $connectedUser->is_photographer ? 'Photographers' : 'Models');
    }

    private function resolveProfileUser(string $username, string $relation): User
    {
        $user = User::with($relation)
            ->where('username', $username)
            ->first();

        if (!$user) {
            $history = UsernameHistory::where('username', $username)
                ->where('is_active', true)
                ->whereNotNull('redirects_to_username')
                ->first();

            if ($history && $history->redirects_to_username !== $username) {
                redirect()->route('photographers.show', $history->redirects_to_username)->send();
                exit;
            }
        }

        if (!$user) {
            abort(404);
        }

        return $user;
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

        // Load the profile - ensure we get the actual model instance, not a new one
        $profile = $user->photographerProfile;
        if (!$profile) {
            $profile = new PhotographerProfile(['user_id' => $user->id]);
        }

        // Use wizard view if wizard parameter is set, or if no existing profile
        // When wizard=1 is explicitly set, always show wizard (even for existing profiles)
        $hasExistingProfile = $profile->id !== null;
        $useWizard = $request->has('wizard') || !$hasExistingProfile;
        
        return view($useWizard ? 'photographers.edit-wizard' : 'photographers.edit', [
            'user' => $user,
            'profile' => $profile,
            'countries' => config('countries'),
            'specialtiesOptions' => \App\Helpers\PhotographerOptions::specialties('photographer'),
            'servicesOptions' => \App\Helpers\PhotographerOptions::services(),
        ]);
    }

    /**
     * Update the authenticated user's photographer profile.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        // Parse equipment JSON if it's a string. The full editor also sends newline text fields
        // so photographers can maintain equipment without fighting repeatable inputs.
        $equipmentData = $request->input('equipment');
        if (is_string($equipmentData)) {
            $equipmentData = json_decode($equipmentData, true);
        }

        if (!$equipmentData) {
            $splitEquipment = function (?string $value): array {
                return collect(preg_split('/\r\n|\r|\n/', (string) $value))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all();
            };

            $equipmentData = [
                'cameras' => $splitEquipment($request->input('equipment_cameras')),
                'lenses' => $splitEquipment($request->input('equipment_lenses')),
                'lighting' => $splitEquipment($request->input('equipment_lighting')),
                'other' => $splitEquipment($request->input('equipment_other')),
            ];
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

        try {
            $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('users', 'username')->ignore($user->id),
                Rule::unique('username_histories', 'username')->ignore($user->username, 'username'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1200'],
            // Bio validation: if provided, must be 50-1200 chars, but can be empty
            // We'll handle this in the controller logic
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'display_name_format' => ['nullable', 'in:professional_name,first_name_last_initial,first_name,initials,full_name'],
            'show_company_on_profile' => ['nullable', 'boolean'],
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
            
            // Contact
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['nullable', 'in:instagram,facebook,x,tiktok,youtube,behance,linkedin,website'],
            'social_links.*.url' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'portfolio_website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            
            // Images
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240'], // 10MB max (handles HEIC conversion)
            'profile_photo_crop_data' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'], // 2MB max for logo
            
            // Settings
            'is_public' => ['boolean'],
            'contains_nudity' => ['boolean'],
        ]);

        // Validate bio length if provided (must be 50-1200 chars if not empty)
        if (isset($validated['bio']) && $validated['bio'] !== null && trim($validated['bio']) !== '') {
            $bioLength = mb_strlen(trim($validated['bio']));
            if ($bioLength < 50 || $bioLength > 1200) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Bio must be between 50 and 1200 characters if provided.',
                        'errors' => ['bio' => ['Bio must be between 50 and 1200 characters if provided.']]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['bio' => 'Bio must be between 50 and 1200 characters if provided.'])
                    ->withInput();
            }
        }
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
        
        // Replace validated specialties and services with filtered arrays
        $validated['specialties'] = $specialties;
        $validated['services_offered'] = $services;
        $validated['equipment'] = $equipmentData;
        $validated['social_links'] = collect($validated['social_links'] ?? [])
            ->filter(function ($link) {
                return filled($link['platform'] ?? null) && filled($link['url'] ?? null);
            })
            ->map(function ($link) {
                return [
                    'platform' => $link['platform'],
                    'url' => $link['url'],
                ];
            })
            ->values()
            ->all();
        $validated['instagram'] = collect($validated['social_links'])
            ->firstWhere('platform', 'instagram')['url'] ?? null;
        $validated['portfolio_website'] = collect($validated['social_links'])
            ->firstWhere('platform', 'website')['url'] ?? null;
        $validated['facebook'] = collect($validated['social_links'])
            ->firstWhere('platform', 'facebook')['url'] ?? null;
        $validated['twitter'] = collect($validated['social_links'])
            ->firstWhere('platform', 'x')['url'] ?? null;

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
        // Clean up the city and country values first
        $studioCity = isset($validated['studio_location_city']) ? trim($validated['studio_location_city']) : '';
        $studioCountry = isset($validated['studio_location_country']) ? trim($validated['studio_location_country']) : '';
        
        // Remove any extra commas from city name
        $studioCity = preg_replace('/,+/', '', $studioCity);
        $studioCity = trim($studioCity);
        
        if ($studioCity && $studioCountry) {
            $validated['studio_location'] = $studioCity . ', ' . $studioCountry;
        } elseif ($studioCity || $studioCountry) {
            $validated['studio_location'] = trim($studioCity . ', ' . $studioCountry);
        }

        // Sanitize bio: strip HTML but preserve line breaks
        if (isset($validated['bio']) && $validated['bio']) {
            // Convert common HTML line break tags to newlines
            $bio = $validated['bio'];
            $bio = preg_replace('/<br\s*\/?>/i', "\n", $bio);
            $bio = preg_replace('/<\/p>/i', "\n\n", $bio);
            $bio = preg_replace('/<p[^>]*>/i', '', $bio);
            // Strip all remaining HTML tags
            $bio = strip_tags($bio);
            // Normalize whitespace (preserve line breaks, collapse multiple spaces)
            $bio = preg_replace('/[ \t]+/', ' ', $bio);
            // Remove any remaining HTML entities
            $bio = html_entity_decode($bio, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Trim and ensure max length
            $bio = trim($bio);
            if (mb_strlen($bio) > 1200) {
                $bio = mb_substr($bio, 0, 1200);
            }
            $validated['bio'] = $bio;
        }

        $requestedUsername = $validated['username'] ?? null;
        unset($validated['username']);

        // Check if this is a new profile (wizard completion)
        $isNewProfile = !$user->photographerProfile || !$user->photographerProfile->exists;
        $isWizardCompletion = $request->has('wizard_completion') && $isNewProfile;

        // Update user name parts when present. Keep the legacy `name` field redacted via User::setNameAttribute.
        if (array_key_exists('first_name', $validated) || array_key_exists('last_name', $validated)) {
            $firstName = trim((string) ($validated['first_name'] ?? $user->first_name));
            $lastName = trim((string) ($validated['last_name'] ?? $user->last_name));
            $user->name = trim($firstName . ' ' . $lastName);
            $user->save();
            unset($validated['first_name'], $validated['last_name']);
        } elseif (isset($validated['name']) && $validated['name']) {
            $user->name = $validated['name'];
            $user->save();
        }

        if ($user->canEditUsername() && filled($requestedUsername)) {
            $user->changeUsername($requestedUsername);
        } elseif (!filled($user->username)) {
            $user->username = $user->generateUniqueUsername();
        }
        $user->save();
        
        $profile = $user->photographerProfile ?? new PhotographerProfile();
        $profile->user_id = $user->id;
        
        // Handle boolean fields - convert string '0' to false, '1' or true to true
        if (isset($validated['is_public'])) {
            $validated['is_public'] = filter_var($validated['is_public'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }
        if (isset($validated['contains_nudity'])) {
            $validated['contains_nudity'] = filter_var($validated['contains_nudity'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }
        if (isset($validated['available_for_travel'])) {
            $validated['available_for_travel'] = filter_var($validated['available_for_travel'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        $verifiedOnlyDisplayFormats = ['full_name', 'professional_name'];
        if (
            in_array($validated['display_name_format'] ?? '', $verifiedOnlyDisplayFormats, true) &&
            !$profile->isVerified()
        ) {
            $validated['display_name_format'] = 'first_name_last_initial';
        }

        $validated['show_company_on_profile'] = $profile->isVerified()
            ? $request->boolean('show_company_on_profile')
            : false;
        
        // Explicitly handle date_of_birth to ensure it's saved correctly
        // Laravel's date cast will handle the conversion, but we ensure it's set
        if (isset($validated['date_of_birth']) && !empty($validated['date_of_birth'])) {
            $profile->date_of_birth = $validated['date_of_birth'];
            // Remove from validated so fill() doesn't try to set it again
            unset($validated['date_of_birth']);
        }
        
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
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Failed to process profile photo: ' . $e->getMessage(),
                        'errors' => ['profile_photo' => ['Failed to process profile photo: ' . $e->getMessage()]]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['profile_photo' => 'Failed to process profile photo: ' . $e->getMessage()])
                    ->withInput();
            }
        }
        
        // Handle logo upload. Company logos are reserved for verified profiles so the
        // public company presentation cannot be used to bypass identity controls.
        $hasProfessionalName = $profile->professional_name || ($validated['professional_name'] ?? null);
        if ($request->hasFile('logo') && !$profile->isVerified()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Company logo uploads are available to verified photographer profiles.',
                    'errors' => ['logo' => ['Company logo uploads are available to verified photographer profiles.']]
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['logo' => 'Company logo uploads are available to verified photographer profiles.'])
                ->withInput();
        }

        if ($request->hasFile('logo') && $hasProfessionalName) {
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
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Failed to process logo: ' . $e->getMessage(),
                        'errors' => ['logo' => ['Failed to process logo: ' . $e->getMessage()]]
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors(['logo' => 'Failed to process logo: ' . $e->getMessage()])
                    ->withInput();
            }
        } elseif ($request->hasFile('logo') && !$hasProfessionalName) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Please set a professional/company name before uploading a logo.',
                    'errors' => ['logo' => ['Please set a professional/company name before uploading a logo.']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['logo' => 'Please set a professional/company name before uploading a logo.'])
                ->withInput();
        }
        
        $profile->save();

        // Handle AJAX requests - check for X-Requested-With header or Accept: application/json
        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'status' => 'success',
                'profile_photo_path' => $profile->profile_photo_path ? asset($profile->profile_photo_path) : null,
                'logo_path' => $profile->logo_path ? asset($profile->logo_path) : null,
            ]);
        }

        if ($isWizardCompletion) {
            return redirect()->route('photographers.profile.photos')
                ->with('status', 'Profile created successfully! Now add your photos.');
        }

        return redirect()->route('photographers.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    public function updateBio(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:1200'],
        ]);

        $profile = $user->photographerProfile;

        if (!$profile) {
            abort(404, 'Photographer profile not found.');
        }

        $profile->bio = $this->cleanProfileBio($validated['bio'] ?? null);
        $profile->save();

        return response()->json([
            'message' => 'Bio updated.',
            'bio' => $profile->bio,
        ]);
    }

    public function updateProfessionalQuick(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        $profile = $user->photographerProfile;

        if (!$profile) {
            abort(404, 'Photographer profile not found.');
        }

        $specialtyOptions = \App\Helpers\PhotographerOptions::specialties('photographer');
        $serviceOptions = \App\Helpers\PhotographerOptions::services();

        $validated = $request->validate([
            'experience_level' => ['nullable', 'string', 'max:50'],
            'experience_start_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'studio_location' => ['nullable', 'string', 'max:255'],
            'available_for_travel' => ['nullable', 'boolean'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'services_offered' => ['nullable', 'array'],
            'services_offered.*' => ['string', 'max:100'],
        ]);

        $profile->experience_level = $validated['experience_level'] ?? null;
        $profile->experience_start_year = $validated['experience_start_year'] ?? null;
        $profile->studio_location = $validated['studio_location'] ?? null;
        $profile->available_for_travel = $request->boolean('available_for_travel');
        $profile->specialties = array_values(array_intersect($validated['specialties'] ?? [], array_keys($specialtyOptions)));
        $profile->services_offered = array_values(array_intersect($validated['services_offered'] ?? [], array_keys($serviceOptions)));
        $profile->save();

        return response()->json([
            'message' => 'Profile details updated.',
        ]);
    }

    public function updateMedia(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_photographer) {
            abort(403, 'Only photographers can have photographer profiles.');
        }

        $profile = $user->photographerProfile;

        if (!$profile) {
            abort(404, 'Photographer profile not found.');
        }

        $validated = $request->validate([
            'profile_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'cover_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'profile_photo_image_id' => ['nullable', 'integer', 'exists:photographer_portfolio_images,id'],
            'cover_photo_image_id' => ['nullable', 'integer', 'exists:photographer_portfolio_images,id'],
            'profile_photo_crop_data' => ['nullable', 'string'],
            'cover_photo_crop_data' => ['nullable', 'string'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'remove_cover_photo' => ['nullable', 'boolean'],
        ]);

        $profileCrop = $this->parseProfileCropData($validated['profile_photo_crop_data'] ?? null);
        $coverCrop = $this->parseProfileCropData($validated['cover_photo_crop_data'] ?? null);
        $previousProfilePhoto = $profile->profile_photo_path;
        $previousCoverPhoto = $profile->cover_photo_path;
        $mediaWasChanged = false;

        if ($request->boolean('remove_profile_photo')) {
            $profile->profile_photo_path = null;
            $mediaWasChanged = true;
        } elseif ($request->hasFile('profile_photo_upload')) {
            $profile->profile_photo_path = $this->storePhotographerMediaUpload(
                $request->file('profile_photo_upload'),
                $user->id,
                'profile',
                $profileCrop
            );
            $mediaWasChanged = true;
        } elseif (!empty($validated['profile_photo_image_id'])) {
            $portfolioImage = PhotographerPortfolioImage::where('id', $validated['profile_photo_image_id'])
                ->where('photographer_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $profile->profile_photo_path = $this->storePhotographerMediaFromPortfolioImage($portfolioImage, $user->id, 'profile', $profileCrop);
                $mediaWasChanged = true;
            }
        } elseif ($profileCrop && $previousProfilePhoto && File::exists(public_path($previousProfilePhoto))) {
            $profile->profile_photo_path = $this->storeCroppedPhotographerProfileImage(
                public_path($previousProfilePhoto),
                $user->id,
                $profileCrop
            );
            $mediaWasChanged = true;
        }

        if ($request->boolean('remove_cover_photo')) {
            $profile->cover_photo_path = null;
            $mediaWasChanged = true;
        } elseif ($request->hasFile('cover_photo_upload')) {
            $profile->cover_photo_path = $this->storePhotographerMediaUpload(
                $request->file('cover_photo_upload'),
                $user->id,
                'cover',
                $coverCrop
            );
            $mediaWasChanged = true;
        } elseif (!empty($validated['cover_photo_image_id'])) {
            $portfolioImage = PhotographerPortfolioImage::where('id', $validated['cover_photo_image_id'])
                ->where('photographer_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $profile->cover_photo_path = $this->storePhotographerMediaFromPortfolioImage($portfolioImage, $user->id, 'cover', $coverCrop);
                $mediaWasChanged = true;
            }
        } elseif ($coverCrop && $previousCoverPhoto && File::exists(public_path($previousCoverPhoto))) {
            $profile->cover_photo_path = $this->storeCroppedPhotographerCoverImage(
                public_path($previousCoverPhoto),
                $user->id,
                $coverCrop
            );
            $mediaWasChanged = true;
        }

        if (!$mediaWasChanged) {
            return response()->json([
                'message' => 'No profile or cover image change was received.',
                'errors' => [
                    'profile_photo_upload' => ['Choose an image or adjust the crop and try again.'],
                ],
            ], 422);
        }

        if ($previousProfilePhoto && $previousProfilePhoto !== $profile->profile_photo_path) {
            $this->deletePhotographerMediaIfOwned($previousProfilePhoto, $user->id);
        }

        if ($previousCoverPhoto && $previousCoverPhoto !== $profile->cover_photo_path) {
            $this->deletePhotographerMediaIfOwned($previousCoverPhoto, $user->id);
        }

        $profile->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Profile media updated successfully.',
                'profile_photo_path' => $profile->profile_photo_path,
                'profile_photo_url' => $profile->profile_photo_path ? asset($profile->profile_photo_path) . '?v=' . $profile->updated_at?->timestamp : null,
                'cover_photo_path' => $profile->cover_photo_path,
                'cover_photo_url' => $profile->cover_photo_path ? asset($profile->cover_photo_path) . '?v=' . $profile->updated_at?->timestamp : null,
            ]);
        }

        return back()->with('status', 'Profile media updated successfully.');
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

    private function cleanProfileBio(?string $bio): ?string
    {
        if ($bio === null) {
            return null;
        }

        $bio = strip_tags($bio);
        $bio = str_replace(["\r\n", "\r"], "\n", $bio);
        $bio = preg_replace("/[ \t]+\n/", "\n", $bio);
        $bio = preg_replace("/\n{3,}/", "\n\n", $bio);
        $bio = trim($bio);

        return $bio === '' ? null : $bio;
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

        return redirect()->route('portfolio.create')
            ->with('status', 'Photos uploaded successfully! Now create your portfolio.');
    }

    private function storePhotographerMediaUpload(UploadedFile $file, int $userId, string $type, ?array $crop = null): string
    {
        if ($type === 'profile') {
            return $this->storeCroppedPhotographerProfileImage($file->getRealPath(), $userId, $crop);
        }

        return $this->storeCroppedPhotographerCoverImage($file->getRealPath(), $userId, $crop);
    }

    private function storePhotographerMediaFromPortfolioImage(PhotographerPortfolioImage $image, int $userId, string $type, ?array $crop = null): string
    {
        $sourcePath = $image->full_path ?: $image->medium_path ?: $image->thumbnail_path;

        if (!$sourcePath || !File::exists(public_path($sourcePath))) {
            return '';
        }

        if ($type === 'profile') {
            return $this->storeCroppedPhotographerProfileImage(public_path($sourcePath), $userId, $crop);
        }

        return $this->storeCroppedPhotographerCoverImage(public_path($sourcePath), $userId, $crop);
    }

    private function storeCroppedPhotographerProfileImage(string $sourcePath, int $userId, ?array $crop = null): string
    {
        return $this->storeCroppedPhotographerMediaImage($sourcePath, $userId, 'profile', 900, 900, $crop);
    }

    private function storeCroppedPhotographerCoverImage(string $sourcePath, int $userId, ?array $crop = null): string
    {
        return $this->storeCroppedPhotographerMediaImage($sourcePath, $userId, 'cover', 1800, 600, $crop);
    }

    private function storeCroppedPhotographerMediaImage(string $sourcePath, int $userId, string $type, int $targetWidth, int $targetHeight, ?array $crop = null): string
    {
        $folder = public_path("uploads/photographers/{$userId}/{$type}");

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $sourceImage = $this->loadGdImage($sourcePath);

        if (!$sourceImage) {
            throw new \RuntimeException('Unable to load photographer media image for cropping.');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $sourceCropX = 0;
        $sourceCropY = 0;
        $sourceCropWidth = $sourceWidth;
        $sourceCropHeight = $sourceHeight;

        if ($crop) {
            $sourceCropX = (int) floor(max(0, min($sourceWidth - 1, $crop['x'])));
            $sourceCropY = (int) floor(max(0, min($sourceHeight - 1, $crop['y'])));
            $sourceCropWidth = (int) floor(max(1, min($sourceWidth - $sourceCropX, $crop['width'])));
            $sourceCropHeight = (int) floor(max(1, min($sourceHeight - $sourceCropY, $crop['height'])));
        } else {
            $targetRatio = $targetWidth / $targetHeight;
            $sourceRatio = $sourceWidth / $sourceHeight;

            if ($sourceRatio > $targetRatio) {
                $sourceCropHeight = $sourceHeight;
                $sourceCropWidth = (int) floor($sourceHeight * $targetRatio);
                $sourceCropX = (int) floor(($sourceWidth - $sourceCropWidth) / 2);
            } else {
                $sourceCropWidth = $sourceWidth;
                $sourceCropHeight = (int) floor($sourceWidth / $targetRatio);
                $sourceCropY = (int) floor(($sourceHeight - $sourceCropHeight) / 2);
            }
        }

        $destinationImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($destinationImage, 255, 255, 255);
        imagefill($destinationImage, 0, 0, $white);

        imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0,
            0,
            $sourceCropX,
            $sourceCropY,
            $targetWidth,
            $targetHeight,
            $sourceCropWidth,
            $sourceCropHeight
        );

        $filename = "{$type}_" . uniqid() . '.jpg';
        $path = "uploads/photographers/{$userId}/{$type}/{$filename}";

        imagejpeg($destinationImage, public_path($path), 90);
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);

        return $path;
    }

    private function parseProfileCropData(?string $cropData): ?array
    {
        if (!$cropData) {
            return null;
        }

        $decoded = json_decode($cropData, true);

        if (
            !is_array($decoded) ||
            !isset($decoded['x'], $decoded['y'], $decoded['width'], $decoded['height']) ||
            (float) $decoded['width'] <= 0 ||
            (float) $decoded['height'] <= 0
        ) {
            return null;
        }

        return [
            'x' => (float) $decoded['x'],
            'y' => (float) $decoded['y'],
            'width' => (float) $decoded['width'],
            'height' => (float) $decoded['height'],
        ];
    }

    private function loadGdImage(string $path): mixed
    {
        $mime = function_exists('mime_content_type') ? mime_content_type($path) : null;

        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => $this->loadGdImageByExtension($path),
        };
    }

    private function loadGdImageByExtension(string $path): mixed
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($path),
            'png' => imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function deletePhotographerMediaIfOwned(?string $path, int $userId): void
    {
        if (!$path) {
            return;
        }

        $ownedPrefixes = [
            "uploads/photographers/{$userId}/profile/",
            "uploads/photographers/{$userId}/cover/",
        ];

        foreach ($ownedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                \App\Services\ImageProcessingService::deleteImage($path);
                break;
            }
        }
    }
}
