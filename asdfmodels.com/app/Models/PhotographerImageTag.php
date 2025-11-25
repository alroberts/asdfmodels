<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerImageTag extends Model
{
    protected $fillable = [
        'portfolio_image_id',
        'photographer_id',
        'model_id',
        'role',
    ];

    /**
     * Get the portfolio image.
     */
    public function portfolioImage(): BelongsTo
    {
        return $this->belongsTo(PortfolioImage::class);
    }

    /**
     * Get the photographer.
     */
    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    /**
     * Get the model.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(User::class, 'model_id');
    }
}

