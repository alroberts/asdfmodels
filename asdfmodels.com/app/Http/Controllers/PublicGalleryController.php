<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use App\Models\PortfolioImageComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicGalleryController extends Controller
{
    public function show(Request $request, PortfolioAlbum $gallery): View
    {
        $viewer = Auth::user();

        if (!$this->canViewGallery($gallery, $viewer)) {
            abort(404);
        }

        if ($gallery->contains_nudity && !$this->isAgeVerified($viewer, $request)) {
            return view('albums.age-verification', [
                'album' => $gallery,
                'action' => route('public.galleries.verify-age', $gallery->id),
            ]);
        }

        $owner = $gallery->user()->with(['modelProfile', 'photographerProfile'])->firstOrFail();
        $images = $gallery->owner_role === 'photographer'
            ? PhotographerPortfolioImage::where('photographer_id', $gallery->user_id)
                ->where('album_id', $gallery->id)
                ->where('is_public', true)
                ->with(['credits.creditedUser', 'comments.user'])
                ->orderBy('display_order')
                ->orderByDesc('created_at')
                ->get()
            : PortfolioImage::where('model_id', $gallery->user_id)
                ->where('album_id', $gallery->id)
                ->where('is_public', true)
                ->with(['credits.creditedUser', 'comments.user'])
                ->orderBy('display_order')
                ->orderByDesc('created_at')
                ->get();

        return view('galleries.public-show', [
            'gallery' => $gallery,
            'owner' => $owner,
            'images' => $images,
            'galleryCredits' => PortfolioCredit::where('status', PortfolioCredit::STATUS_ACCEPTED_VISIBLE)
                ->where('creditable_type', PortfolioAlbum::class)
                ->where('creditable_id', $gallery->id)
                ->with('creditedUser')
                ->get(),
            'ownerProfile' => $owner->is_photographer ? $owner->photographerProfile : $owner->modelProfile,
            'ownerProfileRoute' => $owner->is_photographer
                ? route('photographers.show', $owner->id)
                : route('models.show', $owner->id),
        ]);
    }

    public function verifyAge(Request $request, PortfolioAlbum $gallery): RedirectResponse
    {
        $request->validate([
            'age_verified' => ['required', 'accepted'],
        ]);

        $request->session()->put("age_verified_{$gallery->id}", true);
        $request->session()->put('age_verified_at', now());

        return redirect()->route('public.galleries.show', $gallery->id);
    }

    public function comment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_type' => ['required', Rule::in(['model', 'photographer'])],
            'image_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:1200'],
        ]);

        $image = $validated['image_type'] === 'photographer'
            ? PhotographerPortfolioImage::where('is_public', true)->findOrFail($validated['image_id'])
            : PortfolioImage::where('is_public', true)->findOrFail($validated['image_id']);

        $comment = PortfolioImageComment::create([
            'imageable_type' => $image::class,
            'imageable_id' => $image->id,
            'user_id' => Auth::id(),
            'body' => trim($validated['body']),
        ])->load('user');

        return response()->json([
            'message' => 'Comment added.',
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => $comment->user?->display_name ?: $comment->user?->name,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    private function canViewGallery(PortfolioAlbum $gallery, $viewer): bool
    {
        if ($viewer && (int) $gallery->user_id === (int) $viewer->id) {
            return true;
        }

        $visibility = $gallery->visibility ?? ($gallery->is_public ? 'public' : 'hidden');

        return match ($visibility) {
            'public', 'link_only' => true,
            'custom' => $viewer && in_array($viewer->id, $gallery->custom_visibility_users ?? [], true),
            default => false,
        };
    }

    private function isAgeVerified($viewer, Request $request): bool
    {
        if ($request->session()->get('age_verified_at')) {
            $verifiedAt = $request->session()->get('age_verified_at');

            if (now()->diffInHours($verifiedAt) < 24) {
                return true;
            }
        }

        if ($viewer && $viewer->modelProfile && $viewer->modelProfile->date_of_birth) {
            return $viewer->modelProfile->date_of_birth->age >= 18;
        }

        return false;
    }
}
