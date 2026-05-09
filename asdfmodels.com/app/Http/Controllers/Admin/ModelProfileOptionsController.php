<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ModelProfileOptions;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelProfileOptionsController extends Controller
{
    public function appearance(): View
    {
        return view('admin.model-options.appearance', [
            'hairColors' => ModelProfileOptions::hairColors(),
            'eyeColors' => ModelProfileOptions::eyeColors(),
        ]);
    }

    public function updateAppearance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hair_colors' => ['nullable', 'string'],
            'eye_colors' => ['nullable', 'string'],
        ]);

        $hairColors = $this->linesToList($validated['hair_colors'] ?? '');
        $eyeColors = $this->linesToList($validated['eye_colors'] ?? '');

        if ($hairColors === []) {
            return back()->withErrors(['hair_colors' => 'Add at least one hair colour option.'])->withInput();
        }

        if ($eyeColors === []) {
            return back()->withErrors(['eye_colors' => 'Add at least one eye colour option.'])->withInput();
        }

        Setting::setValue('model_hair_color_options', json_encode($hairColors), 'string', 'Available model hair colour options');
        Setting::setValue('model_eye_color_options', json_encode($eyeColors), 'string', 'Available model eye colour options');

        return redirect()->route('admin.model-options.appearance')
            ->with('status', 'Model appearance options updated successfully.');
    }

    private function linesToList(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($line) => trim($line),
            $lines
        ))));
    }
}
