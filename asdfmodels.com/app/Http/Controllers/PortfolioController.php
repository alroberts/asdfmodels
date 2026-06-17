<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->index();
        }

        return app(PortfolioImageController::class)->index();
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->create();
        }

        $uploadIntent = $request->query('type') === 'polaroids' ? 'polaroids' : 'images';
        $redirectParams = ['upload' => $uploadIntent];

        if ($request->filled('gallery')) {
            $redirectParams['gallery'] = $request->integer('gallery');
        }

        return redirect()->route('portfolio.index', $redirectParams);
    }

    public function store(Request $request)
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->store($request);
        }

        return app(PortfolioImageController::class)->store($request);
    }

    public function edit(Request $request, string $id)
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->edit($request, $id);
        }

        return app(PortfolioImageController::class)->edit($id);
    }

    public function update(Request $request, string $id)
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->update($request, $id);
        }

        return app(PortfolioImageController::class)->update($request, $id);
    }

    public function destroy(Request $request, string $id)
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->destroy($request, $id);
        }

        return app(PortfolioImageController::class)->destroy($id);
    }

    public function bulkAction(Request $request)
    {
        if (!Auth::user()->is_photographer) {
            abort(404);
        }

        return app(PhotographerPortfolioController::class)->bulkAction($request);
    }

    public function reorder(Request $request)
    {
        if (Auth::user()->is_photographer) {
            return app(PhotographerPortfolioController::class)->reorder($request);
        }

        return app(PortfolioImageController::class)->reorder($request);
    }
}
