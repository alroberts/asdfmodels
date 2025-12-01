<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PhotographerGallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PhotographerPortfolioController extends Controller
{
    /**
     * Display a listing of the authenticated photographer's galleries.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can manage portfolios.');
        }

        $galleries = PhotographerGallery::where('photographer_id', $user->id)
            ->withCount('images')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get list of models for tagging (for upload modal)
        $models = \App\Models\User::where('is_photographer', false)
            ->where('is_admin', false)
            ->whereHas('modelProfile', function($q) {
                $q->where('is_public', true);
            })
            ->orderBy('name')
            ->get();

        return view('photographers.portfolio.index', [
            'galleries' => $galleries,
            'models' => $models,
        ]);
    }

    /**
     * Show the form for uploading new images.
     */
    public function create(): View
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can upload portfolio images.');
        }

        // Get list of models for tagging
        $models = \App\Models\User::where('is_photographer', false)
            ->where('is_admin', false)
            ->whereHas('modelProfile', function($q) {
                $q->where('is_public', true);
            })
            ->orderBy('name')
            ->get();

        return view('photographers.portfolio.upload', [
            'models' => $models,
        ]);
    }

    /**
     * Store newly uploaded images.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can upload portfolio images.');
        }

        $validated = $request->validate([
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:10240'], // 10MB max
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
            'is_featured' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'model_id' => ['nullable', 'exists:users,id'],
            'gallery_id' => ['required', 'exists:photographer_galleries,id'],
        ]);

        $uploadedCount = 0;
        $userFolder = public_path("uploads/photographers/{$user->id}/portfolio");

        // Create directories if they don't exist (only resized and thumbnails)
        $directories = ['resized', 'thumbnails'];
        foreach ($directories as $dir) {
            $path = "{$userFolder}/{$dir}";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Get max image size from settings
        $maxSize = \App\Models\Setting::getValue('max_image_size', 2100);
        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Process image directly from uploaded file
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            // Get dimensions
            $width = $image->width();
            $height = $image->height();
            $longestEdge = max($width, $height);

            // Resize to max size if needed (2100px on long edge from settings)
            if ($longestEdge > $maxSize) {
                if ($width > $height) {
                    $image->scale(width: $maxSize);
                } else {
                    $image->scale(height: $maxSize);
                }
            }

            // Save resized version (2100px on long edge)
            $resizedPath = "{$userFolder}/resized/{$filename}";
            $image->save($resizedPath, quality: 90);

            // Create thumbnail (300px)
            $thumbImage = $manager->read($resizedPath);
            $thumbWidth = $thumbImage->width();
            $thumbHeight = $thumbImage->height();
            $thumbLongestEdge = max($thumbWidth, $thumbHeight);
            
            if ($thumbLongestEdge > 300) {
                if ($thumbWidth > $thumbHeight) {
                    $thumbImage->scale(width: 300);
                } else {
                    $thumbImage->scale(height: 300);
                }
            }
            $thumbPath = "{$userFolder}/thumbnails/{$filename}";
            $thumbImage->save($thumbPath, quality: 80);

            // Get display order for gallery if provided
            $displayOrder = PhotographerPortfolioImage::where('photographer_id', $user->id)->max('display_order') ?? 0;
            $displayOrder++;

            // Create database record
            $imageRecord = PhotographerPortfolioImage::create([
                'photographer_id' => $user->id,
                'model_id' => $validated['model_id'] ?? null,
                'original_path' => null, // No longer storing original
                'thumbnail_path' => "uploads/photographers/{$user->id}/portfolio/thumbnails/{$filename}",
                'medium_path' => null, // No longer creating medium version
                'full_path' => "uploads/photographers/{$user->id}/portfolio/resized/{$filename}", // Store resized version here
                'contains_nudity' => $request->boolean('contains_nudity', false),
                'is_public' => $request->boolean('is_public', true),
                'is_featured' => $request->boolean('is_featured', false),
                'category' => $validated['category'] ?? null,
                'display_order' => $displayOrder,
            ]);

            // Associate with gallery (required)
            $gallery = PhotographerGallery::find($validated['gallery_id']);
            if ($gallery && $gallery->photographer_id === $user->id) {
                // Get the next display order for this gallery from pivot table
                $galleryDisplayOrder = DB::table('gallery_image')
                    ->where('gallery_id', $gallery->id)
                    ->max('display_order') ?? 0;
                
                $gallery->images()->attach($imageRecord->id, [
                    'display_order' => $galleryDisplayOrder + 1
                ]);
            }

            $uploadedImages[] = [
                'id' => $imageRecord->id,
                'full_path' => $imageRecord->full_path,
                'thumbnail_path' => $imageRecord->thumbnail_path,
            ];

            $uploadedCount++;
        }

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded {$uploadedCount} image(s).",
                'count' => $uploadedCount,
                'images' => $uploadedImages,
            ]);
        }

        return redirect()->route('photographers.portfolio.index')
            ->with('status', "Successfully uploaded {$uploadedCount} image(s).");
    }

    /**
     * Show the form for editing the specified image.
     */
    public function edit(Request $request, string $id)
    {
        $image = PhotographerPortfolioImage::findOrFail($id);
        $user = Auth::user();

        if ($image->photographer_id !== $user->id) {
            abort(403);
        }

        // Get list of models for tagging
        $models = \App\Models\User::where('is_photographer', false)
            ->where('is_admin', false)
            ->whereHas('modelProfile', function($q) {
                $q->where('is_public', true);
            })
            ->orderBy('name')
            ->get();

        // Return HTML view for both AJAX and regular requests
        // The JavaScript will parse the HTML to extract form data
        return view('photographers.portfolio.edit', [
            'image' => $image,
            'models' => $models,
        ]);
    }

    /**
     * Update the specified image.
     */
    public function update(Request $request, string $id)
    {
        $image = PhotographerPortfolioImage::findOrFail($id);
        $user = Auth::user();

        if ($image->photographer_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'contains_nudity' => ['boolean'],
            'is_cover' => ['boolean'],
            'shot_date' => ['nullable', 'date'],
        ]);

        $image->update([
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'contains_nudity' => $validated['contains_nudity'] ?? false,
            'shot_date' => $validated['shot_date'] ?? null,
        ]);

        // Handle cover image - need gallery_id from request
        $galleryId = $request->input('gallery_id');
        if ($galleryId) {
            $gallery = \App\Models\PhotographerGallery::where('photographer_id', $user->id)
                ->find($galleryId);
            
            if ($gallery) {
                if ($validated['is_cover'] ?? false) {
                    // Set this image as cover
                    $gallery->update(['cover_image_path' => $image->full_path]);
                } else {
                    // Check if this image is currently the cover and unset it
                    if ($gallery->cover_image_path === $image->full_path) {
                        $gallery->update(['cover_image_path' => null]);
                    }
                }
            }
        }

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image updated successfully.']);
        }

        return redirect()->route('photographers.portfolio.index')
            ->with('status', 'Image updated successfully.');
    }

    /**
     * Remove the specified image.
     */
    public function destroy(Request $request, string $id)
    {
        $image = PhotographerPortfolioImage::findOrFail($id);
        $user = Auth::user();

        // Check ownership
        if ($image->photographer_id !== $user->id) {
            abort(403);
        }

        // Delete files (only resized and thumbnail)
        $files = [
            $image->thumbnail_path,
            $image->full_path,
        ];

        foreach ($files as $file) {
            if ($file) {
                $fullPath = public_path($file);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        $image->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return redirect()->route('photographers.portfolio.index')
            ->with('status', 'Image deleted successfully.');
    }

    /**
     * Handle bulk actions on multiple images.
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can manage portfolios.');
        }

        $validated = $request->validate([
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['integer', 'exists:photographer_portfolio_images,id'],
            'action' => ['required', 'string', 'in:feature,public'],
            'value' => ['required', 'boolean'],
        ]);

        $images = PhotographerPortfolioImage::whereIn('id', $validated['image_ids'])
            ->where('photographer_id', $user->id)
            ->get();

        foreach ($images as $image) {
            if ($validated['action'] === 'feature') {
                $image->is_featured = $validated['value'];
            } elseif ($validated['action'] === 'public') {
                $image->is_public = $validated['value'];
            }
            $image->save();
        }

        return response()->json(['success' => true, 'message' => 'Images updated successfully.']);
    }

    /**
     * Update display order of images.
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_photographer) {
            abort(403, 'Only photographers can manage portfolios.');
        }

        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', 'exists:photographer_galleries,id'],
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['required', 'integer', 'exists:photographer_portfolio_images,id'],
        ]);

        // Get the gallery and verify ownership
        $gallery = \App\Models\PhotographerGallery::where('photographer_id', $user->id)
            ->findOrFail($validated['gallery_id']);

        // Update display_order in the pivot table based on the order of image_ids array
        foreach ($validated['image_ids'] as $index => $imageId) {
            $image = PhotographerPortfolioImage::find($imageId);
            if ($image && $image->photographer_id === $user->id) {
                // Update the pivot table's display_order
                $gallery->images()->updateExistingPivot($imageId, [
                    'display_order' => $index + 1
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Display order updated successfully.']);
    }
}

