<?php

namespace App\Http\Controllers;

use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortfolioAlbumController extends Controller
{
    /**
     * Display a listing of the user's galleries.
     */
    public function index(): View
    {
        $user = Auth::user();
        $albums = PortfolioAlbum::where('user_id', $user->id)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->with('coverImage')
            ->withCount('images')
            ->get();

        $stats = [
            ['label' => 'Galleries', 'value' => $albums->count(), 'class' => 'text-purple-600'],
            ['label' => 'Total Images', 'value' => $albums->sum('images_count'), 'class' => 'text-yellow-600'],
            ['label' => 'Public Images', 'value' => $albums->where('is_public', true)->sum('images_count'), 'class' => 'text-green-600'],
            ['label' => 'Featured Galleries', 'value' => 0, 'class' => 'text-blue-600'],
        ];

        $photographers = \App\Models\User::where('is_photographer', true)
            ->orderBy('name')
            ->get();
        $polaroids = PortfolioImage::where('model_id', $user->id)
            ->where('is_polaroid', true)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        return view('galleries.index', [
            'role' => 'model',
            'galleries' => $albums,
            'polaroids' => $polaroids,
            'stats' => $stats,
            'relatedEntities' => $photographers,
            'relatedField' => 'photographer_id',
            'relatedLabel' => 'Photographer (optional)',
            'uploadGalleryRequired' => true,
            'supportsPolaroids' => true,
            'polaroidCount' => $polaroids->count(),
            'polaroidLabelOptions' => [
                'front' => 'Front',
                'left_side' => 'Left Side',
                'right_side' => 'Right Side',
                'back' => 'Back',
                'full_body' => 'Full Body',
                'three_quarter' => 'Three Quarter',
                'close_up' => 'Close Up',
                'profile' => 'Profile',
            ],
        ]);
    }

    /**
     * Show the form for creating a new gallery.
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
     * Store a newly created gallery.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_id' => ['nullable', 'exists:portfolio_images,id'],
            'visibility' => ['required', 'in:public,link_only,hidden,custom'],
            'status' => ['required', 'in:draft,published'],
            'custom_visibility_users' => ['nullable', 'array'],
            'custom_visibility_users.*' => ['exists:users,id'],
            'contains_nudity' => ['boolean'],
        ]);

        // Verify cover image belongs to user
        if (!empty($validated['cover_image_id'])) {
            $coverImage = PortfolioImage::findOrFail($validated['cover_image_id']);
            if ($coverImage->model_id !== $user->id) {
                return back()->withErrors(['cover_image_id' => 'Cover image must be from your portfolio.']);
            }
        }

        $album = PortfolioAlbum::create([
            'user_id' => $user->id,
            'owner_role' => 'model',
            'name' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'cover_image_id' => $validated['cover_image_id'] ?? null,
            'contains_nudity' => $request->boolean('contains_nudity', false),
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users'])
                ? $validated['custom_visibility_users']
                : null,
            'is_public' => $validated['visibility'] === 'public',
            'display_order' => PortfolioAlbum::where('user_id', $user->id)->max('display_order') + 1,
        ]);

        return redirect()->route('portfolio.galleries.show', ['id' => $album->id, 'upload' => 1])
            ->with('status', 'Gallery created. You can add images below.');
    }

    /**
     * Display the specified gallery.
     */
    public function show(Request $request, string $id): View
    {
        $user = Auth::user();
        $album = PortfolioAlbum::findOrFail($id);

        $isOwner = $user && $album->user_id === $user->id;

        $album->load(['images' => function($query) use ($isOwner) {
            if (!$isOwner) {
                $query->where('is_public', true);
            }

            $query->orderBy('display_order')
                  ->orderBy('created_at', 'desc');
        }]);

        if (!$this->canViewAlbum($album, $user)) {
            abort(403);
        }

        // Age verification for nudity
        if ($album->contains_nudity) {
            // Check session for this specific album
            if (!$request->session()->get("age_verified_{$album->id}") && (!$user || !$this->isAgeVerified($user))) {
                return view('albums.age-verification', ['album' => $album]);
            }
        }

        $photographers = $isOwner
            ? \App\Models\User::where('is_photographer', true)->orderBy('name')->get()
            : collect();

        $credits = $isOwner
            ? PortfolioCredit::where('owner_user_id', $user->id)
                ->where(function ($query) use ($album) {
                    $query
                        ->where(function ($albumQuery) use ($album) {
                            $albumQuery->where('creditable_type', PortfolioAlbum::class)
                                ->where('creditable_id', $album->id);
                        })
                        ->orWhere(function ($imageQuery) use ($album) {
                            $imageQuery->where('creditable_type', PortfolioImage::class)
                                ->whereIn('creditable_id', $album->images->pluck('id'));
                        });
                })
                ->with('creditedUser')
                ->get()
            : collect();

        return view('galleries.show', [
            'role' => 'model',
            'gallery' => $album,
            'ownerId' => $album->user_id,
            'relatedEntities' => $photographers,
            'relatedField' => 'photographer_id',
            'relatedLabel' => 'Photographer (optional)',
            'supportsPolaroids' => true,
            'credits' => $credits,
        ]);
    }

    /**
     * Show the form for editing the specified gallery.
     */
    public function edit(string $id): View
    {
        $album = PortfolioAlbum::findOrFail($id);
        $user = Auth::user();

        if ($album->user_id !== $user->id) {
            abort(403);
        }

        $images = PortfolioImage::where('model_id', $user->id)
            ->where('album_id', $album->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('albums.edit', [
            'album' => $album,
            'images' => $images,
        ]);
    }

    /**
     * Update the specified gallery.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $album = PortfolioAlbum::findOrFail($id);
        $user = Auth::user();

        if ($album->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_id' => ['nullable', 'exists:portfolio_images,id'],
            'visibility' => ['required', 'in:public,link_only,hidden,custom'],
            'status' => ['required', 'in:draft,published'],
            'custom_visibility_users' => ['nullable', 'array'],
            'custom_visibility_users.*' => ['exists:users,id'],
            'contains_nudity' => ['boolean'],
        ]);

        // Verify cover image belongs to user
        if (!empty($validated['cover_image_id'])) {
            $coverImage = PortfolioImage::findOrFail($validated['cover_image_id']);
            if ($coverImage->model_id !== $user->id || (int) $coverImage->album_id !== (int) $album->id) {
                return back()->withErrors(['cover_image_id' => 'Cover image must already be in this gallery.']);
            }
        }

        $album->update([
            'name' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'cover_image_id' => $validated['cover_image_id'] ?? null,
            'contains_nudity' => $request->boolean('contains_nudity', false),
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users'])
                ? $validated['custom_visibility_users']
                : null,
            'is_public' => $validated['visibility'] === 'public',
        ]);

        return redirect()->route('portfolio.galleries.show', $album->id)
            ->with('status', 'Gallery updated successfully.');
    }

    /**
     * Remove the specified gallery.
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

        return redirect()->route('portfolio.galleries.index')
            ->with('status', 'Gallery deleted successfully.');
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

        return redirect()->route('portfolio.galleries.show', $album->id);
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

    private function canViewAlbum(PortfolioAlbum $album, $user): bool
    {
        if ($user && $album->user_id === $user->id) {
            return true;
        }

        $visibility = $album->visibility ?? ($album->is_public ? 'public' : 'hidden');

        return match ($visibility) {
            'public', 'link_only' => true,
            'custom' => $user && in_array($user->id, $album->custom_visibility_users ?? [], true),
            default => false,
        };
    }
}
