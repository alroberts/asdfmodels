<?php

namespace App\Http\Controllers;

use App\Models\PortfolioImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PortfolioImageController extends Controller
{
    /**
     * Display a listing of the authenticated user's portfolio images.
     */
    public function index(): View
    {
        $user = Auth::user();
        $images = PortfolioImage::where('model_id', $user->id)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $polaroids = PortfolioImage::where('model_id', $user->id)
            ->where('is_polaroid', true)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portfolio.index', [
            'images' => $images,
            'polaroids' => $polaroids,
        ]);
    }

    /**
     * Show the form for uploading new images.
     */
    public function create(): View
    {
        return view('portfolio.upload');
    }

    /**
     * Store newly uploaded images.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $maxSize = 2100; // Default, should be configurable from settings

        $validated = $request->validate([
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:10240'], // 10MB max
            'is_polaroid' => ['boolean'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'photographer_id' => ['nullable', 'exists:users,id'],
        ]);

        $uploadedCount = 0;
        $userFolder = public_path("uploads/models/{$user->id}/portfolio");

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
            $image = PortfolioImage::create([
                'model_id' => $user->id,
                'photographer_id' => $validated['photographer_id'] ?? null,
                'original_path' => "uploads/models/{$user->id}/portfolio/original/{$filename}",
                'thumbnail_path' => "uploads/models/{$user->id}/portfolio/thumbnails/{$filename}",
                'medium_path' => "uploads/models/{$user->id}/portfolio/medium/{$filename}",
                'full_path' => "uploads/models/{$user->id}/portfolio/full/{$filename}",
                'is_polaroid' => $request->boolean('is_polaroid', false),
                'contains_nudity' => $request->boolean('contains_nudity', false),
                'is_public' => $request->boolean('is_public', true),
                'category' => $validated['category'] ?? null,
                'display_order' => PortfolioImage::where('model_id', $user->id)->max('display_order') + 1,
            ]);

            // Create photographer tag if photographer_id provided
            if ($validated['photographer_id'] ?? null) {
                \App\Models\PhotographerImageTag::create([
                    'portfolio_image_id' => $image->id,
                    'photographer_id' => $validated['photographer_id'],
                    'model_id' => $user->id,
                    'role' => 'photographer', // Photographer tagged this image
                ]);
            }

            $uploadedCount++;
        }

        return redirect()->route('portfolio.index')
            ->with('status', "Successfully uploaded {$uploadedCount} image(s).");
    }

    /**
     * Show the form for editing the specified image.
     */
    public function edit(string $id): View
    {
        $image = PortfolioImage::findOrFail($id);
        $user = Auth::user();

        if ($image->model_id !== $user->id) {
            abort(403);
        }

        $albums = \App\Models\PortfolioAlbum::where('user_id', $user->id)->get();
        $photographers = \App\Models\User::where('is_photographer', true)->orderBy('name')->get();

        return view('portfolio.edit', [
            'image' => $image,
            'albums' => $albums,
            'photographers' => $photographers,
        ]);
    }

    /**
     * Update the specified image.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $image = PortfolioImage::findOrFail($id);
        $user = Auth::user();

        if ($image->model_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'album_id' => ['nullable', 'exists:portfolio_albums,id'],
            'photographer_id' => ['nullable', 'exists:users,id'],
            'is_featured' => ['boolean'],
            'is_polaroid' => ['boolean'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
            'shot_date' => ['nullable', 'date'],
        ]);

        // Verify album belongs to user
        if ($validated['album_id']) {
            $album = \App\Models\PortfolioAlbum::findOrFail($validated['album_id']);
            if ($album->user_id !== $user->id) {
                return back()->withErrors(['album_id' => 'Album must be yours.']);
            }
        }

        $image->update($validated);

        // Update photographer tag
        if ($validated['photographer_id'] ?? null) {
            // Remove existing tags for this image
            \App\Models\PhotographerImageTag::where('portfolio_image_id', $image->id)->delete();
            
            // Create new tag
            \App\Models\PhotographerImageTag::create([
                'portfolio_image_id' => $image->id,
                'photographer_id' => $validated['photographer_id'],
                'model_id' => $user->id,
                'role' => 'photographer',
            ]);
            
            $image->photographer_id = $validated['photographer_id'];
            $image->save();
        }

        return redirect()->route('portfolio.index')
            ->with('status', 'Image updated successfully.');
    }

    /**
     * Remove the specified image.
     */
    public function destroy(string $id): RedirectResponse
    {
        $image = PortfolioImage::findOrFail($id);
        $user = Auth::user();

        // Check ownership
        if ($image->model_id !== $user->id) {
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

        return redirect()->route('portfolio.index')
            ->with('status', 'Image deleted successfully.');
    }
}

