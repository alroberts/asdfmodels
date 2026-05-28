<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        // Get featured models (with featured images)
        $featuredModels = \App\Models\ModelProfile::with(['user'])
            ->where('is_public', true)
            ->whereHas('user', function($q) {
                $q->where('is_photographer', false)
                  ->where('is_admin', false);
            })
            ->whereHas('portfolioImages', function($query) {
                $query->where('is_featured', true)
                      ->where('is_public', true);
            })
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Get newest members
        $newestMembers = \App\Models\ModelProfile::with('user')
            ->where('is_public', true)
            ->whereHas('user', function($q) {
                $q->where('is_photographer', false)
                  ->where('is_admin', false);
            })
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
    } catch (\Exception $e) {
        // If there's an error (e.g., tables don't exist yet), return empty collections
        $featuredModels = collect([]);
        $newestMembers = collect([]);
    }

    return view('home', [
        'featuredModels' => $featuredModels,
        'newestMembers' => $newestMembers,
    ]);
})->name('home');

Route::get('/dashboard', [\App\Http\Controllers\FeedController::class, 'index'])
    ->middleware(['auth', 'verified', 'profile.complete'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes (accessible without profile completion - needed to complete profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Model Profile Management (accessible without profile completion)
    Route::get('/profile/model', [\App\Http\Controllers\ModelProfileController::class, 'edit'])->name('profile.model.edit');
    Route::patch('/profile/model', [\App\Http\Controllers\ModelProfileController::class, 'update'])->name('profile.model.update');
    Route::patch('/profile/model/bio', [\App\Http\Controllers\ModelProfileController::class, 'updateBio'])->name('profile.model.bio.update');
    Route::patch('/profile/model/measurements', [\App\Http\Controllers\ModelProfileController::class, 'updateMeasurements'])->name('profile.model.measurements.update');
    Route::patch('/profile/model/specialties', [\App\Http\Controllers\ModelProfileController::class, 'updateSpecialties'])->name('profile.model.specialties.update');
    Route::patch('/profile/model/media', [\App\Http\Controllers\ModelProfileController::class, 'updateMedia'])->name('profile.model.media.update');
    
    // Photographer Profile Management (accessible without profile completion)
    Route::get('/profile/photographer', [\App\Http\Controllers\PhotographerProfileController::class, 'edit'])->name('photographers.profile.edit');
    Route::patch('/profile/photographer', [\App\Http\Controllers\PhotographerProfileController::class, 'update'])->name('photographers.profile.update');
    Route::patch('/profile/photographer/bio', [\App\Http\Controllers\PhotographerProfileController::class, 'updateBio'])->name('photographers.profile.bio.update');
    Route::patch('/profile/photographer/professional', [\App\Http\Controllers\PhotographerProfileController::class, 'updateProfessionalQuick'])->name('photographers.profile.professional.update');
    Route::patch('/profile/photographer/media', [\App\Http\Controllers\PhotographerProfileController::class, 'updateMedia'])->name('photographers.profile.media.update');
    Route::get('/profile/photographer/photos', [\App\Http\Controllers\PhotographerProfileController::class, 'photos'])->name('photographers.profile.photos');
    Route::post('/profile/photographer/photos', [\App\Http\Controllers\PhotographerProfileController::class, 'uploadPhotos'])->name('photographers.profile.upload-photos');
    
    // Verification (accessible without profile completion)
    Route::get('/verification', [\App\Http\Controllers\VerificationController::class, 'create'])->name('verification.create');
    Route::get('/verification/start', [\App\Http\Controllers\VerificationController::class, 'start'])->name('verification.start');
    Route::post('/verification', [\App\Http\Controllers\VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verification/mobile-status/{token}', [\App\Http\Controllers\VerificationController::class, 'mobileStatus'])->name('verification.mobile.status');
});

Route::get('/verification/mobile/{token}', [\App\Http\Controllers\VerificationController::class, 'mobile'])->name('verification.mobile');
Route::post('/verification/mobile/{token}', [\App\Http\Controllers\VerificationController::class, 'mobileStore'])->name('verification.mobile.store');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    // Two-Factor Authentication
    Route::get('/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/authenticator', [\App\Http\Controllers\TwoFactorController::class, 'enableAuthenticator'])->name('two-factor.authenticator');
    Route::post('/two-factor/email', [\App\Http\Controllers\TwoFactorController::class, 'enableEmail'])->name('two-factor.email');
    Route::get('/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::delete('/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');
    
    // Shared Portfolio Management
    Route::get('/portfolio', [\App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/upload', [\App\Http\Controllers\PortfolioController::class, 'create'])->name('portfolio.create');
    Route::post('/portfolio', [\App\Http\Controllers\PortfolioController::class, 'store'])->name('portfolio.store');
    Route::patch('/portfolio/polaroids/labels', [\App\Http\Controllers\PortfolioImageController::class, 'updatePolaroidLabels'])->name('portfolio.polaroids.labels.update');
    Route::get('/portfolio/credits/search', [\App\Http\Controllers\PortfolioCreditController::class, 'search'])->name('portfolio.credits.search');
    Route::post('/portfolio/credits', [\App\Http\Controllers\PortfolioCreditController::class, 'store'])->name('portfolio.credits.store');
    Route::post('/portfolio/credits/request', [\App\Http\Controllers\PortfolioCreditController::class, 'requestTag'])->name('portfolio.credits.request');
    Route::patch('/portfolio/credits/{credit}', [\App\Http\Controllers\PortfolioCreditController::class, 'update'])->name('portfolio.credits.update');
    Route::delete('/portfolio/credits/{credit}', [\App\Http\Controllers\PortfolioCreditController::class, 'destroy'])->name('portfolio.credits.destroy');
    Route::get('/portfolio/{id}/edit', [\App\Http\Controllers\PortfolioController::class, 'edit'])->name('portfolio.edit');
    Route::patch('/portfolio/{id}', [\App\Http\Controllers\PortfolioController::class, 'update'])->name('portfolio.update');
    Route::delete('/portfolio/{id}', [\App\Http\Controllers\PortfolioController::class, 'destroy'])->name('portfolio.destroy');
    Route::post('/portfolio/bulk-action', [\App\Http\Controllers\PortfolioController::class, 'bulkAction'])->name('portfolio.bulk-action');
    Route::post('/portfolio/reorder', [\App\Http\Controllers\PortfolioController::class, 'reorder'])->name('portfolio.reorder');
    
    // Shared Gallery Management
    Route::get('/portfolio/galleries', [\App\Http\Controllers\PortfolioGalleryController::class, 'index'])->name('portfolio.galleries.index');
    Route::get('/portfolio/galleries/create', [\App\Http\Controllers\PortfolioGalleryController::class, 'create'])->name('portfolio.galleries.create');
    Route::post('/portfolio/galleries', [\App\Http\Controllers\PortfolioGalleryController::class, 'store'])->name('portfolio.galleries.store');
    Route::get('/portfolio/galleries/{id}', [\App\Http\Controllers\PortfolioGalleryController::class, 'show'])->name('portfolio.galleries.show');
    Route::get('/portfolio/galleries/{id}/edit', [\App\Http\Controllers\PortfolioGalleryController::class, 'edit'])->name('portfolio.galleries.edit');
    Route::patch('/portfolio/galleries/{id}', [\App\Http\Controllers\PortfolioGalleryController::class, 'update'])->name('portfolio.galleries.update');
    Route::delete('/portfolio/galleries/{id}', [\App\Http\Controllers\PortfolioGalleryController::class, 'destroy'])->name('portfolio.galleries.destroy');
    Route::post('/portfolio/galleries/{id}/verify-age', [\App\Http\Controllers\PortfolioGalleryController::class, 'verifyAge'])->name('portfolio.galleries.verify-age');

    
    // Messaging
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/summary', [\App\Http\Controllers\MessageController::class, 'summary'])->name('messages.summary');
    Route::get('/messages/with/{recipient}', [\App\Http\Controllers\MessageController::class, 'open'])->name('messages.open');
    Route::delete('/messages/items/{message}', [\App\Http\Controllers\MessageController::class, 'unsend'])->name('messages.unsend');
    Route::get('/messages/{thread}/thread', [\App\Http\Controllers\MessageController::class, 'thread'])->name('messages.thread');
    Route::get('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/summary', [\App\Http\Controllers\NotificationController::class, 'summary'])->name('notifications.summary');
    Route::post('/notifications/credits', [\App\Http\Controllers\NotificationController::class, 'updateCreditStatus'])->name('notifications.credits.update');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/open', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.open');
    Route::get('/connections', [\App\Http\Controllers\ConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections/{user}', [\App\Http\Controllers\ConnectionController::class, 'store'])->name('connections.store');
    Route::post('/connections/{connection}/accept', [\App\Http\Controllers\ConnectionController::class, 'accept'])->name('connections.accept');
    Route::post('/connections/{connection}/decline', [\App\Http\Controllers\ConnectionController::class, 'decline'])->name('connections.decline');
    Route::post('/connections/{connection}/block', [\App\Http\Controllers\ConnectionController::class, 'block'])->name('connections.block');
    Route::delete('/connections/{connection}', [\App\Http\Controllers\ConnectionController::class, 'destroy'])->name('connections.destroy');
    Route::post('/galleries/images/comments', [\App\Http\Controllers\PublicGalleryController::class, 'comment'])->name('public.galleries.comments.store');
    Route::post('/feed', [\App\Http\Controllers\FeedController::class, 'store'])->name('feed.store');
    Route::get('/feed/link-preview', [\App\Http\Controllers\FeedController::class, 'previewLink'])->name('feed.link-preview');
    Route::patch('/feed/mentions/{mention}', [\App\Http\Controllers\FeedController::class, 'updateMention'])->name('feed.mentions.update');
    
    // Browse Models
    Route::get('/models', [\App\Http\Controllers\ModelBrowseController::class, 'index'])->name('models.browse');
    
    // Browse Photographers
    Route::get('/photographers', [\App\Http\Controllers\PhotographerBrowseController::class, 'index'])->name('photographers.browse');
    
    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/verification', [\App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('verification.index');
        Route::get('/verification/{id}', [\App\Http\Controllers\Admin\VerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/{id}/approve', [\App\Http\Controllers\Admin\VerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{id}/reject', [\App\Http\Controllers\Admin\VerificationController::class, 'reject'])->name('verification.reject');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        
        // Photographer Options Management
        Route::prefix('photographer-options')->name('photographer-options.')->group(function () {
            // Specialties
            Route::get('/specialties', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'specialties'])->name('specialties');
            Route::post('/specialties', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'storeSpecialty'])->name('specialties.store');
            Route::patch('/specialties/{id}', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'updateSpecialty'])->name('specialties.update');
            Route::delete('/specialties/{id}', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'deleteSpecialty'])->name('specialties.delete');
            
            // Services
            Route::get('/services', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'services'])->name('services');
            Route::post('/services', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'storeService'])->name('services.store');
            Route::patch('/services/{id}', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'updateService'])->name('services.update');
            Route::delete('/services/{id}', [\App\Http\Controllers\Admin\PhotographerOptionsController::class, 'deleteService'])->name('services.delete');
        });

        Route::prefix('model-options')->name('model-options.')->group(function () {
            Route::get('/appearance', [\App\Http\Controllers\Admin\ModelProfileOptionsController::class, 'appearance'])->name('appearance');
            Route::patch('/appearance', [\App\Http\Controllers\Admin\ModelProfileOptionsController::class, 'updateAppearance'])->name('appearance.update');
        });
    });
});

