<?php

namespace App\Http\Controllers;

use App\Models\PhotographerGallery;
use App\Models\PhotographerPortfolioImage;
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
    public function store(Request $request): RedirectResponse
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
        $maxOrder = PhotographerGallery::where('photographer_id', $user->id)->max('display_order') ?? 0;

        $gallery = PhotographerGallery::create([
            'photographer_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'display_order' => $maxOrder + 1,
            'contains_nudity' => isset($validated['contains_nudity']) && $validated['contains_nudity'] == '1',
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users']) 
                ? $validated['custom_visibility_users'] 
                : null,
            // Keep legacy fields for now (can be removed later)
            'is_featured' => false,
            'is_public' => $validated['visibility'] === 'public',
        ]);

        return redirect()->route('photographers.portfolio.index')
            ->with('status', 'Gallery created successfully!');
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

        $gallery = PhotographerGallery::where('photographer_id', $user->id)
            ->with('images')
            ->findOrFail($id);

        return view('photographers.portfolio.galleries.show', [
            'gallery' => $gallery,
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

        $gallery = PhotographerGallery::where('photographer_id', $user->id)
            ->with('images')
            ->findOrFail($id);

        $allImages = PhotographerPortfolioImage::where('photographer_id', $user->id)
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
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can update galleries.');
        }

        $gallery = PhotographerGallery::where('photographer_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contains_nudity' => ['nullable'],
            'visibility' => ['required', 'in:public,link_only,hidden,custom'],
            'status' => ['required', 'in:draft,published'],
            'custom_visibility_users' => ['nullable', 'array'],
            'custom_visibility_users.*' => ['exists:users,id'],
        ]);

        $gallery->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'contains_nudity' => isset($validated['contains_nudity']) && $validated['contains_nudity'] == '1',
            'visibility' => $validated['visibility'],
            'status' => $validated['status'],
            'custom_visibility_users' => $validated['visibility'] === 'custom' && !empty($validated['custom_visibility_users']) 
                ? $validated['custom_visibility_users'] 
                : null,
            // Keep legacy fields for now (can be removed later)
            'is_public' => $validated['visibility'] === 'public',
        ]);

        return redirect()->route('photographers.portfolio.index')
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

        $gallery = PhotographerGallery::where('photographer_id', $user->id)
            ->findOrFail($id);

        // Detach all images (images themselves are not deleted)
        $gallery->images()->detach();

        $gallery->delete();

        return redirect()->route('photographers.portfolio.index')
            ->with('status', 'Gallery deleted successfully!');
    }
}

