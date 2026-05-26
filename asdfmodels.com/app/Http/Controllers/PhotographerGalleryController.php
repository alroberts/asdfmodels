<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PhotographerGalleryController extends Controller
{
    /**
     * Show the form for creating a new gallery.
     */
    public function create(): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can create galleries.');
        }

        return view('photographers.portfolio.galleries.create');
    }

    /**
     * Store a newly created gallery.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can create galleries.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contains_nudity' => ['nullable'],
            'visibility' => ['required', 'in:public,link_only,hidden,custom'],
            'status' => ['required', 'in:draft,published'],
            'custom_visibility_users' => ['nullable', 'array'],
            'custom_visibility_users.*' => ['exists:users,id'],
        ]);

        // Get the highest display_order
        $maxOrder = PortfolioAlbum::where('user_id', $user->id)->max('display_order') ?? 0;

        $gallery = PortfolioAlbum::create([
            'user_id' => $user->id,
            'owner_role' => 'photographer',
            'name' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'display_order' => $maxOrder + 1,
            'contains_nudity' => isset($validated['contains_nudity']) && $validated['contains_nudity'] == '1',
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users']) 
                ? $validated['custom_visibility_users'] 
                : null,
            'is_public' => $validated['visibility'] === 'public',
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery created.',
                'gallery_id' => $gallery->id,
                'redirect_url' => route('portfolio.galleries.show', $gallery->id),
            ]);
        }

        return redirect()->route('portfolio.galleries.show', $gallery->id)
            ->with('status', 'Gallery created. You can add images below.');
    }

    /**
     * Display the specified gallery.
     */
    public function show(string $id): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can view galleries.');
        }

        $gallery = PortfolioAlbum::where('user_id', $user->id)->findOrFail($id);
        $images = PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->where('album_id', $gallery->id)
            ->orderBy('display_order')
            ->orderBy('created_at')
            ->get();
        $gallery->setRelation('images', $images);

        $models = \App\Models\User::where('is_photographer', false)
            ->where('is_admin', false)
            ->whereHas('modelProfile', function($q) {
                $q->where('is_public', true);
            })
            ->orderBy('name')
            ->get();

        $credits = PortfolioCredit::where('owner_user_id', $user->id)
            ->where(function ($query) use ($gallery, $images) {
                $query
                    ->where(function ($albumQuery) use ($gallery) {
                        $albumQuery->where('creditable_type', PortfolioAlbum::class)
                            ->where('creditable_id', $gallery->id);
                    })
                    ->orWhere(function ($imageQuery) use ($images) {
                        $imageQuery->where('creditable_type', PhotographerPortfolioImage::class)
                            ->whereIn('creditable_id', $images->pluck('id'));
                    });
            })
            ->with('creditedUser')
            ->get();

        return view('galleries.show', [
            'role' => 'photographer',
            'gallery' => $gallery,
            'ownerId' => $user->id,
            'relatedEntities' => $models,
            'relatedField' => 'model_id',
            'relatedLabel' => 'Model in Photo (optional)',
            'supportsPolaroids' => false,
            'credits' => $credits,
        ]);
    }

    /**
     * Show the form for editing the specified gallery.
     */
    public function edit(string $id): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can edit galleries.');
        }

        $gallery = PortfolioAlbum::where('user_id', $user->id)->findOrFail($id);

        $allImages = PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->where('album_id', $gallery->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('photographers.portfolio.galleries.edit', [
            'gallery' => $gallery,
            'allImages' => $allImages,
        ]);
    }

    /**
     * Update the specified gallery.
     */
    public function update(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can update galleries.');
        }

        $gallery = PortfolioAlbum::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contains_nudity' => ['nullable'],
            'cover_image_id' => ['nullable', 'exists:photographer_portfolio_images,id'],
            'visibility' => ['required', 'in:public,link_only,hidden,custom'],
            'status' => ['required', 'in:draft,published'],
            'custom_visibility_users' => ['nullable', 'array'],
            'custom_visibility_users.*' => ['exists:users,id'],
        ]);

        if (!empty($validated['cover_image_id'])) {
            $coverImage = PhotographerPortfolioImage::where('photographer_id', $user->id)
                ->where('album_id', $gallery->id)
                ->findOrFail($validated['cover_image_id']);
        }

        $updateData = [
            'name' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'contains_nudity' => isset($validated['contains_nudity']) && $validated['contains_nudity'] == '1',
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users']) 
                ? $validated['custom_visibility_users'] 
                : null,
            'is_public' => $validated['visibility'] === 'public',
        ];

        if ($request->has('cover_image_id')) {
            $updateData['cover_image_path'] = !empty($validated['cover_image_id']) ? $coverImage->full_path : null;
        }

        $gallery->update($updateData);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery settings updated.',
                'gallery' => [
                    'title' => $gallery->name,
                    'description' => $gallery->description,
                    'visibility' => $gallery->visibility,
                    'status' => $gallery->status,
                    'contains_nudity' => $gallery->contains_nudity,
                ],
            ]);
        }

        return redirect()->route('portfolio.galleries.show', $gallery->id)
            ->with('status', 'Gallery updated successfully!');
    }

    /**
     * Remove the specified gallery.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can delete galleries.');
        }

        $gallery = PortfolioAlbum::where('user_id', $user->id)->findOrFail($id);

        PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->where('album_id', $gallery->id)
            ->update(['album_id' => null]);

        $gallery->delete();

        return redirect()->route('portfolio.galleries.index')
            ->with('status', 'Gallery deleted successfully!');
    }
}
