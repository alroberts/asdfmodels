<?php

namespace App\Http\Controllers;

use App\Models\ModelProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ModelProfileController extends Controller
{
    /**
     * Display the specified model profile (public view).
     */
    public function show(string $id): View
    {
        $user = User::with('modelProfile')->findOrFail($id);

        if (!$user->modelProfile || !$user->modelProfile->is_public) {
            abort(404);
        }

        $profile = $user->modelProfile;
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

        return view('models.show', [
            'user' => $user,
            'profile' => $profile,
            'featuredImages' => $featuredImages,
            'polaroids' => $polaroids,
        ]);
    }

    /**
     * Show the form for editing the authenticated user's model profile.
     */
    public function edit(): View
    {
        $user = Auth::user();
        
        // Ensure user is not a photographer
        if ($user->is_photographer) {
            abort(403, 'Photographers cannot have model profiles.');
        }

        $profile = $user->modelProfile ?? new ModelProfile(['user_id' => $user->id]);

        return view('models.edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
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
            'bio' => ['nullable', 'string', 'max:2000'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_country' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            
            // Male fields
            'height' => ['nullable', 'string', 'max:50'],
            'weight' => ['nullable', 'string', 'max:50'],
            'chest' => ['nullable', 'string', 'max:50'],
            'waist' => ['nullable', 'string', 'max:50'],
            'inseam' => ['nullable', 'string', 'max:50'],
            'shoe_size' => ['nullable', 'string', 'max:50'],
            'suit_size' => ['nullable', 'string', 'max:50'],
            
            // Female fields
            'bust' => ['nullable', 'string', 'max:50'],
            'hips' => ['nullable', 'string', 'max:50'],
            'dress_size' => ['nullable', 'string', 'max:50'],
            
            // Common fields
            'hair_color' => ['nullable', 'string', 'max:50'],
            'eye_color' => ['nullable', 'string', 'max:50'],
            
            // Professional
            'experience_level' => ['nullable', 'string', 'max:50'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            
            // Contact
            'public_email' => ['nullable', 'email', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'portfolio_website' => ['nullable', 'url', 'max:255'],
            
            // Settings
            'is_public' => ['boolean'],
            'contains_nudity' => ['boolean'],
        ]);

        $profile = $user->modelProfile ?? new ModelProfile();
        $profile->user_id = $user->id;
        $profile->fill($validated);
        $profile->save();

        return redirect()->route('profile.model.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

