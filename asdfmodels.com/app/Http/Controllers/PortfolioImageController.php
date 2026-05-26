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
        $baseQuery = PortfolioImage::where('model_id', $user->id);

        $polaroids = (clone $baseQuery)
            ->where('is_polaroid', true)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $galleries = \App\Models\PortfolioAlbum::where('user_id', $user->id)
            ->with('coverImage')
            ->withCount('images')
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'galleries' => $galleries->count(),
            'images' => (clone $baseQuery)->count(),
            'public_images' => (clone $baseQuery)->where('is_public', true)->count(),
            'featured_images' => (clone $baseQuery)->where('is_featured', true)->count(),
            'polaroids' => $polaroids->count(),
        ];

        $photographers = \App\Models\User::where('is_photographer', true)
            ->orderBy('name')
            ->get();

        $selectedGalleryId = request()->integer('gallery');
        if ($selectedGalleryId && !$galleries->contains('id', $selectedGalleryId)) {
            $selectedGalleryId = null;
        }

        return view('galleries.index', [
            'role' => 'model',
            'galleries' => $galleries,
            'polaroids' => $polaroids,
            'stats' => [
                ['label' => 'Galleries', 'value' => $stats['galleries'], 'class' => 'text-purple-600'],
                ['label' => 'Total Images', 'value' => $stats['images'], 'class' => 'text-yellow-600'],
                ['label' => 'Public Images', 'value' => $stats['public_images'], 'class' => 'text-green-600'],
                ['label' => 'Featured Images', 'value' => $stats['featured_images'], 'class' => 'text-blue-600'],
                ['label' => 'Polaroids', 'value' => $stats['polaroids'], 'class' => 'text-amber-600'],
            ],
            'relatedEntities' => $photographers,
            'relatedField' => 'photographer_id',
            'relatedLabel' => 'Photographer (optional)',
            'uploadGalleryRequired' => true,
            'supportsPolaroids' => true,
            'polaroidCount' => $stats['polaroids'],
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
            'initialUploadIntent' => request()->query('upload'),
            'selectedGalleryId' => $selectedGalleryId,
        ]);
    }

    /**
     * Show the form for uploading new images.
     */
    public function create(): View
    {
        $user = Auth::user();
        $galleries = \App\Models\PortfolioAlbum::where('user_id', $user->id)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedGalleryId = request()->integer('gallery');
        if ($selectedGalleryId && !$galleries->contains('id', $selectedGalleryId)) {
            $selectedGalleryId = null;
        }

        $photographers = \App\Models\User::where('is_photographer', true)
            ->orderBy('name')
            ->get();

        return view('portfolio.upload', [
            'galleries' => $galleries,
            'selectedGalleryId' => $selectedGalleryId,
            'photographers' => $photographers,
        ]);
    }

    /**
     * Store newly uploaded images.
     */
    public function store(Request $request)
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
            'album_id' => ['nullable', 'exists:portfolio_albums,id'],
        ]);

        $isPolaroidUpload = $request->boolean('is_polaroid', false);

        if ($isPolaroidUpload) {
            $validated['album_id'] = null;
            $validated['category'] = null;
            $validated['photographer_id'] = null;
            $validated['is_public'] = true;
        }

        if (!empty($validated['album_id'])) {
            $album = \App\Models\PortfolioAlbum::findOrFail($validated['album_id']);
            if ($album->user_id !== $user->id) {
                return back()->withErrors(['album_id' => 'Gallery must be yours.'])->withInput();
            }
        }

        $uploadedCount = 0;
        $firstUploadedImage = null;
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
                'album_id' => $validated['album_id'] ?? null,
                'original_path' => "uploads/models/{$user->id}/portfolio/original/{$filename}",
                'thumbnail_path' => "uploads/models/{$user->id}/portfolio/thumbnails/{$filename}",
                'medium_path' => "uploads/models/{$user->id}/portfolio/medium/{$filename}",
                'full_path' => "uploads/models/{$user->id}/portfolio/full/{$filename}",
                'is_polaroid' => $isPolaroidUpload,
                'contains_nudity' => $isPolaroidUpload ? false : $request->boolean('contains_nudity', false),
                'is_public' => $isPolaroidUpload ? true : $request->boolean('is_public', true),
                'category' => $validated['category'] ?? null,
                'display_order' => PortfolioImage::where('model_id', $user->id)->max('display_order') + 1,
            ]);

            if ($firstUploadedImage === null) {
                $firstUploadedImage = $image;
            }

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

        if (!empty($validated['album_id']) && $firstUploadedImage && empty($album->cover_image_id)) {
            $album->update(['cover_image_id' => $firstUploadedImage->id]);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            $uploadedImages = PortfolioImage::where('model_id', $user->id)
                ->latest('id')
                ->take($uploadedCount)
                ->get()
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'full_path' => $image->full_path,
                    'thumbnail_path' => $image->thumbnail_path,
                ])
                ->values();

            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded {$uploadedCount} image(s).",
                'count' => $uploadedCount,
                'images' => $uploadedImages,
            ]);
        }

        if (!empty($validated['album_id'])) {
            return redirect()->route('portfolio.galleries.show', $validated['album_id'])
                ->with('status', "Successfully uploaded {$uploadedCount} image(s) to your gallery.");
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
    public function update(Request $request, string $id)
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
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'is_featured' => ['boolean'],
            'is_polaroid' => ['boolean'],
            'contains_nudity' => ['boolean'],
            'is_public' => ['boolean'],
            'is_cover' => ['boolean'],
            'shot_date' => ['nullable', 'date'],
        ]);

        // Verify album belongs to user
        if (!empty($validated['album_id'])) {
            $album = \App\Models\PortfolioAlbum::findOrFail($validated['album_id']);
            if ($album->user_id !== $user->id) {
                return back()->withErrors(['album_id' => 'Album must be yours.']);
            }
        }

        $updateData = collect($validated)->except('is_cover')->all();
        if ($request->exists('is_polaroid') && $request->boolean('is_polaroid')) {
            $updateData['is_public'] = true;
            $updateData['contains_nudity'] = false;
        }
        $image->update($updateData);

        if (!empty($validated['album_id']) && $request->exists('is_cover')) {
            $album = \App\Models\PortfolioAlbum::where('user_id', $user->id)
                ->find($validated['album_id']);

            if ($album) {
                if ($request->boolean('is_cover')) {
                    $album->update(['cover_image_id' => $image->id]);
                } elseif ((int) $album->cover_image_id === (int) $image->id) {
                    $album->update(['cover_image_id' => null]);
                }
            }
        }

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

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image updated successfully.']);
        }

        return redirect()->route('portfolio.index')
            ->with('status', 'Image updated successfully.');
    }

    public function updatePolaroidLabels(Request $request)
    {
        $user = Auth::user();

        if ($user->is_photographer) {
            abort(404);
        }

        $validated = $request->validate([
            'labels' => ['required', 'array'],
            'labels.*' => ['nullable', 'string', 'max:100'],
        ]);

        $allowedLabels = [
            'front',
            'left_side',
            'right_side',
            'back',
            'full_body',
            'three_quarter',
            'close_up',
            'profile',
        ];

        $ids = collect(array_keys($validated['labels']))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $images = PortfolioImage::where('model_id', $user->id)
            ->where('is_polaroid', true)
            ->whereIn('id', $ids)
            ->get();

        foreach ($images as $image) {
            $value = $validated['labels'][$image->id] ?? '';
            $image->tags = in_array($value, $allowedLabels, true) ? [$value] : [];
            $image->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Polaroid labels saved.',
            'updated' => $images->count(),
        ]);
    }

    /**
     * Remove the specified image.
     */
    public function destroy(string $id)
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

        \App\Models\PortfolioAlbum::where('user_id', $user->id)
            ->where('cover_image_id', $image->id)
            ->update(['cover_image_id' => null]);

        $image->delete();

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return redirect()->route('portfolio.index')
            ->with('status', 'Image deleted successfully.');
    }

    /**
     * Reorder images within a gallery.
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'gallery_id' => ['required', 'integer', 'exists:portfolio_albums,id'],
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['required', 'integer', 'exists:portfolio_images,id'],
        ]);

        $gallery = \App\Models\PortfolioAlbum::where('user_id', $user->id)
            ->findOrFail($validated['gallery_id']);

        foreach ($validated['image_ids'] as $index => $imageId) {
            PortfolioImage::where('id', $imageId)
                ->where('model_id', $user->id)
                ->where('album_id', $gallery->id)
                ->update(['display_order' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Display order updated successfully.']);
    }
}