// Public Model Profiles
Route::get('/galleries/{gallery}', [\App\Http\Controllers\PublicGalleryController::class, 'show'])->name('public.galleries.show');
Route::post('/galleries/{gallery}/verify-age', [\App\Http\Controllers\PublicGalleryController::class, 'verifyAge'])->name('public.galleries.verify-age');
Route::get('/models/{legacyId}/galleries', fn () => response('Not Found', 404))->whereNumber('legacyId');
Route::get('/models/{legacyId}', fn () => response('Not Found', 404))->whereNumber('legacyId');
Route::get('/models/{username}/galleries', [\App\Http\Controllers\ModelProfileController::class, 'galleries'])->name('models.galleries');
Route::get('/models/{username}', [\App\Http\Controllers\ModelProfileController::class, 'show'])->name('models.show');

// Public Photographer Profiles
Route::get('/photographers/{legacyId}', fn () => response('Not Found', 404))->whereNumber('legacyId');
Route::get('/photographers/{username}', [\App\Http\Controllers\PhotographerProfileController::class, 'show'])->name('photographers.show');

// Legal Pages
Route::get('/terms', [\App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [\App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/cookies', [\App\Http\Controllers\LegalController::class, 'cookies'])->name('legal.cookies');

require __DIR__.'/auth.php';
