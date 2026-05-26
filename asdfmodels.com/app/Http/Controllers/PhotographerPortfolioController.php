<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PortfolioAlbum;
use App\Services\PortfolioCleanupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $galleries = PortfolioAlbum::where('user_id', $user->id)
            ->select('portfolio_albums.*')
            ->selectSub(function ($query) use ($user) {
                $query->from('photographer_portfolio_images')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('photographer_portfolio_images.album_id', 'portfolio_albums.id')
                    ->where('photographer_portfolio_images.photographer_id', $user->id);
            }, 'images_count')
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

        $stats = [
            ['label' => 'Galleries', 'value' => $galleries->count(), 'class' => 'text-purple-600'],
            ['label' => 'Total Images', 'value' => $galleries->sum('images_count'), 'class' => 'text-yellow-600'],
            ['label' => 'Public Images', 'value' => $galleries->where('is_public', true)->sum('images_count'), 'class' => 'text-green-600'],
            ['label' => 'Featured Galleries', 'value' => $galleries->where('is_featured', true)->count(), 'class' => 'text-blue-600'],
        ];

        return view('galleries.index', [
            'role' => 'photographer',
            'galleries' => $galleries,
            'stats' => $stats,
            'relatedEntities' => $models,
            'relatedField' => 'model_id',
            'relatedLabel' => 'Model in Photo (optional)',
            'uploadGalleryRequired' => true,
            'supportsPolaroids' => false,
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
            'gallery_id' => ['required', 'exists:portfolio_albums,id'],
        ]);

        $gallery = PortfolioAlbum::where('user_id', $user->id)
            ->findOrFail($validated['gallery_id']);

        $uploadedCount = 0;
        $firstUploadedImage = null;
        $userFolder = public_path("uploads/photographers/{$user->id}/portfolio");

        // Create directories if they don't exist.
        $directories = ['resized', 'medium', 'thumbnails'];
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

            // Create medium version to keep downstream views and database assumptions consistent.
            $mediumImage = $manager->read($resizedPath);
            $mediumWidth = $mediumImage->width();
            $mediumHeight = $mediumImage->height();
            $mediumLongestEdge = max($mediumWidth, $mediumHeight);
            $mediumTargetSize = min((int) $maxSize, 1200);

            if ($mediumLongestEdge > $mediumTargetSize) {
                if ($mediumWidth > $mediumHeight) {
                    $mediumImage->scale(width: $mediumTargetSize);
                } else {
                    $mediumImage->scale(height: $mediumTargetSize);
                }
            }

            $mediumPath = "{$userFolder}/medium/{$filename}";
            $mediumImage->save($mediumPath, quality: 85);

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
                'album_id' => $gallery->id,
                'original_path' => null, // No longer storing original
                'thumbnail_path' => "uploads/photographers/{$user->id}/portfolio/thumbnails/{$filename}",
                'medium_path' => "uploads/photographers/{$user->id}/portfolio/medium/{$filename}",
                'full_path' => "uploads/photographers/{$user->id}/portfolio/resized/{$filename}", // Store resized version here
                'contains_nudity' => $request->boolean('contains_nudity', false),
                'is_public' => $request->boolean('is_public', true),
                'is_featured' => $request->boolean('is_featured', false),
                'category' => $validated['category'] ?? null,
                'display_order' => $displayOrder,
            ]);

            $uploadedImages[] = [
                'id' => $imageRecord->id,
                'full_path' => $imageRecord->full_path,
                'thumbnail_path' => $imageRecord->thumbnail_path,
            ];

            if ($firstUploadedImage === null) {
                $firstUploadedImage = $imageRecord;
            }

            $uploadedCount++;
        }

        if ($firstUploadedImage && empty($gallery->cover_image_path)) {
            $gallery->update(['cover_image_path' => $firstUploadedImage->full_path]);
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

        return redirect()->route('portfolio.index')
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
            'category' => ['nullable', 'string', 'max:100'],
            'model_id' => ['nullable', 'exists:users,id'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_public' => ['boolean'],
            'contains_nudity' => ['boolean'],
            'is_cover' => ['boolean'],
            'shot_date' => ['nullable', 'date'],
        ]);

        $updateData = [
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'contains_nudity' => $validated['contains_nudity'] ?? false,
            'shot_date' => $validated['shot_date'] ?? null,
        ];

        if ($request->exists('category')) {
            $updateData['category'] = $validated['category'] ?? null;
        }

        if ($request->exists('model_id')) {
            $updateData['model_id'] = $validated['model_id'] ?? null;
        }

        if ($request->exists('display_order')) {
            $updateData['display_order'] = $validated['display_order'] ?? $image->display_order;
        }

        if ($request->exists('is_featured')) {
            $updateData['is_featured'] = $request->boolean('is_featured');
        }

        if ($request->exists('is_public')) {
            $updateData['is_public'] = $request->boolean('is_public');
        }

        $image->update($updateData);

        // Handle cover image - need gallery_id from request
        $galleryId = $request->input('gallery_id');
        if ($galleryId) {
            $gallery = PortfolioAlbum::where('user_id', $user->id)
                ->find($galleryId);
            
            if ($gallery) {
                if ($validated['is_cover'] ?? false) {
                    $gallery->update(['cover_image_path' => $image->full_path]);
                } else {
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

        return redirect()->route('portfolio.index')
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

        PortfolioCleanupService::deleteImageReferences(PhotographerPortfolioImage::class, $image->id, $image->album_id);

        // Delete files (only resized and thumbnail)
        $files = [
            $image->thumbnail_path,
            $image->medium_path,
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

        PortfolioAlbum::where('user_id', $user->id)
            ->where('cover_image_path', $image->full_path)
            ->update(['cover_image_path' => null]);

        $image->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return redirect()->route('portfolio.index')
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
            'gallery_id' => ['required', 'integer', 'exists:portfolio_albums,id'],
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['required', 'integer', 'exists:photographer_portfolio_images,id'],
        ]);

        $gallery = PortfolioAlbum::where('user_id', $user->id)
            ->findOrFail($validated['gallery_id']);

        foreach ($validated['image_ids'] as $index => $imageId) {
            PhotographerPortfolioImage::where('id', $imageId)
                ->where('photographer_id', $user->id)
                ->where('album_id', $gallery->id)
                ->update(['display_order' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Display order updated successfully.']);
    }
}
