@props([
    'polaroids',
    'polaroidLabelOptions' => [],
])

@php
    $polaroids = collect($polaroids ?? []);
@endphp

@if($polaroids->isNotEmpty())
    <x-modal name="manage-polaroids" maxWidth="6xl">
        <div class="p-6" x-data="portfolioManager({
            initialUploadIntent: null,
            selectedGalleryId: null,
            polaroidLabels: @js($polaroids->mapWithKeys(fn ($polaroid) => [$polaroid->id => collect($polaroid->tags ?? [])->first() ?: ''])->all()),
            polaroidLabelOptions: @js($polaroidLabelOptions ?? []),
        })">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Manage Polaroids</h3>
                    <p class="mt-1 text-sm text-gray-600">Relabel, delete, or upload more polaroids without crowding the page.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-upload-more-polaroids
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition-colors hover:border-gray-400 hover:bg-gray-50"
                    >
                        <i class="fas fa-plus text-sm"></i>
                        <span>Upload More</span>
                    </button>
                    <button @click="$dispatch('close-modal', 'manage-polaroids')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div
                x-show="polaroidToast"
                x-cloak
                x-transition.opacity
                class="mb-4 rounded-lg border px-4 py-3 text-sm font-semibold"
                :class="polaroidToastType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700'"
                x-text="polaroidToast"
            ></div>

            <div class="grid max-h-[70vh] grid-cols-2 gap-3 overflow-y-auto pr-1 md:grid-cols-3 xl:grid-cols-4">
                @foreach($polaroids as $polaroid)
                    @php
                        $polaroidLabel = collect($polaroid->tags ?? [])->first();
                    @endphp
                    <div class="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="relative aspect-[4/5] overflow-hidden bg-gray-100">
                            <img src="{{ asset($polaroid->thumbnail_path ?? $polaroid->full_path) }}" alt="Polaroid" class="h-full w-full object-cover">
                            <button
                                type="button"
                                @click="deletePolaroid({{ $polaroid->id }})"
                                class="absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-white opacity-0 transition-all duration-200 hover:bg-red-600 group-hover:opacity-100"
                                title="Delete polaroid"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                            <div
                                x-show="polaroidLabels['{{ $polaroid->id }}']"
                                x-cloak
                                class="absolute left-3 top-3 rounded-full bg-black/75 px-3 py-1 text-xs font-semibold text-white"
                                x-text="polaroidLabelText(polaroidLabels['{{ $polaroid->id }}'])"
                            ></div>
                        </div>
                        <div class="space-y-2 p-3">
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Polaroid Label</label>
                            <select
                                x-model="polaroidLabels['{{ $polaroid->id }}']"
                                class="w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-xs text-gray-800 focus:border-gray-800 focus:ring-0"
                            >
                                <option value="">Select a label...</option>
                                @foreach(($polaroidLabelOptions ?? []) as $value => $label)
                                    <option value="{{ $value }}" {{ $polaroidLabel === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-500" x-text="hasPolaroidLabelChanges() ? 'Unsaved label changes' : 'Labels saved'"></p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" @click="$dispatch('close-modal', 'manage-polaroids')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                <button
                    type="button"
                    @click="savePolaroidLabels()"
                    :disabled="savingPolaroidLabels || !hasPolaroidLabelChanges()"
                    class="inline-flex items-center gap-2 rounded-lg bg-black px-5 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <i class="fas fa-save text-xs" x-show="!savingPolaroidLabels"></i>
                    <i class="fas fa-spinner fa-spin text-xs" x-show="savingPolaroidLabels"></i>
                    <span x-text="savingPolaroidLabels ? 'Saving...' : 'Save Labels'"></span>
                </button>
            </div>
        </div>
    </x-modal>

    <script>
        (() => {
            if (window.polaroidUploadMoreHandlerBound) {
                return;
            }

            window.polaroidUploadMoreHandlerBound = true;

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-upload-more-polaroids]');

                if (!button) {
                    return;
                }

                event.preventDefault();
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'manage-polaroids' }));

                setTimeout(() => {
                    const hasUploadModal = document.querySelector('[data-polaroid-upload-target]');

                    if (hasUploadModal) {
                        window.dispatchEvent(new CustomEvent('configure-upload-modal', {
                            detail: {
                                mode: 'polaroids',
                                galleryId: '',
                            },
                        }));
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-images' }));
                        return;
                    }

                    window.location.href = @js(route('portfolio.index', ['type' => 'polaroids']));
                }, 180);
            });
        })();
    </script>
@endif
