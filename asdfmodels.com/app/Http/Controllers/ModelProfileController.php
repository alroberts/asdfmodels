<?php

namespace App\Http\Controllers;

use App\Helpers\ModelProfileOptions;
use App\Models\Connection;
use App\Models\ModelProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ModelProfileController extends Controller
{
    /**
     * Display the specified model profile (public view).
     */
    public function show(string $username): View
    {
        $user = $this->resolveProfileUser($username, 'modelProfile');
        $viewer = Auth::user();

        if (
            !$user->modelProfile ||
            (
                !$user->modelProfile->is_public &&
                (!$viewer || $viewer->id !== $user->id)
            )
        ) {
            abort(404);
        }

        $profile = $user->modelProfile;
        if ($profile) {
            $profile->setRelation('user', $user);
        }
        $featuredImages = \App\Models\PortfolioImage::where('model_id', $user->id)
            ->where('is_featured', true)
            ->where('is_public', true)
            ->limit(6)
            ->get();
        $polaroids = \App\Models\PortfolioImage::where('model_id', $user->id)
            ->where('is_polaroid', true)
            ->where('is_public', true)
            ->limit(6)
            ->get();
        $publicGalleries = PortfolioAlbum::with(['coverImage', 'images' => function ($query) {
                $query->where('is_public', true)
                    ->orderBy('display_order')
                    ->orderByDesc('uploaded_at')
                    ->limit(4);
            }])
            ->withCount(['images' => function ($query) {
                $query->where('is_public', true);
            }])
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();
        $portfolioImages = collect();
        $portfolioMediaGroups = collect();
        $visibleCredits = PortfolioCredit::visibleOnProfile($user, 'model')
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

                return ($image instanceof PortfolioImage || $image instanceof \App\Models\PhotographerPortfolioImage)
                    && $image->is_public;
            })
            ->values();
        $pendingCredits = ($viewer && $viewer->id === $user->id)
            ? PortfolioCredit::awaitingResponse($user, 'model')->with(['creditable', 'owner'])->latest()->get()
            : collect();
        $connections = $this->profileConnections($user);
        $viewerConnection = $viewer && $viewer->id !== $user->id
            ? Connection::with(['requester', 'recipient'])->between($viewer->id, $user->id)->first()
            : null;

        if ($viewer && $viewer->id === $user->id) {
            $portfolioImages = $user->portfolioImages()
                ->orderByDesc('is_featured')
                ->orderByDesc('uploaded_at')
                ->get();

            $polaroids = $portfolioImages
                ->where('is_polaroid', true)
                ->values();

            if ($polaroids->isNotEmpty()) {
                $portfolioMediaGroups->push([
                    'id' => 'polaroids',
                    'label' => 'Polaroids',
                    'count' => $polaroids->count(),
                    'cover' => $polaroids->first()->thumbnail_path,
                    'images' => $polaroids,
                ]);
            }

            $ungroupedImages = $portfolioImages
                ->where('is_polaroid', false)
                ->whereNull('album_id')
                ->values();

            if ($ungroupedImages->isNotEmpty()) {
                $portfolioMediaGroups->push([
                    'id' => 'ungrouped',
                    'label' => 'Ungrouped',
                    'count' => $ungroupedImages->count(),
                    'cover' => $ungroupedImages->first()->thumbnail_path,
                    'images' => $ungroupedImages,
                ]);
            }

            PortfolioAlbum::with(['images' => function ($query) {
                    $query->where('is_public', true)
                        ->orderBy('display_order')
                        ->orderByDesc('uploaded_at');
                }])
                ->where('user_id', $user->id)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get()
                ->each(function (PortfolioAlbum $album) use ($portfolioMediaGroups) {
                    $images = $album->images->values();

                    if ($images->isEmpty()) {
                        return;
                    }

                    $coverImage = $album->coverImage ?: $images->first();

                    $portfolioMediaGroups->push([
                        'id' => 'album-' . $album->id,
                        'label' => $album->name,
                        'count' => $images->count(),
                        'cover' => $coverImage?->thumbnail_path,
                        'images' => $images,
                    ]);
                });
        }

        return view('models.show', [
            'user' => $user,
            'profile' => $profile,
            'featuredImages' => $featuredImages,
            'polaroids' => $polaroids,
            'publicGalleries' => $publicGalleries,
            'portfolioImages' => $portfolioImages,
            'portfolioMediaGroups' => $portfolioMediaGroups,
            'ownerCanManage' => $viewer && $viewer->id === $user->id,
            'featuredAlbumCredits' => $featuredAlbumCredits,
            'featuredImageCredits' => $featuredImageCredits,
            'pendingCredits' => $pendingCredits,
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

    /**
     * Display public galleries for the specified model profile.
     */
    public function galleries(string $username): View
    {
        $user = $this->resolveProfileUser($username, 'modelProfile');
        $viewer = Auth::user();

        if (
            !$user->modelProfile ||
            (
                !$user->modelProfile->is_public &&
                (!$viewer || $viewer->id !== $user->id)
            )
        ) {
            abort(404);
        }

        $profile = $user->modelProfile;
        $profile->setRelation('user', $user);

        $publicGalleries = PortfolioAlbum::with(['coverImage', 'images' => function ($query) {
                $query->where('is_public', true)
                    ->orderBy('display_order')
                    ->orderByDesc('uploaded_at')
                    ->limit(6);
            }])
            ->withCount(['images' => function ($query) {
                $query->where('is_public', true);
            }])
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        return view('models.galleries', [
            'user' => $user,
            'profile' => $profile,
            'publicGalleries' => $publicGalleries,
        ]);
    }

    /**
     * Show the form for editing the authenticated user's model profile.
     */
    public function edit(Request $request): View
    {
        $user = Auth::user();

        // Ensure user is not a photographer
        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $profile = $user->modelProfile ?? new ModelProfile(['user_id' => $user->id]);

        $viewData = [
            'user' => $user,
            'profile' => $profile,
            'countries' => config('countries'),
            'displayNameFormats' => ModelProfileOptions::displayNameFormats(),
            'measurementSystems' => ModelProfileOptions::measurementSystems(),
            'measurementSystemChoices' => ModelProfileOptions::measurementSystemChoicesForCountry($profile->location_country_code),
            'shoeSizeRegions' => ModelProfileOptions::shoeSizeRegions(),
            'shoeSizes' => ModelProfileOptions::shoeSizes(),
            'dressSizeRegions' => ModelProfileOptions::dressSizeRegions(),
            'dressSizes' => ModelProfileOptions::dressSizes(),
            'hairColors' => ModelProfileOptions::hairColors(),
            'eyeColors' => ModelProfileOptions::eyeColors(),
            'specialtiesOptions' => \App\Helpers\PhotographerOptions::specialties('model'),
            'portfolioImages' => $user->portfolioImages()
                ->orderByDesc('is_featured')
                ->orderByDesc('uploaded_at')
                ->limit(24)
                ->get(),
        ];

        if ($user->hasCompletedModelProfile()) {
            return view('models.edit', $viewData);
        }

        return view('models.edit-wizard', $viewData);
    }

    /**
     * Update the authenticated user's model profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('users', 'username')->ignore($user->id),
                Rule::unique('username_histories', 'username')->ignore($user->username, 'username'),
            ],
            'bio' => ['nullable', 'string', 'max:2000'],
            'display_name_format' => ['nullable', 'in:first_name_last_initial,first_name,initials,full_name'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_country' => ['nullable', 'string', 'max:255'],
            'location_geoname_id' => ['nullable', 'integer', 'exists:geonames_locations,geoname_id'],
            'location_country_code' => ['nullable', 'string', 'size:2'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'measurement_system' => ['nullable', 'in:metric,imperial,us_customary,mixed_uk,mixed_ca,mixed_metric_default'],
            
            // Canonical measurements
            'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:25', 'max:250'],
            'chest_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'waist_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'inseam_cm' => ['nullable', 'numeric', 'min:20', 'max:150'],
            'suit_size' => ['nullable', 'string', 'max:50'],
            
            // Female measurements
            'bust_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'hips_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'dress_size_region' => ['nullable', 'in:eu,uk,us'],
            'dress_size_value' => ['nullable', 'string', 'max:20'],
            
            // Common fields
            'shoe_size_region' => ['nullable', 'in:eu,uk,us_women,us_men'],
            'shoe_size_value' => ['nullable', 'string', 'max:20'],
            'hair_color' => ['nullable', 'string', 'max:100'],
            'eye_color' => ['nullable', 'string', 'max:100'],
            
            // Professional
            'experience_level' => ['nullable', 'string', 'max:50'],
            'experience_start_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            
            // Contact
            'public_email' => ['nullable', 'email', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['nullable', 'in:instagram,facebook,x,tiktok,youtube,behance,linkedin,website'],
            'social_links.*.url' => ['nullable', 'url', 'max:255'],
            'profile_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'cover_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'profile_photo_image_id' => ['nullable', 'integer', 'exists:portfolio_images,id'],
            'cover_photo_image_id' => ['nullable', 'integer', 'exists:portfolio_images,id'],
            
            // Settings
            'is_public' => ['boolean'],
            'contains_nudity' => ['boolean'],
        ]);

        $validated['measurement_system'] = $validated['measurement_system']
            ?? ModelProfileOptions::defaultMeasurementSystemForCountry($validated['location_country_code'] ?? $user->modelProfile?->location_country_code);

        $validated['public_email'] = $validated['public_email'] ?: $user->email;

        if (($validated['measurement_system'] ?? null) === 'imperial') {
            $validated['measurement_system'] = 'us_customary';
        }

        if (($validated['display_name_format'] ?? null) === 'full_name' && !$user->modelProfile?->isVerified()) {
            return back()
                ->withErrors(['display_name_format' => 'Full name display is only available to verified members.'])
                ->withInput();
        }

        if (!ModelProfileOptions::isValidHairColor($validated['hair_color'] ?? null)) {
            return back()->withErrors(['hair_color' => 'Choose a valid hair colour option.'])->withInput();
        }

        if (!ModelProfileOptions::isValidEyeColor($validated['eye_color'] ?? null)) {
            return back()->withErrors(['eye_color' => 'Choose a valid eye colour option.'])->withInput();
        }

        if (!ModelProfileOptions::isValidShoeSize($validated['shoe_size_region'] ?? null, $validated['shoe_size_value'] ?? null)) {
            return back()->withErrors(['shoe_size_value' => 'Choose a valid shoe size.'])->withInput();
        }

        if (!ModelProfileOptions::isValidDressSize($validated['dress_size_region'] ?? null, $validated['dress_size_value'] ?? null)) {
            return back()->withErrors(['dress_size_value' => 'Choose a valid dress size.'])->withInput();
        }

        $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties('model');
        $validated['specialties'] = array_values(array_intersect(
            $validated['specialties'] ?? [],
            array_keys($specialtiesOptions)
        ));

        // If geoname_id is provided, fetch and populate city/country from GeoNames
        if (isset($validated['location_geoname_id']) && $validated['location_geoname_id']) {
            $location = \App\Models\GeoNameLocation::find($validated['location_geoname_id']);
            if ($location) {
                $validated['location_city'] = $location->name;
                $countries = config('countries', []);
                $validated['location_country'] = $countries[$location->country_code] ?? $location->country_code;
            }
        }

        $validated['height'] = $this->formatHeight($validated['height_cm'] ?? null);
        $validated['weight_kg'] = $validated['weight_kg'] ?? null;
        $validated['weight'] = $this->formatWeight($validated['weight_kg']);
        $validated['chest'] = $this->formatLength($validated['chest_cm'] ?? null);
        $validated['waist'] = $this->formatLength($validated['waist_cm'] ?? null);
        $validated['inseam'] = $this->formatLength($validated['inseam_cm'] ?? null);
        $validated['bust'] = $this->formatLength($validated['bust_cm'] ?? null);
        $validated['hips'] = $this->formatLength($validated['hips_cm'] ?? null);
        $validated['shoe_size'] = $this->formatMappedSize($validated['shoe_size_region'] ?? null, $validated['shoe_size_value'] ?? null, ModelProfileOptions::shoeSizeRegions());
        $validated['dress_size'] = $this->formatMappedSize($validated['dress_size_region'] ?? null, $validated['dress_size_value'] ?? null, ModelProfileOptions::dressSizeRegions());
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

        $profile = $user->modelProfile ?? new ModelProfile();
        $profile->user_id = $user->id;
        $requestedUsername = $validated['username'] ?? null;
        unset($validated['username']);

        $previousProfilePhoto = $profile->profile_photo_path;
        $previousCoverPhoto = $profile->cover_photo_path;

        if ($request->hasFile('profile_photo_upload')) {
            $validated['profile_photo_path'] = $this->storeModelMediaUpload(
                $request->file('profile_photo_upload'),
                $user->id,
                'profile'
            );
        } elseif (!empty($validated['profile_photo_image_id'])) {
            $portfolioImage = PortfolioImage::where('id', $validated['profile_photo_image_id'])
                ->where('model_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $validated['profile_photo_path'] = $this->storeModelMediaFromPortfolioImage($portfolioImage, $user->id, 'profile');
            }
        }

        if ($request->hasFile('cover_photo_upload')) {
            $validated['cover_photo_path'] = $this->storeModelMediaUpload(
                $request->file('cover_photo_upload'),
                $user->id,
                'cover'
            );
        } elseif (!empty($validated['cover_photo_image_id'])) {
            $portfolioImage = PortfolioImage::where('id', $validated['cover_photo_image_id'])
                ->where('model_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $validated['cover_photo_path'] = $this->storeModelMediaFromPortfolioImage($portfolioImage, $user->id, 'cover');
            }
        }

        unset(
            $validated['profile_photo_upload'],
            $validated['cover_photo_upload'],
            $validated['profile_photo_image_id'],
            $validated['cover_photo_image_id']
        );

        $profile->fill($validated);

        if ($previousProfilePhoto && $previousProfilePhoto !== ($validated['profile_photo_path'] ?? $previousProfilePhoto)) {
            $this->deleteModelMediaIfOwned($previousProfilePhoto, $user->id);
        }

        if ($previousCoverPhoto && $previousCoverPhoto !== ($validated['cover_photo_path'] ?? $previousCoverPhoto)) {
            $this->deleteModelMediaIfOwned($previousCoverPhoto, $user->id);
        }

        $profile->save();

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = trim($validated['first_name'] . ' ' . $validated['last_name']);
        if ($user->canEditUsername() && filled($requestedUsername)) {
            $user->changeUsername($requestedUsername);
        } elseif (!filled($user->username)) {
            $user->username = $user->generateUniqueUsername();
        }
        $user->save();

        return redirect()->route('profile.model.edit')
            ->with('status', 'Profile updated successfully.');
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
                redirect()->route(
                    request()->routeIs('models.galleries') ? 'models.galleries' : 'models.show',
                    $history->redirects_to_username
                )->send();
                exit;
            }
        }

        if (!$user) {
            abort(404);
        }

        return $user;
    }

    public function updateBio(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile = $user->modelProfile;

        if (!$profile) {
            abort(404, 'Model profile not found.');
        }

        $profile->bio = $this->cleanProfileBio($validated['bio'] ?? null);
        $profile->save();

        return response()->json([
            'message' => 'Bio updated.',
            'bio' => $profile->bio,
        ]);
    }

    public function updateSpecialties(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $profile = $user->modelProfile;

        if (!$profile) {
            abort(404, 'Model profile not found.');
        }

        $options = \App\Helpers\PhotographerOptions::specialties('model');
        $validated = $request->validate([
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
        ]);

        $profile->specialties = array_values(array_intersect($validated['specialties'] ?? [], array_keys($options)));
        $profile->save();

        return response()->json([
            'message' => 'Specialties updated.',
            'specialties' => collect($profile->specialties)->map(fn ($key) => [
                'key' => $key,
                'label' => $options[$key] ?? $key,
            ])->values(),
        ]);
    }

    public function updateMeasurements(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $profile = $user->modelProfile;

        if (!$profile) {
            abort(404, 'Model profile not found.');
        }

        $validated = $request->validate([
            'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'chest_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'bust_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'waist_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'hips_cm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'inseam_cm' => ['nullable', 'numeric', 'min:20', 'max:150'],
            'shoe_size_region' => ['nullable', 'string', 'max:20'],
            'shoe_size_value' => ['nullable', 'string', 'max:20'],
            'dress_size_region' => ['nullable', 'string', 'max:20'],
            'dress_size_value' => ['nullable', 'string', 'max:20'],
            'hair_color' => ['nullable', 'string', 'max:100'],
            'eye_color' => ['nullable', 'string', 'max:100'],
        ]);

        $profile->fill($validated);
        $profile->height = $this->formatHeight(isset($validated['height_cm']) ? (int) $validated['height_cm'] : null);
        $profile->chest = $this->formatLength(isset($validated['chest_cm']) ? (float) $validated['chest_cm'] : null);
        $profile->bust = $this->formatLength(isset($validated['bust_cm']) ? (float) $validated['bust_cm'] : null);
        $profile->waist = $this->formatLength(isset($validated['waist_cm']) ? (float) $validated['waist_cm'] : null);
        $profile->hips = $this->formatLength(isset($validated['hips_cm']) ? (float) $validated['hips_cm'] : null);
        $profile->inseam = $this->formatLength(isset($validated['inseam_cm']) ? (float) $validated['inseam_cm'] : null);
        $profile->shoe_size = $this->formatMappedSize($profile->shoe_size_region, $profile->shoe_size_value, \App\Helpers\ModelProfileOptions::shoeSizeRegions());
        $profile->dress_size = $this->formatMappedSize($profile->dress_size_region, $profile->dress_size_value, \App\Helpers\ModelProfileOptions::dressSizeRegions());
        $profile->save();

        return response()->json([
            'message' => 'Measurements updated.',
        ]);
    }

    /**
     * Update just the model media from the public profile page.
     */
    public function updateMedia(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $profile = $user->modelProfile;
        if (!$profile) {
            abort(404);
        }

        $validated = $request->validate([
            'profile_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'cover_photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'profile_photo_image_id' => ['nullable', 'integer', 'exists:portfolio_images,id'],
            'cover_photo_image_id' => ['nullable', 'integer', 'exists:portfolio_images,id'],
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
            $profile->profile_photo_path = $this->storeModelMediaUpload(
                $request->file('profile_photo_upload'),
                $user->id,
                'profile',
                $profileCrop
            );
            $mediaWasChanged = true;
        } elseif (!empty($validated['profile_photo_image_id'])) {
            $portfolioImage = PortfolioImage::where('id', $validated['profile_photo_image_id'])
                ->where('model_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $profile->profile_photo_path = $this->storeModelMediaFromPortfolioImage($portfolioImage, $user->id, 'profile', $profileCrop);
                $mediaWasChanged = true;
            }
        } elseif ($profileCrop && $previousProfilePhoto && File::exists(public_path($previousProfilePhoto))) {
            $profile->profile_photo_path = $this->storeCroppedProfileImage(
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
            $profile->cover_photo_path = $this->storeModelMediaUpload(
                $request->file('cover_photo_upload'),
                $user->id,
                'cover',
                $coverCrop
            );
            $mediaWasChanged = true;
        } elseif (!empty($validated['cover_photo_image_id'])) {
            $portfolioImage = PortfolioImage::where('id', $validated['cover_photo_image_id'])
                ->where('model_id', $user->id)
                ->first();

            if ($portfolioImage) {
                $profile->cover_photo_path = $this->storeModelMediaFromPortfolioImage($portfolioImage, $user->id, 'cover', $coverCrop);
                $mediaWasChanged = true;
            }
        } elseif ($coverCrop && $previousCoverPhoto && File::exists(public_path($previousCoverPhoto))) {
            $profile->cover_photo_path = $this->storeCroppedCoverImage(
                public_path($previousCoverPhoto),
                $user->id,
                $coverCrop
            );
            $mediaWasChanged = true;
        }

        if (!$mediaWasChanged) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'No profile or cover image change was received.',
                    'errors' => [
                        'profile_photo_upload' => ['No profile or cover image change was received. Please choose an image or adjust the crop and try again.'],
                    ],
                ], 422);
            }

            return back()->withErrors([
                'profile_photo_upload' => 'No profile or cover image change was received. Please choose an image or adjust the crop and try again.',
            ]);
        }

        if ($previousProfilePhoto && $previousProfilePhoto !== $profile->profile_photo_path) {
            $this->deleteModelMediaIfOwned($previousProfilePhoto, $user->id);
        }

        if ($previousCoverPhoto && $previousCoverPhoto !== $profile->cover_photo_path) {
            $this->deleteModelMediaIfOwned($previousCoverPhoto, $user->id);
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

    private function formatHeight(?int $cm): ?string
    {
        if (!$cm) {
            return null;
        }

        $totalInches = (int) round($cm / 2.54);
        $feet = intdiv($totalInches, 12);
        $inches = $totalInches % 12;

        return "{$cm} cm / {$feet}'{$inches}\"";
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

    private function formatWeight(?float $kg): ?string
    {
        if (!$kg) {
            return null;
        }

        $lbs = round($kg * 2.20462);
        $kgText = rtrim(rtrim(number_format($kg, 2, '.', ''), '0'), '.');

        return "{$kgText} kg / {$lbs} lbs";
    }

    private function formatLength(?float $cm): ?string
    {
        if (!$cm) {
            return null;
        }

        $inches = round($cm / 2.54, 1);
        $cmText = rtrim(rtrim(number_format($cm, 2, '.', ''), '0'), '.');
        $inchesText = rtrim(rtrim(number_format($inches, 1, '.', ''), '0'), '.');

        return "{$cmText} cm / {$inchesText} in";
    }

    private function formatMappedSize(?string $region, ?string $value, array $labels): ?string
    {
        if (blank($region) || blank($value)) {
            return null;
        }

        return ($labels[$region] ?? strtoupper($region)) . ' ' . $value;
    }

    private function storeModelMediaUpload(UploadedFile $file, int $userId, string $type, ?array $crop = null): string
    {
        $folder = public_path("uploads/models/{$userId}/{$type}");

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        if ($type === 'profile') {
            return $this->storeCroppedProfileImage($file->getRealPath(), $userId, $crop);
        }

        if ($type === 'cover') {
            return $this->storeCroppedCoverImage($file->getRealPath(), $userId, $crop);
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        $maxEdge = $type === 'cover' ? 1800 : 900;
        if (max($image->width(), $image->height()) > $maxEdge) {
            if ($image->width() >= $image->height()) {
                $image->scale(width: $maxEdge);
            } else {
                $image->scale(height: $maxEdge);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? ($extension === 'jpeg' ? 'jpg' : $extension) : 'jpg';
        $filename = "{$type}_" . uniqid() . ".{$extension}";
        $path = "uploads/models/{$userId}/{$type}/{$filename}";
        $image->save(public_path($path), quality: 90);

        return $path;
    }

    private function storeModelMediaFromPortfolioImage(PortfolioImage $image, int $userId, string $type, ?array $crop = null): string
    {
        $sourcePath = $image->full_path ?: $image->medium_path ?: $image->thumbnail_path;

        if (!$sourcePath || !File::exists(public_path($sourcePath))) {
            return '';
        }

        $folder = public_path("uploads/models/{$userId}/{$type}");

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        if ($type === 'profile') {
            return $this->storeCroppedProfileImage(public_path($sourcePath), $userId, $crop);
        }

        if ($type === 'cover') {
            return $this->storeCroppedCoverImage(public_path($sourcePath), $userId, $crop);
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $filename = "{$type}_portfolio_{$image->id}_" . uniqid() . ".{$extension}";
        $path = "uploads/models/{$userId}/{$type}/{$filename}";

        File::copy(public_path($sourcePath), public_path($path));

        return $path;
    }

    private function storeCroppedProfileImage(string $sourcePath, int $userId, ?array $crop = null): string
    {
        return $this->storeCroppedModelMediaImage($sourcePath, $userId, 'profile', 900, 900, $crop);
    }

    private function storeCroppedCoverImage(string $sourcePath, int $userId, ?array $crop = null): string
    {
        return $this->storeCroppedModelMediaImage($sourcePath, $userId, 'cover', 1800, 600, $crop);
    }

    private function storeCroppedModelMediaImage(string $sourcePath, int $userId, string $type, int $targetWidth, int $targetHeight, ?array $crop = null): string
    {
        $folder = public_path("uploads/models/{$userId}/{$type}");

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $sourceImage = $this->loadGdImage($sourcePath);

        if (!$sourceImage) {
            throw new \RuntimeException('Unable to load model media image for cropping.');
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
                $sourceCropY = 0;
            } else {
                $sourceCropWidth = $sourceWidth;
                $sourceCropHeight = (int) floor($sourceWidth / $targetRatio);
                $sourceCropX = 0;
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
        $path = "uploads/models/{$userId}/{$type}/{$filename}";

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

    private function deleteModelMediaIfOwned(?string $path, int $userId): void
    {
        if (!$path) {
            return;
        }

        $ownedPrefixes = [
            "uploads/models/{$userId}/profile/",
            "uploads/models/{$userId}/cover/",
        ];

        foreach ($ownedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                \App\Services\ImageProcessingService::deleteImage($path);
                break;
            }
        }
    }
}
