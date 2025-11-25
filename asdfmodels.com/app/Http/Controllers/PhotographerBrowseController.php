<?php

namespace App\Http\Controllers;

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
        $query = User::with(['photographerImages' => function($q) {
                $q->where('is_public', true)
                  ->orderBy('created_at', 'desc')
                  ->limit(1);
            }])
            ->where('is_photographer', true)
            ->where('is_admin', false);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Note: Location filtering can be added when User model has location fields

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

        $photographers = $query->paginate(24);

        return view('photographers.browse', [
            'photographers' => $photographers,
            'filters' => $request->only(['search', 'sort']),
        ]);
    }
}

