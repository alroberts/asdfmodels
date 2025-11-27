<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotographerSpecialty;
use App\Models\PhotographerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotographerOptionsController extends Controller
{
    /**
     * Display the specialties management page.
     */
    public function specialties(): View
    {
        $specialties = PhotographerSpecialty::orderBy('display_order')
            ->orderBy('label')
            ->get();

        return view('admin.photographer-options.specialties', [
            'specialties' => $specialties,
        ]);
    }

    /**
     * Store a new specialty.
     */
    public function storeSpecialty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_specialties,key'],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        PhotographerSpecialty::create([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.photographer-options.specialties')
            ->with('status', 'Specialty created successfully.');
    }

    /**
     * Update a specialty.
     */
    public function updateSpecialty(Request $request, int $id): RedirectResponse
    {
        $specialty = PhotographerSpecialty::findOrFail($id);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_specialties,key,' . $id],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $specialty->update([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.photographer-options.specialties')
            ->with('status', 'Specialty updated successfully.');
    }

    /**
     * Delete a specialty.
     */
    public function deleteSpecialty(int $id): RedirectResponse
    {
        $specialty = PhotographerSpecialty::findOrFail($id);
        
        // Note: We don't delete from user profiles - they'll just be filtered out
        // when displaying. This ensures no data loss.
        $specialty->delete();

        return redirect()->route('admin.photographer-options.specialties')
            ->with('status', 'Specialty deleted successfully. Note: This specialty will be removed from user profiles automatically.');
    }

    /**
     * Display the services management page.
     */
    public function services(): View
    {
        $services = PhotographerService::orderBy('display_order')
            ->orderBy('label')
            ->get();

        return view('admin.photographer-options.services', [
            'services' => $services,
        ]);
    }

    /**
     * Store a new service.
     */
    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_services,key'],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        PhotographerService::create([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.photographer-options.services')
            ->with('status', 'Service created successfully.');
    }

    /**
     * Update a service.
     */
    public function updateService(Request $request, int $id): RedirectResponse
    {
        $service = PhotographerService::findOrFail($id);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_services,key,' . $id],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $service->update([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.photographer-options.services')
            ->with('status', 'Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function deleteService(int $id): RedirectResponse
    {
        $service = PhotographerService::findOrFail($id);
        
        // Note: We don't delete from user profiles - they'll just be filtered out
        // when displaying. This ensures no data loss.
        $service->delete();

        return redirect()->route('admin.photographer-options.services')
            ->with('status', 'Service deleted successfully. Note: This service will be removed from user profiles automatically.');
    }
}

