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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'profile.complete'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes (accessible without profile completion - needed to complete profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Model Profile Management (accessible without profile completion)
    Route::get('/profile/model', [\App\Http\Controllers\ModelProfileController::class, 'edit'])->name('profile.model.edit');
    Route::patch('/profile/model', [\App\Http\Controllers\ModelProfileController::class, 'update'])->name('profile.model.update');
    
    // Photographer Profile Management (accessible without profile completion)
    Route::get('/profile/photographer', [\App\Http\Controllers\PhotographerProfileController::class, 'edit'])->name('photographers.profile.edit');
    Route::patch('/profile/photographer', [\App\Http\Controllers\PhotographerProfileController::class, 'update'])->name('photographers.profile.update');
    Route::get('/profile/photographer/photos', [\App\Http\Controllers\PhotographerProfileController::class, 'photos'])->name('photographers.profile.photos');
    Route::post('/profile/photographer/photos', [\App\Http\Controllers\PhotographerProfileController::class, 'uploadPhotos'])->name('photographers.profile.upload-photos');
    
    // Verification (accessible without profile completion)
    Route::get('/verification', [\App\Http\Controllers\VerificationController::class, 'create'])->name('verification.create');
    Route::post('/verification', [\App\Http\Controllers\VerificationController::class, 'store'])->name('verification.store');
});

Route::middleware(['auth', 'profile.complete'])->group(function () {
    // Two-Factor Authentication
    Route::get('/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/authenticator', [\App\Http\Controllers\TwoFactorController::class, 'enableAuthenticator'])->name('two-factor.authenticator');
    Route::post('/two-factor/email', [\App\Http\Controllers\TwoFactorController::class, 'enableEmail'])->name('two-factor.email');
    Route::get('/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::delete('/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');
    
    // Portfolio Management (Models)
    Route::get('/portfolio', [\App\Http\Controllers\PortfolioImageController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/upload', [\App\Http\Controllers\PortfolioImageController::class, 'create'])->name('portfolio.create');
    Route::post('/portfolio', [\App\Http\Controllers\PortfolioImageController::class, 'store'])->name('portfolio.store');
    Route::get('/portfolio/{id}/edit', [\App\Http\Controllers\PortfolioImageController::class, 'edit'])->name('portfolio.edit');
    Route::patch('/portfolio/{id}', [\App\Http\Controllers\PortfolioImageController::class, 'update'])->name('portfolio.update');
    Route::delete('/portfolio/{id}', [\App\Http\Controllers\PortfolioImageController::class, 'destroy'])->name('portfolio.destroy');
    
    // Photographer Portfolio Management
    Route::get('/photographers/portfolio', [\App\Http\Controllers\PhotographerPortfolioController::class, 'index'])->name('photographers.portfolio.index');
    Route::get('/photographers/portfolio/upload', [\App\Http\Controllers\PhotographerPortfolioController::class, 'create'])->name('photographers.portfolio.create');
    Route::post('/photographers/portfolio', [\App\Http\Controllers\PhotographerPortfolioController::class, 'store'])->name('photographers.portfolio.store');
    Route::get('/photographers/portfolio/{id}/edit', [\App\Http\Controllers\PhotographerPortfolioController::class, 'edit'])->name('photographers.portfolio.edit');
    Route::patch('/photographers/portfolio/{id}', [\App\Http\Controllers\PhotographerPortfolioController::class, 'update'])->name('photographers.portfolio.update');
    Route::delete('/photographers/portfolio/{id}', [\App\Http\Controllers\PhotographerPortfolioController::class, 'destroy'])->name('photographers.portfolio.destroy');
    Route::post('/photographers/portfolio/bulk-action', [\App\Http\Controllers\PhotographerPortfolioController::class, 'bulkAction'])->name('photographers.portfolio.bulk-action');
    Route::post('/photographers/portfolio/reorder', [\App\Http\Controllers\PhotographerPortfolioController::class, 'reorder'])->name('photographers.portfolio.reorder');
    
    // Gallery Management
    Route::get('/photographers/portfolio/galleries/create', [\App\Http\Controllers\PhotographerGalleryController::class, 'create'])->name('photographers.portfolio.galleries.create');
    Route::post('/photographers/portfolio/galleries', [\App\Http\Controllers\PhotographerGalleryController::class, 'store'])->name('photographers.portfolio.galleries.store');
    Route::get('/photographers/portfolio/galleries/{id}', [\App\Http\Controllers\PhotographerGalleryController::class, 'show'])->name('photographers.portfolio.galleries.show');
    Route::get('/photographers/portfolio/galleries/{id}/edit', [\App\Http\Controllers\PhotographerGalleryController::class, 'edit'])->name('photographers.portfolio.galleries.edit');
    Route::patch('/photographers/portfolio/galleries/{id}', [\App\Http\Controllers\PhotographerGalleryController::class, 'update'])->name('photographers.portfolio.galleries.update');
    Route::delete('/photographers/portfolio/galleries/{id}', [\App\Http\Controllers\PhotographerGalleryController::class, 'destroy'])->name('photographers.portfolio.galleries.destroy');
    
    // Album Management
    Route::resource('albums', \App\Http\Controllers\PortfolioAlbumController::class);
    Route::post('/albums/{id}/verify-age', [\App\Http\Controllers\PortfolioAlbumController::class, 'verifyAge'])->name('albums.verify-age');
    
    // Messaging
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [\App\Http\Controllers\MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
    
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
    });
});

// Public Model Profiles
Route::get('/models/{id}', [\App\Http\Controllers\ModelProfileController::class, 'show'])->name('models.show');

// Public Photographer Profiles
Route::get('/photographers/{id}', [\App\Http\Controllers\PhotographerProfileController::class, 'show'])->name('photographers.show');

// Legal Pages
Route::get('/terms', [\App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [\App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/cookies', [\App\Http\Controllers\LegalController::class, 'cookies'])->name('legal.cookies');

require __DIR__.'/auth.php';
