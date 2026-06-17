<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotographerSpecialty;
use App\Models\PhotographerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotographerOptionsController extends Controller
{
    private function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * Display the specialties management page.
     */
    public function specialties(): View
    {
        $specialties = PhotographerSpecialty::orderBy('label')
            ->orderBy('key')
            ->get();

        return view('admin.photographer-options.specialties', [
            'specialties' => $specialties,
        ]);
    }

    /**
     * Store a new specialty.
     */
    public function storeSpecialty(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_specialties,key'],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'applies_to_photographers' => ['nullable', 'boolean'],
            'applies_to_models' => ['nullable', 'boolean'],
        ]);

        if (!$request->boolean('applies_to_photographers') && !$request->boolean('applies_to_models')) {
            return back()
                ->withInput()
                ->withErrors(['applies_to_roles' => 'Choose at least one profile type for this specialty.']);
        }

        $specialty = PhotographerSpecialty::create([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
            'applies_to_photographers' => $request->boolean('applies_to_photographers'),
            'applies_to_models' => $request->boolean('applies_to_models'),
        ]);

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Specialty created successfully.',
                'specialty' => $this->serializeSpecialty($specialty),
            ]);
        }

        return redirect()->to(route('admin.photographer-options.specialties') . '#specialties-list')
            ->with('status', 'Specialty created successfully.');
    }

    /**
     * Update a specialty.
     */
    public function updateSpecialty(Request $request, int $id)
    {
        $specialty = PhotographerSpecialty::findOrFail($id);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_specialties,key,' . $id],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'applies_to_photographers' => ['nullable', 'boolean'],
            'applies_to_models' => ['nullable', 'boolean'],
        ]);

        if (!$request->boolean('applies_to_photographers') && !$request->boolean('applies_to_models')) {
            return back()
                ->withInput()
                ->withErrors(['edit_applies_to_roles' => 'Choose at least one profile type for this specialty.']);
        }

        $specialty->update([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'applies_to_photographers' => $request->boolean('applies_to_photographers'),
            'applies_to_models' => $request->boolean('applies_to_models'),
        ]);

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Specialty updated successfully.',
                'specialty' => $this->serializeSpecialty($specialty->fresh()),
            ]);
        }

        return redirect()->to(route('admin.photographer-options.specialties') . '#specialty-' . $specialty->id)
            ->with('status', 'Specialty updated successfully.');
    }

    /**
     * Delete a specialty.
     */
    public function deleteSpecialty(Request $request, int $id)
    {
        $specialty = PhotographerSpecialty::findOrFail($id);
        
        // Note: We don't delete from user profiles - they'll just be filtered out
        // when displaying. This ensures no data loss.
        $specialty->delete();

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Specialty deleted successfully. Note: This specialty will be removed from user profiles automatically.',
                'id' => $id,
            ]);
        }

        return redirect()->to(route('admin.photographer-options.specialties') . '#specialties-list')
            ->with('status', 'Specialty deleted successfully. Note: This specialty will be removed from user profiles automatically.');
    }

    /**
     * Display the services management page.
     */
    public function services(): View
    {
        $services = PhotographerService::orderBy('label')
            ->orderBy('key')
            ->get();

        return view('admin.photographer-options.services', [
            'services' => $services,
        ]);
    }

    /**
     * Store a new service.
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:photographer_services,key'],
            'label' => ['required', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $service = PhotographerService::create([
            'key' => strtolower(str_replace(' ', '-', $validated['key'])),
            'label' => $validated['label'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Service created successfully.',
                'service' => $this->serializeService($service),
            ]);
        }

        return redirect()->to(route('admin.photographer-options.services') . '#services-list')
            ->with('status', 'Service created successfully.');
    }

    /**
     * Update a service.
     */
    public function updateService(Request $request, int $id)
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

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Service updated successfully.',
                'service' => $this->serializeService($service->fresh()),
            ]);
        }

        return redirect()->to(route('admin.photographer-options.services') . '#service-' . $service->id)
            ->with('status', 'Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function deleteService(Request $request, int $id)
    {
        $service = PhotographerService::findOrFail($id);
        
        // Note: We don't delete from user profiles - they'll just be filtered out
        // when displaying. This ensures no data loss.
        $service->delete();

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'status' => 'Service deleted successfully. Note: This service will be removed from user profiles automatically.',
                'id' => $id,
            ]);
        }

        return redirect()->to(route('admin.photographer-options.services') . '#services-list')
            ->with('status', 'Service deleted successfully. Note: This service will be removed from user profiles automatically.');
    }

    private function serializeSpecialty(PhotographerSpecialty $specialty): array
    {
        return [
            'id' => $specialty->id,
            'key' => $specialty->key,
            'label' => $specialty->label,
            'display_order' => $specialty->display_order,
            'is_active' => $specialty->is_active,
            'applies_to_photographers' => $specialty->applies_to_photographers,
            'applies_to_models' => $specialty->applies_to_models,
        ];
    }

    private function serializeService(PhotographerService $service): array
    {
        return [
            'id' => $service->id,
            'key' => $service->key,
            'label' => $service->label,
            'display_order' => $service->display_order,
            'is_active' => $service->is_active,
        ];
    }
}
