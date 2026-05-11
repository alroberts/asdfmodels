<?php

namespace App\Http\Controllers;

use App\Helpers\PhotographerOptions;
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
            'verified' => ['nullable', 'in:1'],
            'sort' => ['nullable', 'in:newest,oldest,name'],
        ]);

        if (!$request->has('country') && $defaultCountry = $this->defaultCountryFor($request->user())) {
            $filters['country'] = $defaultCountry;
        }

        $specialtyOptions = PhotographerOptions::specialties('model');
        $experienceOptions = $this->experienceOptions();
        $selectedSpecialties = array_values(array_intersect($filters['specialties'] ?? [], array_keys($specialtyOptions)));

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
            'selectedSpecialties' => $selectedSpecialties,
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
}
