<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PhotographerProfileController extends Controller
{
    /**
     * Display a photographer's profile.
     */
    public function show(string $id): View
    {
        $photographer = User::with(['images' => function($query) {
            $query->where('is_public', true)
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        if (!$photographer->is_photographer) {
            abort(404);
        }

        // Get images where this photographer is tagged
        $taggedImages = \App\Models\PortfolioImage::whereHas('photographerTags', function($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })
        ->where('is_public', true)
        ->with('model')
        ->orderBy('created_at', 'desc')
        ->limit(12)
        ->get();

        return view('photographers.show', [
            'photographer' => $photographer,
            'taggedImages' => $taggedImages,
        ]);
    }
}

