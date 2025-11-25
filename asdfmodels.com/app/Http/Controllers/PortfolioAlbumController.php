<?php

namespace App\Http\Controllers;

use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortfolioAlbumController extends Controller
{
    /**
     * Display a listing of the user's albums.
     */
    public function index(): View
    {
        $user = Auth::user();
        $albums = PortfolioAlbum::where('user_id', $user->id)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->with('coverImage')
            ->get();

        return view('albums.index', [
            'albums' => $albums,
        ]);
    }

    /**
     * Show the form for creating a new album.
     */
    public function create(): View
    {
        $user = Auth::user();
        $images = PortfolioImage::where('model_id', $user->id)
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('albums.create', [
            'images' => $images,
        ]);
    }

    /**
     * Store a newly created album.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_id' => ['nullable', 'exists:portfolio_images,id'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
        ]);

        // Verify cover image belongs to user
        if ($validated['cover_image_id']) {
            $coverImage = PortfolioImage::findOrFail($validated['cover_image_id']);
            if ($coverImage->model_id !== $user->id) {
                return back()->withErrors(['cover_image_id' => 'Cover image must be from your portfolio.']);
            }
        }

        $album = PortfolioAlbum::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'cover_image_id' => $validated['cover_image_id'] ?? null,
            'contains_nudity' => $request->boolean('contains_nudity', false),
            'is_public' => $request->boolean('is_public', true),
            'display_order' => PortfolioAlbum::where('user_id', $user->id)->max('display_order') + 1,
        ]);

        return redirect()->route('albums.show', $album->id)
            ->with('status', 'Album created successfully.');
    }

    /**
     * Display the specified album.
     */
    public function show(Request $request, string $id): View
    {
        $album = PortfolioAlbum::with(['images' => function($query) {
            $query->where('is_public', true)
                  ->orderBy('display_order')
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Check if user can view (public or owner)
        $user = Auth::user();
        if (!$album->is_public && (!$user || $album->user_id !== $user->id)) {
            abort(403);
        }

        // Age verification for nudity
        if ($album->contains_nudity) {
            // Check session for this specific album
            if (!$request->session()->get("age_verified_{$album->id}") && (!$user || !$this->isAgeVerified($user))) {
                return view('albums.age-verification', ['album' => $album]);
            }
        }

        return view('albums.show', [
            'album' => $album,
        ]);
    }

    /**
     * Show the form for editing the specified album.
     */
    public function edit(string $id): View
    {
        $album = PortfolioAlbum::findOrFail($id);
        $user = Auth::user();

        if ($album->user_id !== $user->id) {
            abort(403);
        }

        $images = PortfolioImage::where('model_id', $user->id)
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('albums.edit', [
            'album' => $album,
            'images' => $images,
        ]);
    }

    /**
     * Update the specified album.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $album = PortfolioAlbum::findOrFail($id);
        $user = Auth::user();

        if ($album->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_id' => ['nullable', 'exists:portfolio_images,id'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
        ]);

        // Verify cover image belongs to user
        if ($validated['cover_image_id']) {
            $coverImage = PortfolioImage::findOrFail($validated['cover_image_id']);
            if ($coverImage->model_id !== $user->id) {
                return back()->withErrors(['cover_image_id' => 'Cover image must be from your portfolio.']);
            }
        }

        $album->update($validated);

        return redirect()->route('albums.show', $album->id)
            ->with('status', 'Album updated successfully.');
    }

    /**
     * Remove the specified album.
     */
    public function destroy(string $id): RedirectResponse
    {
        $album = PortfolioAlbum::findOrFail($id);
        $user = Auth::user();

        if ($album->user_id !== $user->id) {
            abort(403);
        }

        // Remove images from album (don't delete images, just unlink)
        $album->images()->update(['album_id' => null]);

        $album->delete();

        return redirect()->route('albums.index')
            ->with('status', 'Album deleted successfully.');
    }

    /**
     * Handle age verification for nudity content.
     */
    public function verifyAge(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'age_verified' => ['required', 'accepted'],
        ]);

        $album = PortfolioAlbum::findOrFail($id);
        
        // Store verification in session (simple approach)
        $request->session()->put("age_verified_{$album->id}", true);
        $request->session()->put('age_verified_at', now());

        return redirect()->route('albums.show', $album->id);
    }

    /**
     * Check if user is age verified (18+).
     * This is a simple check - in production you might want a more robust system.
     */
    private function isAgeVerified($user): bool
    {
        // Check session first (for guest users)
        if (session('age_verified_at')) {
            $verifiedAt = session('age_verified_at');
            // Session verification valid for 24 hours
            if (now()->diffInHours($verifiedAt) < 24) {
                return true;
            }
        }

        // Check if user has model profile with date of birth
        if ($user && $user->modelProfile && $user->modelProfile->date_of_birth) {
            return $user->modelProfile->date_of_birth->age >= 18;
        }

        // For now, if no DOB, assume not verified
        // In production, you might want a separate age verification system
        return false;
    }
}

