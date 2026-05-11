<?php

namespace App\Http\Controllers;

use App\Helpers\PhotographerOptions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotographerBrowseController extends Controller
{
    /**
     * Display a listing of photographers with filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:120'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:100'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'travel' => ['nullable', 'in:1'],
            'experience_level' => ['nullable', 'string', 'max:50'],
            'verified' => ['nullable', 'in:1'],
            'sort' => ['nullable', 'in:newest,oldest,name'],
        ]);

        if (!$request->has('country') && $defaultCountry = $this->defaultCountryFor($request->user())) {
            $filters['country'] = $defaultCountry;
        }

        $serviceOptions = PhotographerOptions::services();
        $specialtyOptions = PhotographerOptions::specialties('photographer');
        $experienceOptions = $this->experienceOptions();
        $selectedServices = array_values(array_intersect($filters['services'] ?? [], array_keys($serviceOptions)));
        $selectedSpecialties = array_values(array_intersect($filters['specialties'] ?? [], array_keys($specialtyOptions)));

        $query = User::with([
            'photographerProfile',
            'photographerPortfolioImages' => function($q) {
                $q->where('is_public', true)
                  ->orderBy('created_at', 'desc')
                  ->limit(1);
            },
        ])
            ->withCount(['photographerPortfolioImages as public_photos_count' => function ($q) {
                $q->where('is_public', true);
            }])
            ->where('is_photographer', true)
            ->where('is_admin', false)
            ->whereHas('photographerProfile', function ($profileQuery) {
                $profileQuery->where('is_public', true);
            });

        if (!empty($filters['search'])) {
            $search = ltrim(trim($filters['search']), '@');
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhereHas('photographerProfile', function ($profileQuery) use ($search) {
                        $profileQuery
                            ->where('professional_name', 'like', "%{$search}%")
                            ->orWhere('location_city', 'like', "%{$search}%")
                            ->orWhere('location_country', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['country'])) {
            $query->whereHas('photographerProfile', function ($profileQuery) use ($filters) {
                $profileQuery->where('location_country_code', strtoupper($filters['country']));
            });
        }

        if (!empty($filters['city']) && !empty($filters['country'])) {
            $query->whereHas('photographerProfile', function ($profileQuery) use ($filters) {
                $profileQuery->where('location_city', 'like', '%' . trim($filters['city']) . '%');
            });
        }

        if (!empty($filters['experience_level']) && array_key_exists($filters['experience_level'], $experienceOptions)) {
            $query->whereHas('photographerProfile', function ($profileQuery) use ($filters) {
                $profileQuery->where('experience_level', $filters['experience_level']);
            });
        }

        if (($filters['travel'] ?? null) === '1') {
            $query->whereHas('photographerProfile', function ($profileQuery) {
                $profileQuery->where('available_for_travel', true);
            });
        }

        foreach ($selectedServices as $service) {
            $query->whereHas('photographerProfile', function ($profileQuery) use ($service) {
                $profileQuery->whereJsonContains('services_offered', $service);
            });
        }

        foreach ($selectedSpecialties as $specialty) {
            $query->whereHas('photographerProfile', function ($profileQuery) use ($specialty) {
                $profileQuery->whereJsonContains('specialties', $specialty);
            });
        }

        if (($filters['verified'] ?? null) === '1') {
            $query->whereHas('photographerProfile', function ($profileQuery) {
                $profileQuery->whereNotNull('verified_at');
            });
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
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $photographers = $query->paginate(24);
        $photographers->appends($request->query());

        $searchSuggestions = User::query()
            ->where('is_photographer', true)
            ->where('is_admin', false)
            ->whereHas('photographerProfile', fn ($profileQuery) => $profileQuery->where('is_public', true))
            ->orderBy('name')
            ->limit(80)
            ->get(['name', 'username'])
            ->flatMap(fn (User $user) => array_filter([$user->name, '@' . $user->username]))
            ->unique()
            ->values();

        return view('photographers.browse', [
            'photographers' => $photographers,
            'filters' => $filters,
            'countries' => config('countries', []),
            'serviceOptions' => $serviceOptions,
            'specialtyOptions' => $specialtyOptions,
            'experienceOptions' => $experienceOptions,
            'selectedServices' => $selectedServices,
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
