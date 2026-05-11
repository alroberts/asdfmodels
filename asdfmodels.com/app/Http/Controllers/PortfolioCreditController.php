<?php

namespace App\Http\Controllers;

use App\Models\PhotographerPortfolioImage;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use App\Models\SiteNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PortfolioCreditController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['model', 'photographer'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $search = ltrim(trim((string) ($validated['q'] ?? '')), '@');

        if (mb_strlen($search) < 2) {
            return response()->json(['users' => []]);
        }

        $query = User::query()
            ->where('is_admin', false)
            ->when($validated['role'] === 'model', function ($userQuery) {
                $userQuery
                    ->where('is_photographer', false)
                    ->whereHas('modelProfile');
            })
            ->when($validated['role'] === 'photographer', function ($userQuery) {
                $userQuery
                    ->where('is_photographer', true)
                    ->whereHas('photographerProfile');
            });

        $query->where(function ($searchQuery) use ($search) {
            $searchQuery->where('username', 'like', "%{$search}%");
        });

        $users = $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(20)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'label' => $user->display_name ?: $user->name,
                'username' => $user->username,
                'role' => $user->is_photographer ? 'photographer' : 'model',
            ]);

        return response()->json(['users' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credited_user_id' => ['required', 'integer', 'exists:users,id'],
            'credited_role' => ['required', Rule::in(['model', 'photographer'])],
            'gallery_id' => ['required', 'integer', 'exists:portfolio_albums,id'],
            'apply_to_gallery' => ['nullable', 'boolean'],
            'image_type' => ['required', Rule::in(['model', 'photographer'])],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['integer'],
        ]);

        $actor = Auth::user();
        $gallery = PortfolioAlbum::findOrFail($validated['gallery_id']);
        $this->authorizeGalleryOwner($gallery, $actor);
        $this->assertCreditedUserMatchesRole((int) $validated['credited_user_id'], $validated['credited_role']);

        $credits = collect();

        if ($request->boolean('apply_to_gallery')) {
            $credits->push($this->upsertCredit($gallery, $actor, $validated));
        }

        $images = $this->ownedImagesForGallery($gallery, $validated['image_type'], $validated['image_ids'] ?? []);
        foreach ($images as $image) {
            $credits->push($this->upsertCredit($image, $actor, $validated));
        }

        if ($credits->isEmpty()) {
            return response()->json([
                'message' => 'Choose the full gallery, one or more images, or both.',
            ], 422);
        }

        $credits->each(fn (PortfolioCredit $credit) => SiteNotification::notifyCredit($credit->loadMissing(['creditable', 'owner'])));

        return response()->json([
            'message' => 'Credit added. The tagged member can now choose whether to show it on their profile.',
            'credits' => $credits->values(),
        ]);
    }

    public function update(Request $request, PortfolioCredit $credit): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted_visible', 'accepted_hidden', 'rejected'])],
        ]);

        if ((int) $credit->credited_user_id !== (int) Auth::id()) {
            abort(403);
        }

        match ($validated['status']) {
            PortfolioCredit::STATUS_ACCEPTED_VISIBLE => $credit->acceptVisible(),
            PortfolioCredit::STATUS_ACCEPTED_HIDDEN => $credit->acceptHidden(),
            PortfolioCredit::STATUS_REJECTED => $credit->reject(),
        };

        SiteNotification::where('user_id', Auth::id())
            ->where('type', 'credit_pending')
            ->where('data->credit_id', $credit->id)
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => $validated['status'] === PortfolioCredit::STATUS_ACCEPTED_VISIBLE
                ? 'Credit accepted and shown on your profile.'
                : 'Credit preference saved.',
            'credit' => $credit->fresh(),
        ]);
    }

    public function destroy(PortfolioCredit $credit): JsonResponse
    {
        $userId = Auth::id();

        if ((int) $credit->owner_user_id !== (int) $userId && (int) $credit->credited_user_id !== (int) $userId) {
            abort(403);
        }

        $credit->delete();

        return response()->json(['message' => 'Credit removed.']);
    }

    public function requestTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_type' => ['required', Rule::in(['model', 'photographer'])],
            'image_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $actor = Auth::user();
        $image = $this->findImage($validated['image_type'], (int) $validated['image_id']);
        $ownerUserId = $image instanceof PhotographerPortfolioImage ? $image->photographer_id : $image->model_id;

        if ((int) $ownerUserId === (int) $actor->id) {
            return response()->json(['message' => 'You already own this image.'], 422);
        }

        $creditedRole = $actor->is_photographer ? 'photographer' : 'model';

        $credit = PortfolioCredit::updateOrCreate(
            [
                'creditable_type' => $image::class,
                'creditable_id' => $image->id,
                'credited_user_id' => $actor->id,
                'credited_role' => $creditedRole,
            ],
            [
                'owner_user_id' => $ownerUserId,
                'created_by_user_id' => $actor->id,
                'status' => PortfolioCredit::STATUS_PENDING,
                'source' => 'tag_request',
                'note' => $validated['note'] ?? null,
                'responded_at' => null,
            ]
        );

        SiteNotification::notifyCredit($credit->loadMissing(['creditable', 'owner']));

        return response()->json([
            'message' => 'Tag request sent.',
            'credit' => $credit,
        ]);
    }

    private function upsertCredit(Model $creditable, User $actor, array $validated): PortfolioCredit
    {
        return PortfolioCredit::updateOrCreate(
            [
                'creditable_type' => $creditable::class,
                'creditable_id' => $creditable->getKey(),
                'credited_user_id' => $validated['credited_user_id'],
                'credited_role' => $validated['credited_role'],
            ],
            [
                'owner_user_id' => $actor->id,
                'created_by_user_id' => $actor->id,
                'status' => PortfolioCredit::STATUS_PENDING,
                'source' => 'owner_tag',
                'responded_at' => null,
            ]
        );
    }

    private function authorizeGalleryOwner(PortfolioAlbum $gallery, User $actor): void
    {
        if ((int) $gallery->user_id !== (int) $actor->id) {
            abort(403);
        }
    }

    private function assertCreditedUserMatchesRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);

        if ($role === 'photographer' && !$user->is_photographer) {
            abort(422, 'Selected member is not a photographer.');
        }

        if ($role === 'model' && $user->is_photographer) {
            abort(422, 'Selected member is not a model.');
        }
    }

    private function ownedImagesForGallery(PortfolioAlbum $gallery, string $imageType, array $imageIds)
    {
        if (empty($imageIds)) {
            return collect();
        }

        $imageIds = collect($imageIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($imageType === 'photographer') {
            return PhotographerPortfolioImage::whereIn('id', $imageIds)
                ->where('photographer_id', $gallery->user_id)
                ->where('album_id', $gallery->id)
                ->get();
        }

        return PortfolioImage::whereIn('id', $imageIds)
            ->where('model_id', $gallery->user_id)
            ->where('album_id', $gallery->id)
            ->get();
    }

    private function findImage(string $imageType, int $imageId): PortfolioImage|PhotographerPortfolioImage
    {
        if ($imageType === 'photographer') {
            return PhotographerPortfolioImage::findOrFail($imageId);
        }

        return PortfolioImage::findOrFail($imageId);
    }
}
