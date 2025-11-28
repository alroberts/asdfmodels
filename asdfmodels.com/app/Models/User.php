<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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

    /**
     * Get the photographer profile for this user.
     */
    public function photographerProfile()
    {
        return $this->hasOne(PhotographerProfile::class);
    }

    /**
     * Get photographer portfolio images (own portfolio).
     */
    public function photographerPortfolioImages()
    {
        return $this->hasMany(PhotographerPortfolioImage::class, 'photographer_id');
    }

    /**
     * Send the email verification notification.
     * Mail configuration is handled centrally in AppServiceProvider.
     */
    public function sendEmailVerificationNotification()
    {
        // Don't send verification emails to admins - they're auto-verified
        if ($this->is_admin) {
            return;
        }

        // Mail configuration is already set in AppServiceProvider boot()
        // All emails will use the configured mailer (SMTP, sendmail, etc.)
        try {
            $this->notify(new \App\Notifications\VerifyEmail);
        } catch (\Exception $e) {
            \Log::error('Failed to send email verification notification: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Send the password reset notification.
     * Uses unified mail configuration from database settings.
     */
    public function sendPasswordResetNotification($token)
    {
        // Mail configuration is already set in AppServiceProvider boot()
        // All emails will use the configured mailer (SMTP, sendmail, etc.)
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    /**
     * Determine if the user has verified their email address.
     * Admins are considered verified automatically.
     */
    public function hasVerifiedEmail(): bool
    {
        // Admins are always considered verified
        if ($this->is_admin) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the email address as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
}
