<?php

namespace App\Services;

use App\Models\PhotographerImageTag;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioCredit;
use App\Models\PortfolioImage;
use App\Models\PortfolioImageComment;
use App\Models\SiteNotification;
use Illuminate\Support\Collection;

class PortfolioCleanupService
{
    public static function deleteGalleryCreditsAndNotifications(PortfolioAlbum $gallery, string $imageModelClass, Collection $imageIds): void
    {
        $creditIds = PortfolioCredit::where(function ($query) use ($gallery, $imageModelClass, $imageIds) {
            $query->where(function ($galleryQuery) use ($gallery) {
                $galleryQuery->where('creditable_type', PortfolioAlbum::class)
                    ->where('creditable_id', $gallery->id);
            });

            if ($imageIds->isNotEmpty()) {
                $query->orWhere(function ($imageQuery) use ($imageModelClass, $imageIds) {
                    $imageQuery->where('creditable_type', $imageModelClass)
                        ->whereIn('creditable_id', $imageIds);
                });
            }
        })->pluck('id');

        self::deleteCreditNotifications($creditIds, $gallery->id);

        if ($creditIds->isNotEmpty()) {
            PortfolioCredit::whereIn('id', $creditIds)->delete();
        }
    }

    public static function deleteImageReferences(string $imageModelClass, int $imageId, ?int $galleryId = null): void
    {
        $creditIds = PortfolioCredit::where('creditable_type', $imageModelClass)
            ->where('creditable_id', $imageId)
            ->pluck('id');

        self::deleteCreditNotifications($creditIds, $galleryId);

        if ($creditIds->isNotEmpty()) {
            PortfolioCredit::whereIn('id', $creditIds)->delete();
        }

        PortfolioImageComment::where('imageable_type', $imageModelClass)
            ->where('imageable_id', $imageId)
            ->delete();

        if ($imageModelClass === PortfolioImage::class) {
            PhotographerImageTag::where('portfolio_image_id', $imageId)->delete();
        }
    }

    private static function deleteCreditNotifications(Collection $creditIds, ?int $galleryId = null): void
    {
        if ($creditIds->isEmpty() && !$galleryId) {
            return;
        }

        SiteNotification::where('type', 'credit_pending')
            ->where(function ($query) use ($creditIds, $galleryId) {
                if ($galleryId) {
                    $query->where('group_key', 'credit:gallery:' . $galleryId)
                        ->orWhere('data->gallery_id', $galleryId);
                }

                if ($creditIds->isNotEmpty()) {
                    $method = $galleryId ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('data->credit_id', $creditIds);
                }
            })
            ->delete();
    }
}
