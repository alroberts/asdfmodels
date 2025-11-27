<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeoNameLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LocationLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $term = Str::lower($validated['q']);
        $country = isset($validated['country']) ? Str::upper($validated['country']) : null;
        $limit = $validated['limit'] ?? 10;

        $cacheKey = sprintf('geonames:lookup:%s:%s:%d', $term, $country ?? 'all', $limit);

        $results = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($term, $country, $limit) {
            return GeoNameLocation::query()
                ->where('feature_class', 'P')
                ->when($country, fn ($query) => $query->where('country_code', $country))
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term.'%')
                        ->orWhere('ascii_name', 'like', $term.'%');
                })
                ->orderByDesc('population')
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn ($location) => $this->formatLocation($location))
                ->values()
                ->all();
        });

        return response()->json(['data' => $results]);
    }

    protected function formatLocation(GeoNameLocation $location): array
    {
        $countryName = $this->countryName($location->country_code);

        return [
            'id' => $location->geoname_id,
            'city' => $location->name,
            'ascii' => $location->ascii_name,
            'country_code' => $location->country_code,
            'country_name' => $countryName,
            'admin1_code' => $location->admin1_code,
            'admin2_code' => $location->admin2_code,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'population' => (int) $location->population,
            'label' => trim($location->name.' · '.($countryName ?? $location->country_code)),
        ];
    }

    protected function countryName(string $countryCode): ?string
    {
        $countries = config('countries', []);
        return $countries[strtoupper($countryCode)] ?? null;
    }
}


