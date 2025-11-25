<?php

namespace App\Http\Controllers;

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
        $query = ModelProfile::with('user')
            ->where('is_public', true)
            ->whereHas('user', function($q) {
                $q->where('is_photographer', false)
                  ->where('is_admin', false);
            });

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where(function($q) use ($request) {
                $q->where('location_city', 'like', "%{$request->location}%")
                  ->orWhere('location_country', 'like', "%{$request->location}%");
            });
        }

        // Filter by verified
        if ($request->filled('verified') && $request->verified == '1') {
            $query->whereNotNull('verified_at');
        }

        // Sort
        $sort = $request->get('sort', 'newest');
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

        $models = $query->paginate(24);

        return view('models.browse', [
            'models' => $models,
            'filters' => $request->only(['search', 'gender', 'location', 'verified', 'sort']),
        ]);
    }
}

