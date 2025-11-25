<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_photographer',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_email_code',
        'two_factor_email_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_email_code_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Get the encrypted two factor secret.
     */
    public function getTwoFactorSecretAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the encrypted two factor secret.
     */
    public function setTwoFactorSecretAttribute($value)
    {
        $this->attributes['two_factor_secret'] = $value ? encrypt($value) : null;
    }

    /**
     * Get the model profile for this user.
     */
    public function modelProfile()
    {
        return $this->hasOne(ModelProfile::class);
    }

    /**
     * Get portfolio images where this user is the model.
     */
    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class, 'model_id');
    }

    /**
     * Get portfolio images where this user is the photographer.
     */
    public function photographerImages()
    {
        return $this->hasMany(PortfolioImage::class, 'photographer_id');
    }
}
