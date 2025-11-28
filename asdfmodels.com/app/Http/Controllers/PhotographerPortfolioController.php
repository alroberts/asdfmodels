<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PhotographerGallery;
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

        $galleries = PhotographerGallery::where('photographer_id', $user->id)
            ->withCount('images')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get images not in any gallery for "Uncategorized" section
        // Use a subquery to find images that don't have any gallery associations
        $uncategorizedImages = PhotographerPortfolioImage::where('photographer_id', $user->id)
            ->whereDoesntHave('galleries')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return view('photographers.portfolio.index', [
            'galleries' => $galleries,
            'uncategorizedImages' => $uncategorizedImages,
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
    public function store(Request $request): RedirectResponse
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
        ]);

        $uploadedCount = 0;
        $userFolder = public_path("uploads/photographers/{$user->id}/portfolio");

        // Create directories if they don't exist
        $directories = ['original', 'full', 'medium', 'thumbnails'];
        foreach ($directories as $dir) {
            $path = "{$userFolder}/{$dir}";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Get max image size from settings
        $maxSize = \App\Models\Setting::getValue('max_image_size', 2100);

        foreach ($request->file('images') as $file) {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store original
            $originalPath = "{$userFolder}/original/{$filename}";
            $file->move("{$userFolder}/original", $filename);

            // Process image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($originalPath);
            
            // Get dimensions
            $width = $image->width();
            $height = $image->height();
            $longestEdge = max($width, $height);

            // Resize if needed (max from settings)
            if ($longestEdge > $maxSize) {
                if ($width > $height) {
                    $image->scale(width: $maxSize);
                } else {
                    $image->scale(height: $maxSize);
                }
            }

            // Save full size
            $fullPath = "{$userFolder}/full/{$filename}";
            $image->save($fullPath, quality: 90);

            // Create medium (800px)
            $mediumImage = $manager->read($originalPath);
            if ($longestEdge > 800) {
                if ($width > $height) {
                    $mediumImage->scale(width: 800);
                } else {
                    $mediumImage->scale(height: 800);
                }
            }
            $mediumPath = "{$userFolder}/medium/{$filename}";
            $mediumImage->save($mediumPath, quality: 85);

            // Create thumbnail (300px)
            $thumbImage = $manager->read($originalPath);
            if ($longestEdge > 300) {
                if ($width > $height) {
                    $thumbImage->scale(width: 300);
                } else {
                    $thumbImage->scale(height: 300);
                }
            }
            $thumbPath = "{$userFolder}/thumbnails/{$filename}";
            $thumbImage->save($thumbPath, quality: 80);

            // Create database record
            $image = PhotographerPortfolioImage::create([
                'photographer_id' => $user->id,
                'model_id' => $validated['model_id'] ?? null,
                'original_path' => "uploads/photographers/{$user->id}/portfolio/original/{$filename}",
                'thumbnail_path' => "uploads/photographers/{$user->id}/portfolio/thumbnails/{$filename}",
                'medium_path' => "uploads/photographers/{$user->id}/portfolio/medium/{$filename}",
                'full_path' => "uploads/photographers/{$user->id}/portfolio/full/{$filename}",
                'contains_nudity' => $request->boolean('contains_nudity', false),
                'is_public' => $request->boolean('is_public', true),
                'is_featured' => $request->boolean('is_featured', false),
                'category' => $validated['category'] ?? null,
                'display_order' => PhotographerPortfolioImage::where('photographer_id', $user->id)->max('display_order') + 1,
            ]);

            $uploadedCount++;
        }

        return redirect()->route('photographers.portfolio.index')
            ->with('status', "Successfully uploaded {$uploadedCount} image(s).");
    }

    /**
     * Show the form for editing the specified image.
     */
    public function edit(string $id): View
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
            'category' => ['nullable', 'string', 'max:100'],
            'model_id' => ['nullable', 'exists:users,id'],
            'is_featured' => ['boolean'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
            'shot_date' => ['nullable', 'date'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $image->update($validated);

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

        // Delete files
        $files = [
            $image->original_path,
            $image->thumbnail_path,
            $image->medium_path,
            $image->full_path,
        ];

        foreach ($files as $file) {
            $fullPath = public_path($file);
            if (file_exists($fullPath)) {
                unlink($fullPath);
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
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer', 'exists:photographer_portfolio_images,id'],
            'order.*.display_order' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['order'] as $item) {
            $image = PhotographerPortfolioImage::find($item['id']);
            if ($image && $image->photographer_id === $user->id) {
                $image->display_order = $item['display_order'];
                $image->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Display order updated successfully.']);
    }
}

