<?php

namespace App\Http\Controllers;

use App\Helpers\PhotographerOptions;
use App\Helpers\ModelProfileOptions;
use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelBrowseController extends Controller
{
    /**
     * Display a listing of models with filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'in:male,female,other'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'has_polaroids' => ['nullable', 'in:1'],
            'experience_level' => ['nullable', 'string', 'max:50'],
            'age_min' => ['nullable', 'integer', 'min:18', 'max:100'],
            'age_max' => ['nullable', 'integer', 'min:18', 'max:100'],
            'height_min' => ['nullable', 'integer', 'min:100', 'max:250'],
            'height_max' => ['nullable', 'integer', 'min:100', 'max:250'],
            'body_min' => ['nullable', 'integer', 'min:20', 'max:200'],
            'body_max' => ['nullable', 'integer', 'min:20', 'max:200'],
            'waist_min' => ['nullable', 'integer', 'min:20', 'max:200'],
            'waist_max' => ['nullable', 'integer', 'min:20', 'max:200'],
            'hips_min' => ['nullable', 'integer', 'min:20', 'max:200'],
            'hips_max' => ['nullable', 'integer', 'min:20', 'max:200'],
            'inseam_min' => ['nullable', 'integer', 'min:20', 'max:150'],
            'inseam_max' => ['nullable', 'integer', 'min:20', 'max:150'],
            'shoe_size_region' => ['nullable', 'string', 'max:30'],
            'shoe_size_min' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'shoe_size_max' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'dress_size_region' => ['nullable', 'string', 'max:30'],
            'dress_size_min' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'dress_size_max' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'hair_colors' => ['nullable', 'array'],
            'hair_colors.*' => ['string', 'max:60'],
            'eye_colors' => ['nullable', 'array'],
            'eye_colors.*' => ['string', 'max:60'],
            'verified' => ['nullable', 'in:1'],
            'sort' => ['nullable', 'in:newest,oldest,name'],
        ]);

        if (!$request->has('country') && $defaultCountry = $this->defaultCountryFor($request->user())) {
            $filters['country'] = $defaultCountry;
        }

        $specialtyOptions = PhotographerOptions::specialties('model');
        $experienceOptions = $this->experienceOptions();
        $hairColorOptions = ModelProfileOptions::hairColors();
        $eyeColorOptions = ModelProfileOptions::eyeColors();
        $shoeSizeRegions = ModelProfileOptions::shoeSizeRegions();
        $shoeSizes = ModelProfileOptions::shoeSizes();
        $dressSizeRegions = ModelProfileOptions::dressSizeRegions();
        $dressSizes = ModelProfileOptions::dressSizes();
        $selectedSpecialties = array_values(array_intersect($filters['specialties'] ?? [], array_keys($specialtyOptions)));
        $selectedHairColors = array_values(array_intersect($filters['hair_colors'] ?? [], $hairColorOptions));
        $selectedEyeColors = array_values(array_intersect($filters['eye_colors'] ?? [], $eyeColorOptions));

        $query = ModelProfile::with('user')
            ->withCount(['portfolioImages as public_photos_count' => function ($q) {
                $q->where('is_public', true);
            }])
            ->where('is_public', true)
            ->whereHas('user', function($q) {
                $q->where('is_photographer', false)
                  ->where('is_admin', false);
            });

        if (!empty($filters['search'])) {
            $search = ltrim(trim($filters['search']), '@');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['country'])) {
            $query->where('location_country_code', strtoupper($filters['country']));
        }

        if (!empty($filters['city']) && !empty($filters['country'])) {
            $query->where('location_city', 'like', '%' . trim($filters['city']) . '%');
        }

        if (!empty($filters['experience_level']) && array_key_exists($filters['experience_level'], $experienceOptions)) {
            $query->where('experience_level', $filters['experience_level']);
        }

        $this->applyAgeRange($query, $filters);
        $this->applyNumericRange($query, 'height_cm', $filters['height_min'] ?? null, $filters['height_max'] ?? null);
        $this->applyNumericRange($query, 'waist_cm', $filters['waist_min'] ?? null, $filters['waist_max'] ?? null);
        $this->applyNumericRange($query, 'hips_cm', $filters['hips_min'] ?? null, $filters['hips_max'] ?? null);
        $this->applyNumericRange($query, 'inseam_cm', $filters['inseam_min'] ?? null, $filters['inseam_max'] ?? null);
        $this->applyBodyRange($query, $filters['body_min'] ?? null, $filters['body_max'] ?? null);
        $this->applySizeRange($query, 'shoe_size', $filters['shoe_size_region'] ?? null, $filters['shoe_size_min'] ?? null, $filters['shoe_size_max'] ?? null, $shoeSizeRegions);
        $this->applySizeRange($query, 'dress_size', $filters['dress_size_region'] ?? null, $filters['dress_size_min'] ?? null, $filters['dress_size_max'] ?? null, $dressSizeRegions);

        if ($selectedHairColors !== []) {
            $query->whereIn('hair_color', $selectedHairColors);
        }

        if ($selectedEyeColors !== []) {
            $query->whereIn('eye_color', $selectedEyeColors);
        }

        foreach ($selectedSpecialties as $specialty) {
            $query->whereJsonContains('specialties', $specialty);
        }

        if (($filters['has_polaroids'] ?? null) === '1') {
            $query->whereHas('portfolioImages', function ($imageQuery) {
                $imageQuery
                    ->where('is_polaroid', true)
                    ->where('is_public', true);
            });
        }

        if (($filters['verified'] ?? null) === '1') {
            $query->whereNotNull('verified_at');
        }

        // Sort
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name':
                $query->orderBy(User::select('name')
                    ->whereColumn('users.id', 'model_profiles.user_id')
                    ->limit(1), 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $models = $query->paginate(24);
        $models->appends($request->query());

        $searchSuggestions = ModelProfile::query()
            ->with('user:id,name,username')
            ->where('is_public', true)
            ->whereHas('user', function ($userQuery) {
                $userQuery
                    ->where('is_photographer', false)
                    ->where('is_admin', false);
            })
            ->orderBy(User::select('name')
                ->whereColumn('users.id', 'model_profiles.user_id')
                ->limit(1))
            ->limit(80)
            ->get()
            ->flatMap(fn (ModelProfile $profile) => array_filter([
                $profile->display_name,
                $profile->user ? '@' . $profile->user->username : null,
            ]))
            ->unique()
            ->values();

        return view('models.browse', [
            'models' => $models,
            'filters' => $filters,
            'countries' => config('countries', []),
            'specialtyOptions' => $specialtyOptions,
            'experienceOptions' => $experienceOptions,
            'hairColorOptions' => $hairColorOptions,
            'eyeColorOptions' => $eyeColorOptions,
            'shoeSizeRegions' => $shoeSizeRegions,
            'shoeSizes' => $shoeSizes,
            'dressSizeRegions' => $dressSizeRegions,
            'dressSizes' => $dressSizes,
            'selectedSpecialties' => $selectedSpecialties,
            'selectedHairColors' => $selectedHairColors,
            'selectedEyeColors' => $selectedEyeColors,
            'searchSuggestions' => $searchSuggestions,
        ]);
    }

    private function experienceOptions(): array
    {
        return [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            'professional' => 'Professional',
        ];
    }

    private function defaultCountryFor(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        $profile = $user->is_photographer ? $user->photographerProfile : $user->modelProfile;
        $country = strtoupper((string) ($profile?->location_country_code ?? ''));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    private function applyNumericRange($query, string $column, mixed $min, mixed $max): void
    {
        if ($min !== null && $min !== '') {
            $query->where($column, '>=', $min);
        }

        if ($max !== null && $max !== '') {
            $query->where($column, '<=', $max);
        }
    }

    private function applyAgeRange($query, array $filters): void
    {
        if (!empty($filters['age_min'])) {
            $query->whereDate('date_of_birth', '<=', now()->subYears((int) $filters['age_min'])->toDateString());
        }

        if (!empty($filters['age_max'])) {
            $query->whereDate('date_of_birth', '>=', now()->subYears(((int) $filters['age_max']) + 1)->addDay()->toDateString());
        }
    }

    private function applyBodyRange($query, mixed $min, mixed $max): void
    {
        if (($min === null || $min === '') && ($max === null || $max === '')) {
            return;
        }

        $query->where(function ($rangeQuery) use ($min, $max) {
            foreach (['bust_cm', 'chest_cm'] as $column) {
                $rangeQuery->orWhere(function ($columnQuery) use ($column, $min, $max) {
                    if ($min !== null && $min !== '') {
                        $columnQuery->where($column, '>=', $min);
                    }

                    if ($max !== null && $max !== '') {
                        $columnQuery->where($column, '<=', $max);
                    }
                });
            }
        });
    }

    private function applySizeRange($query, string $field, ?string $region, mixed $min, mixed $max, array $validRegions): void
    {
        if (!array_key_exists((string) $region, $validRegions)) {
            return;
        }

        $hasMin = $min !== null && $min !== '';
        $hasMax = $max !== null && $max !== '';

        if (!$hasMin && !$hasMax) {
            return;
        }

        $query->where("{$field}_region", $region);

        if ($hasMin) {
            $query->whereRaw("CAST({$field}_value AS DECIMAL(6,2)) >= ?", [(float) $min]);
        }

        if ($hasMax) {
            $query->whereRaw("CAST({$field}_value AS DECIMAL(6,2)) <= ?", [(float) $max]);
        }
    }
}
