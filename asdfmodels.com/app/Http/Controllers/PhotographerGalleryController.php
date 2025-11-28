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

        $images = PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('photographers.portfolio.galleries.create', [
            'images' => $images,
        ]);
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
            'cover_image_id' => ['nullable', 'exists:photographer_portfolio_images,id'],
            'is_featured' => ['boolean'],
            'is_public' => ['boolean'],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['exists:photographer_portfolio_images,id'],
        ]);

        // Get the highest display_order
        $maxOrder = PhotographerGallery::where('photographer_id', $user->id)->max('display_order') ?? 0;

        $gallery = PhotographerGallery::create([
            'photographer_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'display_order' => $maxOrder + 1,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        // Set cover image if provided
        if (isset($validated['cover_image_id'])) {
            $coverImage = PhotographerPortfolioImage::find($validated['cover_image_id']);
            if ($coverImage && $coverImage->photographer_id === $user->id) {
                $gallery->cover_image_path = $coverImage->thumbnail_path;
                $gallery->save();
            }
        }

        // Attach images to gallery
        if (isset($validated['image_ids']) && count($validated['image_ids']) > 0) {
            $images = PhotographerPortfolioImage::whereIn('id', $validated['image_ids'])
                ->where('photographer_id', $user->id)
                ->get();
            
            foreach ($images as $index => $image) {
                $gallery->images()->attach($image->id, ['display_order' => $index]);
            }
        }

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
            'cover_image_id' => ['nullable', 'exists:photographer_portfolio_images,id'],
            'is_featured' => ['boolean'],
            'is_public' => ['boolean'],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['exists:photographer_portfolio_images,id'],
        ]);

        $gallery->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        // Update cover image if provided
        if (isset($validated['cover_image_id'])) {
            $coverImage = PhotographerPortfolioImage::find($validated['cover_image_id']);
            if ($coverImage && $coverImage->photographer_id === $user->id) {
                $gallery->cover_image_path = $coverImage->thumbnail_path;
                $gallery->save();
            }
        }

        // Sync images to gallery
        if (isset($validated['image_ids'])) {
            $images = PhotographerPortfolioImage::whereIn('id', $validated['image_ids'])
                ->where('photographer_id', $user->id)
                ->get();
            
            // Detach all current images
            $gallery->images()->detach();
            
            // Attach new images with display order
            foreach ($images as $index => $image) {
                $gallery->images()->attach($image->id, ['display_order' => $index]);
            }
        }

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

