<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortfolioGalleryController extends Controller
{
    public function index(): View
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->index();
        }

        return app(PortfolioAlbumController::class)->index();
    }

    public function create(): View
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->create();
        }

        return app(PortfolioAlbumController::class)->create();
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->store($request);
        }

        return app(PortfolioAlbumController::class)->store($request);
    }

    public function show(Request $request, string $id): View
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->show($id);
        }

        return app(PortfolioAlbumController::class)->show($request, $id);
    }

    public function edit(string $id): View
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->edit($id);
        }

        return app(PortfolioAlbumController::class)->edit($id);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->update($request, $id);
        }

        return app(PortfolioAlbumController::class)->update($request, $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerGalleryController::class)->destroy($id);
        }

        return app(PortfolioAlbumController::class)->destroy($id);
    }

    public function verifyAge(Request $request, string $id): RedirectResponse
    {
        if (Auth::user()->is_photographer) {
            abort(404);
        }

        return app(PortfolioAlbumController::class)->verifyAge($request, $id);
    }
}
