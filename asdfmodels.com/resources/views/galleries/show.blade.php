@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6/dist/dropzone.css">
@endpush

<x-app-layout>
    @php
        $isPhotographer = ($role ?? 'model') === 'photographer';
        $galleryTitle = $gallery->title ?? $gallery->name;
        $galleryImageField = $isPhotographer ? 'gallery_id' : 'album_id';
        $visibilityLabel = $isPhotographer ? ucfirst($gallery->visibility ?? ($gallery->is_public ? 'public' : 'private')) : ($gallery->is_public ? 'Public' : 'Private');
        $statusLabel = $isPhotographer ? ucfirst($gallery->status ?? 'published') : ($gallery->contains_nudity ? 'NSFW Content' : 'Standard Content');
        $ownerCanManage = auth()->check() && auth()->id() === $ownerId;
        $credits = collect($credits ?? []);
        $creditableAlbumKey = \App\Models\PortfolioAlbum::class . ':' . $gallery->id;
        $creditsByTarget = $credits->groupBy(fn ($credit) => $credit->creditable_type . ':' . $credit->creditable_id);
    @endphp

    <x-slot name="header">
        <div class="space-y-3">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-2 lg:flex-row lg:flex-wrap lg:items-center lg:gap-x-3 lg:gap-y-2">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $galleryTitle }}
                    </h2>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700">
                        <i class="fas fa-images text-xs text-gray-500"></i>
                        <span>{{ $gallery->images->count() }} {{ $gallery->images->count() === 1 ? 'image' : 'images' }}</span>
                    </span>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-800">
                        <i class="fas fa-eye text-xs text-blue-600"></i>
                        <span>{{ $visibilityLabel }}</span>
                    </span>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium {{ $gallery->contains_nudity ? 'text-amber-800' : 'text-emerald-800' }}">
                        <i class="fas {{ $gallery->contains_nudity ? 'fa-triangle-exclamation text-amber-600' : 'fa-shield-heart text-emerald-600' }} text-xs"></i>
                        <span>{{ $statusLabel }}</span>
                    </span>
                </div>
                @if($gallery->description)
                    <p class="max-w-4xl text-sm leading-6 text-gray-600">{{ $gallery->description }}</p>
                @endif
            </div>
            <a href="{{ route('portfolio.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Back to Portfolio</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div
            class="max-w-7xl mx-auto sm:px-6 lg:px-8"
            x-data="galleryManager({{ $gallery->id }})"
            x-init="initSortable(); justifyGrid(); initUploadFocus(); window.addEventListener('resize', () => { justifyGrid(); });"
        >
            @if(request()->boolean('upload') && $ownerCanManage)
                <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                    Gallery created. Add images below to start building it out.
                </div>
            @endif

            @if($gallery->images->count() > 0)
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Gallery Images</h3>
                        <p class="text-sm text-gray-600">
                            Browse the gallery normally, then switch on rearranging when you want to change image order.
                        </p>
                    </div>
                    @if($ownerCanManage)
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="toggleReorderMode()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition"
                                :class="isReorderMode ? 'border-gray-900 bg-gray-900 text-white hover:bg-gray-800' : 'border-gray-300 bg-white text-gray-800 hover:border-gray-400 hover:bg-gray-50'"
                            >
                                <i class="fas" :class="isReorderMode ? 'fa-check' : 'fa-grip-vertical'"></i>
                                <span x-text="isReorderMode ? 'Done Rearranging' : 'Re-Arrange'"></span>
                            </button>
                            <a href="{{ route('portfolio.galleries.edit', $gallery->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition-colors hover:border-gray-400 hover:bg-gray-50">
                                <i class="fas fa-pen-to-square text-sm"></i>
                                <span>Edit Gallery</span>
                            </a>
                            <button type="button" onclick="openCreditsModal()" class="gallery-credit-button">
                                <i class="fas fa-user-tag"></i>
                                <span>Credits</span>
                            </button>
                            <form method="POST" action="{{ route('portfolio.galleries.destroy', $gallery->id) }}" onsubmit="return confirm('Delete this gallery? Images will remain in your portfolio, but they will be removed from this gallery.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition-colors hover:border-red-300 hover:bg-red-100">
                                    <i class="fas fa-trash text-sm"></i>
                                    <span>Delete Gallery</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div x-show="isReorderMode" x-cloak class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Rearranging is on. Drag images into the order you want, then press <span class="font-semibold">Done Rearranging</span>.
                </div>

                <div class="justified-grid relative" id="gallery-images-grid" x-ref="gridContainer">
                    @foreach($gallery->images as $image)
                        @php
                            $isCoverImage = $isPhotographer
                                ? ($gallery->cover_image_path === $image->full_path)
                                : ((int) $gallery->cover_image_id === (int) $image->id);
                        @endphp
                        <div
                            class="relative overflow-hidden rounded-lg border-2 border-gray-200 hover:border-gray-800 transition-all group sortable-item justified-image-item"
                            :class="isReorderMode ? 'cursor-move ring-2 ring-amber-300 ring-offset-2' : 'cursor-default'"
                            data-image-id="{{ $image->id }}"
                        >
                            <img src="{{ asset($image->full_path ?? $image->thumbnail_path) }}"
                                 alt="{{ $image->title ?? 'Image' }}"
                                 class="justified-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 pointer-events-none"
                                 onload="this.setAttribute('data-aspect-ratio', this.naturalWidth / this.naturalHeight); window.dispatchEvent(new Event('resize'));"
                                 data-aspect-ratio="1">

                            @if($ownerCanManage)
                                <div x-show="isReorderMode" x-cloak class="absolute top-14 left-2 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-grip-vertical"></i> Drag to reorder
                                </div>
                                <button
                                    type="button"
                                    onclick="setCoverImage({{ $image->id }}, this); event.stopPropagation();"
                                    class="absolute top-2 left-2 {{ $isCoverImage ? 'bg-amber-500 hover:bg-amber-500' : 'bg-black/70 hover:bg-amber-500 opacity-0 group-hover:opacity-100' }} flex h-9 w-9 items-center justify-center text-white rounded-full transition-opacity shadow-lg z-10"
                                    title="{{ $isCoverImage ? 'Current cover image' : 'Set as cover image' }}"
                                >
                                    <i class="fas fa-star text-sm"></i>
                                </button>
                                <button
                                    type="button"
                                    onclick="openEditModal({{ $image->id }}, '{{ asset($image->full_path ?? $image->thumbnail_path) }}'); event.stopPropagation();"
                                    class="absolute top-2 right-2 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                                    title="Edit image"
                                >
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button
                                    type="button"
                                    onclick="deleteImage({{ $image->id }}, this); event.stopPropagation();"
                                    class="absolute bottom-2 right-2 bg-red-600 hover:bg-red-700 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                                    title="Delete image"
                                >
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            @endif

                            <div class="absolute top-2 left-2 flex flex-wrap gap-2">
                                @if($image->is_featured)
                                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded font-medium">Featured</span>
                                @endif
                                @if($supportsPolaroids && $image->is_polaroid)
                                    <span class="bg-amber-500 text-white text-xs px-2 py-1 rounded font-medium">Polaroid</span>
                                @endif
                            </div>

                            <div class="absolute top-2 right-2 flex flex-col items-end gap-2">
                                @if($image->contains_nudity)
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded font-medium">18+</span>
                                @endif
                                <span class="bg-black/70 text-white text-[11px] px-2 py-1 rounded font-medium">
                                    {{ $image->is_public ? 'Public' : 'Private' }}
                                </span>
                            </div>

                            @php
                                $imageCreditKey = $image::class . ':' . $image->id;
                                $imageCredits = $creditsByTarget->get($imageCreditKey, collect());
                            @endphp
                            @if($imageCredits->isNotEmpty())
                                <div class="gallery-credit-pills">
                                    @foreach($imageCredits->take(3) as $credit)
                                        <span class="gallery-credit-pill" title="{{ $credit->creditedUser?->display_name ?? $credit->creditedUser?->name }}: {{ str_replace('_', ' ', $credit->status) }}">
                                            <i class="fas fa-user-tag"></i>
                                            {{ $credit->creditedUser?->display_name ?? $credit->creditedUser?->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($ownerCanManage)
                    <div class="mt-6" id="gallery-upload-zone">
                        <div id="add-images-dropzone" class="dropzone bg-white rounded-xl shadow-lg border-2 border-dashed border-gray-200 p-8 text-center"></div>
                        <div class="mt-4 hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm" data-upload-previews-wrapper>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Uploading Images</h4>
                                    <p class="text-xs text-gray-500">Each image is processed separately, with live progress.</p>
                                </div>
                                <div class="text-xs font-medium text-gray-500" data-upload-summary></div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" data-upload-previews></div>
                        </div>
                    </div>
                @endif
            @else
                @if($ownerCanManage)
                    <div id="gallery-upload-zone">
                        <div id="gallery-dropzone" class="dropzone bg-white rounded-xl shadow-lg border-2 border-dashed border-gray-200 p-12 text-center"></div>
                        <div class="mt-4 hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm" data-upload-previews-wrapper>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Uploading Images</h4>
                                    <p class="text-xs text-gray-500">Each image is processed separately, with live progress.</p>
                                </div>
                                <div class="text-xs font-medium text-gray-500" data-upload-summary></div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" data-upload-previews></div>
                        </div>
                    </div>
                @else
                    <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                        <p class="text-gray-600">This gallery is empty.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($ownerCanManage)
        <div id="editImageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="closeEditModal(event)">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 max-w-5xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div id="editModalLoading" class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                    <p class="mt-4 text-gray-600">Loading image data...</p>
                </div>
                <div id="editModalContent" class="hidden">
                    <form id="editImageForm" onsubmit="submitEditForm(event)">
                        <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 md:px-8 py-6 flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Edit Image</h3>
                                <p class="text-sm text-gray-600 mt-1">Update your image details and settings</p>
                            </div>
                            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>

                        <div class="p-6 md:p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <img id="editModalImage" src="" alt="Image" class="w-full h-auto rounded-lg border-2 border-gray-200 object-cover">
                                </div>

                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label for="edit_title" class="text-sm font-semibold text-gray-900">Title (optional)</label>
                                        <input type="text" id="edit_title" name="title" class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200" placeholder="Enter image title">
                                    </div>

                                    <div class="space-y-2">
                                        <label for="edit_description" class="text-sm font-semibold text-gray-900">Description (optional)</label>
                                        <textarea id="edit_description" name="description" rows="4" class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200 resize-none" placeholder="Describe your image (optional)"></textarea>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="edit_related_entity_id" class="text-sm font-semibold text-gray-900">{{ $relatedLabel }}</label>
                                        <select id="edit_related_entity_id" name="{{ $relatedField }}" class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200">
                                            <option value="">None</option>
                                            @foreach($relatedEntities as $entity)
                                                <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="edit_shot_date" class="text-sm font-semibold text-gray-900">Shot Date (optional)</label>
                                        <input type="date" id="edit_shot_date" name="shot_date" class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200">
                                    </div>

                                    <div class="border-t border-gray-200 pt-6 space-y-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-4">Image Settings</h3>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                            <label class="flex items-center group cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" id="edit_is_featured" name="is_featured" value="1" class="sr-only">
                                                    <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative">
                                                        <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                            <path d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Featured Image</span>
                                            </label>
                                        </div>

                                        @if($supportsPolaroids)
                                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                                <label class="flex items-center group cursor-pointer">
                                                    <div class="relative">
                                                        <input type="checkbox" id="edit_is_polaroid" name="is_polaroid" value="1" class="sr-only">
                                                        <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative">
                                                            <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                                <path d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Polaroid</span>
                                                </label>
                                            </div>
                                        @endif

                                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                            <label class="flex items-center group cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" id="edit_is_public" name="is_public" value="1" class="sr-only">
                                                    <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative">
                                                        <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                            <path d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Public</span>
                                            </label>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                            <label class="flex items-center group cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" id="edit_contains_nudity" name="contains_nudity" value="1" class="sr-only">
                                                    <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative">
                                                        <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                            <path d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">NSFW Content</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border-t border-gray-200 px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-3">
                            <button type="button" onclick="deleteImageFromModal(); event.stopPropagation();" class="px-6 py-2.5 border-2 border-red-300 text-red-700 rounded-lg font-medium hover:bg-red-50 hover:border-red-400 transition-all duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-trash"></i>
                                Delete Image
                            </button>
                            <div class="flex gap-3">
                                <button type="button" onclick="closeEditModal()" class="px-6 py-2.5 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-white hover:border-gray-400 transition-all duration-200 text-center">
                                    Cancel
                                </button>
                                <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 active:bg-gray-950 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($ownerCanManage)
        <div id="creditsModal" class="gallery-credit-modal" aria-hidden="true">
            <div class="gallery-credit-panel" role="dialog" aria-modal="true" aria-labelledby="creditsModalTitle">
                <div class="gallery-credit-header">
                    <div>
                        <p class="gallery-credit-kicker">Credits & Tags</p>
                        <h3 id="creditsModalTitle">Tag collaborators</h3>
                        <p>Credit a {{ $isPhotographer ? 'model' : 'photographer' }} on the full gallery, selected images, or both.</p>
                    </div>
                    <button type="button" class="gallery-credit-close" onclick="closeCreditsModal()" aria-label="Close credits">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="gallery-credit-body">
                    <div class="gallery-credit-form-grid">
                        <div class="gallery-credit-field">
                            <label for="creditRole">Role</label>
                            <select id="creditRole">
                                <option value="{{ $isPhotographer ? 'model' : 'photographer' }}">{{ $isPhotographer ? 'Model' : 'Photographer' }}</option>
                                <option value="{{ $isPhotographer ? 'photographer' : 'model' }}">{{ $isPhotographer ? 'Photographer' : 'Model' }}</option>
                            </select>
                        </div>
                        <div class="gallery-credit-field">
                            <label for="creditSearch">Member</label>
                            <input id="creditSearch" type="search" placeholder="Search by @username" autocomplete="off">
                            <div id="creditSearchResults" class="gallery-credit-results"></div>
                            <input id="creditUserId" type="hidden">
                        </div>
                    </div>

                    <label class="gallery-credit-check">
                        <input id="creditWholeGallery" type="checkbox" value="1">
                        <span>
                            <strong>Credit the full gallery</strong>
                            <small>This shows as a gallery credit after the tagged member accepts it.</small>
                        </span>
                    </label>

                    <div class="gallery-credit-select-row">
                        <div>
                            <h4>Select individual images</h4>
                            <p>If only some images include the person, select only those images. These are the ones that can appear on their profile.</p>
                        </div>
                        <button type="button" onclick="toggleAllCreditImages()">Select all</button>
                    </div>

                    <div class="gallery-credit-image-grid">
                        @foreach($gallery->images as $image)
                            <label class="gallery-credit-image-option">
                                <input type="checkbox" value="{{ $image->id }}" data-credit-image>
                                <img src="{{ asset($image->thumbnail_path ?? $image->full_path) }}" alt="{{ $image->title ?? 'Gallery image' }}">
                                <span><i class="fas fa-check"></i></span>
                            </label>
                        @endforeach
                    </div>

                    @if($credits->isNotEmpty())
                        <div class="gallery-credit-existing">
                            <h4>Current credits</h4>
                            <div class="gallery-credit-existing-list">
                                @foreach($credits as $credit)
                                    <div class="gallery-credit-existing-item" data-credit-row="{{ $credit->id }}">
                                        <div>
                                            <strong>{{ $credit->creditedUser?->display_name ?? $credit->creditedUser?->name }}</strong>
                                            <span>{{ class_basename($credit->creditable_type) === 'PortfolioAlbum' ? 'Gallery' : 'Image' }} · {{ str_replace('_', ' ', $credit->status) }}</span>
                                        </div>
                                        <button type="button" onclick="deleteCredit({{ $credit->id }})">Remove</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="gallery-credit-footer">
                    <span id="creditStatus" class="gallery-credit-status"></span>
                    <button type="button" onclick="closeCreditsModal()" class="gallery-credit-secondary">Cancel</button>
                    <button type="button" onclick="submitCredits()" class="gallery-credit-primary">
                        <i class="fas fa-user-tag"></i>
                        Save Credits
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/dropzone@6/dist/dropzone-min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const galleryId = {{ $gallery->id }};
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const uploadUrl = '{{ route("portfolio.store") }}';
                const galleryFieldName = @json($galleryImageField);
                const role = @json($role);
                const relatedField = @json($relatedField);

                const emptyDropzone = document.getElementById('gallery-dropzone');
                if (emptyDropzone) {
                    createGalleryDropzone(emptyDropzone, galleryId, uploadUrl, csrfToken);
                }

                const addImagesDropzone = document.getElementById('add-images-dropzone');
                if (addImagesDropzone) {
                    createGalleryDropzone(addImagesDropzone, galleryId, uploadUrl, csrfToken, true);
                }

                function createGalleryDropzone(element, galleryId, uploadUrl, csrfToken, compact = false) {
                    const uploadZone = element.closest('#gallery-upload-zone');
                    const previewsWrapper = uploadZone?.querySelector('[data-upload-previews-wrapper]');
                    const previewsContainer = uploadZone?.querySelector('[data-upload-previews]');
                    const summaryElement = uploadZone?.querySelector('[data-upload-summary]');

                    const previewTemplate = `
                        <div class="dz-preview dz-file-preview overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            <div class="flex gap-4 p-3">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                    <img data-dz-thumbnail class="h-full w-full object-cover" alt="Upload preview" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900" data-dz-name></p>
                                            <p class="mt-1 text-xs text-gray-500" data-dz-size></p>
                                        </div>
                                        <button type="button" class="text-gray-400 transition hover:text-red-600" data-dz-remove title="Remove upload">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                            <div data-dz-uploadprogress class="h-full w-0 rounded-full bg-gray-900 transition-all duration-300"></div>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <span class="text-xs font-medium text-gray-500" data-upload-status>Queued</span>
                                            <span class="hidden text-xs font-medium text-red-600" data-dz-errormessage></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    let successfulUploads = 0;
                    let failedUploads = 0;

                    const updateUploadSummary = () => {
                        if (!summaryElement || !previewsContainer || !previewsWrapper) return;
                        const activeCards = previewsContainer.querySelectorAll('.dz-preview').length;
                        if (activeCards === 0 && successfulUploads === 0 && failedUploads === 0) {
                            previewsWrapper.classList.add('hidden');
                            summaryElement.textContent = '';
                            return;
                        }
                        previewsWrapper.classList.remove('hidden');
                        const parts = [];
                        if (successfulUploads > 0) parts.push(`${successfulUploads} uploaded`);
                        if (failedUploads > 0) parts.push(`${failedUploads} failed`);
                        if (activeCards > successfulUploads + failedUploads) parts.push(`${activeCards - successfulUploads - failedUploads} processing`);
                        summaryElement.textContent = parts.join(' • ');
                    };

                    new Dropzone(element, {
                        url: uploadUrl,
                        paramName: 'images[]',
                        maxFilesize: 10,
                        acceptedFiles: 'image/jpeg,image/jpg,image/png',
                        addRemoveLinks: false,
                        previewsContainer,
                        previewTemplate,
                        dictDefaultMessage: compact
                            ? '<i class="fas fa-plus-circle text-4xl text-gray-400 mb-2"></i><p class="text-sm font-semibold text-gray-700">Add more images</p><p class="text-xs text-gray-500 mt-1">Drag files here or click to browse</p>'
                            : '<i class="fas fa-cloud-upload-alt text-6xl text-gray-300 mb-4"></i><p class="text-lg font-semibold text-gray-900">Click to upload or drag and drop</p><p class="text-sm text-gray-600 mt-1">PNG, JPG up to 10MB each</p>',
                        parallelUploads: 3,
                        uploadMultiple: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        init: function() {
                            this.on('addedfile', function(file) {
                                if (this.files.length === 1) {
                                    successfulUploads = 0;
                                    failedUploads = 0;
                                }
                                const status = file.previewElement?.querySelector('[data-upload-status]');
                                if (status) status.textContent = 'Queued';
                                updateUploadSummary();
                            });

                            this.on('sending', function(file, xhr, formData) {
                                formData.append(galleryFieldName, galleryId);
                                formData.append('is_public', '1');
                                formData.append('is_featured', '0');
                                formData.append('contains_nudity', '0');
                                if (role === 'model') {
                                    formData.append('is_polaroid', '0');
                                }
                                const status = file.previewElement?.querySelector('[data-upload-status]');
                                if (status) status.textContent = 'Uploading...';
                                updateUploadSummary();
                            });

                            this.on('uploadprogress', function(file, progress) {
                                const status = file.previewElement?.querySelector('[data-upload-status]');
                                if (status) status.textContent = `Uploading ${Math.round(progress)}%`;
                            });

                            this.on('success', function(file) {
                                successfulUploads++;
                                const status = file.previewElement?.querySelector('[data-upload-status]');
                                if (status) status.textContent = 'Uploaded';
                                file.previewElement?.classList.add('ring-1', 'ring-emerald-200');
                                updateUploadSummary();
                            });

                            this.on('error', function(file, message) {
                                failedUploads++;
                                const status = file.previewElement?.querySelector('[data-upload-status]');
                                if (status) status.textContent = 'Upload failed';
                                const errorText = typeof message === 'string' ? message : 'Upload failed';
                                const errorMessage = file.previewElement?.querySelector('[data-dz-errormessage]');
                                if (errorMessage) {
                                    errorMessage.textContent = errorText;
                                    errorMessage.classList.remove('hidden');
                                }
                                file.previewElement?.classList.add('ring-1', 'ring-red-200');
                                updateUploadSummary();
                            });

                            this.on('queuecomplete', function() {
                                updateUploadSummary();
                                if (successfulUploads > 0) {
                                    window.asdfSound?.play('done');
                                    setTimeout(() => window.location.reload(), 1000);
                                }
                            });
                        }
                    });
                }
            });

            let currentEditImageId = null;

            async function openEditModal(imageId, imageSrc) {
                currentEditImageId = imageId;
                const modal = document.getElementById('editImageModal');
                const loading = document.getElementById('editModalLoading');
                const content = document.getElementById('editModalContent');
                const relatedField = @json($relatedField);
                const supportsPolaroids = @json($supportsPolaroids);

                modal.classList.remove('hidden');
                loading.classList.remove('hidden');
                content.classList.add('hidden');
                document.getElementById('editModalImage').src = imageSrc;

                const response = await fetch('{{ url('/portfolio') }}/' + imageId + '/edit', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const form = doc.querySelector('form');
                if (!form) {
                    closeEditModal();
                    return;
                }

                document.getElementById('edit_title').value = form.querySelector('#title')?.value || '';
                document.getElementById('edit_description').value = form.querySelector('#description')?.value || '';
                document.getElementById('edit_shot_date').value = form.querySelector('#shot_date')?.value || '';
                document.getElementById('edit_related_entity_id').value = form.querySelector('#' + relatedField)?.value || '';

                syncCheckbox('edit_is_featured', form.querySelector('[name="is_featured"]')?.checked || false);
                if (supportsPolaroids) {
                    syncCheckbox('edit_is_polaroid', form.querySelector('[name="is_polaroid"]')?.checked || false);
                }
                syncCheckbox('edit_is_public', form.querySelector('[name="is_public"]')?.checked || false);
                syncCheckbox('edit_contains_nudity', form.querySelector('[name="contains_nudity"]')?.checked || false);

                loading.classList.add('hidden');
                content.classList.remove('hidden');
            }

            function syncCheckbox(id, checked) {
                const input = document.getElementById(id);
                if (!input) return;
                input.checked = checked;
                const box = input.parentElement.querySelector('div.w-5');
                const icon = box.querySelector('svg');
                box.className = 'w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative ' + (checked ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white');
                icon.className = 'absolute w-3.5 h-3.5 transition-opacity duration-200 ' + (checked ? 'opacity-100' : 'opacity-0');
            }

            function closeEditModal(event) {
                if (event && event.target.id !== 'editImageModal' && !event.target.closest('.bg-white')) {
                    return;
                }

                document.getElementById('editImageModal').classList.add('hidden');
                currentEditImageId = null;
            }

            async function submitEditForm(event) {
                event.preventDefault();
                if (!currentEditImageId) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const payload = {
                    title: document.getElementById('edit_title').value || null,
                    description: document.getElementById('edit_description').value || null,
                    shot_date: document.getElementById('edit_shot_date').value || null,
                    is_featured: document.getElementById('edit_is_featured').checked,
                    is_public: document.getElementById('edit_is_public').checked,
                    contains_nudity: document.getElementById('edit_contains_nudity').checked,
                    {{ $relatedField }}: document.getElementById('edit_related_entity_id').value || null,
                    {{ $galleryImageField }}: {{ $gallery->id }},
                };

                @if($supportsPolaroids)
                    payload.is_polaroid = document.getElementById('edit_is_polaroid').checked;
                @endif

                const response = await fetch('{{ url('/portfolio') }}/' + currentEditImageId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    closeEditModal();
                    window.location.reload();
                } else {
                    alert('Failed to update image.');
                }
            }

            async function deleteImage(imageId, buttonElement) {
                if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ url('/portfolio') }}/' + imageId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    buttonElement.closest('.sortable-item')?.remove();
                    window.location.reload();
                } else {
                    alert('Failed to delete image.');
                }
            }

            async function setCoverImage(imageId, buttonElement) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ url('/portfolio') }}/' + imageId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        is_cover: true,
                        {{ $galleryImageField }}: {{ $gallery->id }}
                    })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to set cover image.');
                }
            }

            function deleteImageFromModal() {
                if (currentEditImageId) {
                    deleteImage(currentEditImageId, document.body);
                }
            }

            function galleryManager(galleryId) {
                return {
                    galleryId,
                    sortable: null,
                    isReorderMode: false,
                    initSortable() {
                        this.$nextTick(() => {
                            const grid = document.getElementById('gallery-images-grid');
                            if (!grid || !window.Sortable) return;

                            this.sortable = new Sortable(grid, {
                                animation: 150,
                                ghostClass: 'opacity-50',
                                chosenClass: 'sortable-chosen',
                                dragClass: 'opacity-30',
                                disabled: true,
                                onEnd: () => {
                                    this.reorderImages();
                                    this.justifyGrid();
                                }
                            });
                        });
                    },
                    toggleReorderMode() {
                        this.isReorderMode = !this.isReorderMode;
                        if (this.sortable) {
                            this.sortable.option('disabled', !this.isReorderMode);
                        }
                    },
                    justifyGrid() {
                        this.$nextTick(() => {
                            const container = this.$refs.gridContainer;
                            if (!container) return;

                            const items = container.querySelectorAll('.justified-image-item');
                            if (items.length === 0) return;

                            const containerWidth = container.offsetWidth;
                            const targetRowHeight = 250;
                            const gap = 16;
                            let currentRow = [];
                            let currentRowWidth = 0;

                            items.forEach((item) => {
                                const img = item.querySelector('.justified-img');
                                if (!img) return;

                                const aspectRatio = parseFloat(img.getAttribute('data-aspect-ratio')) || 1;
                                const itemWidth = targetRowHeight * aspectRatio;

                                if (currentRowWidth + itemWidth + (currentRow.length * gap) > containerWidth && currentRow.length > 0) {
                                    const actualRowHeight = (containerWidth - (currentRow.length - 1) * gap) / currentRow.reduce((sum, i) => sum + i.aspectRatio, 0);
                                    currentRow.forEach((rowItem) => {
                                        const width = actualRowHeight * rowItem.aspectRatio;
                                        rowItem.element.style.width = width + 'px';
                                        rowItem.element.style.height = actualRowHeight + 'px';
                                        rowItem.img.style.height = actualRowHeight + 'px';
                                        rowItem.img.style.width = 'auto';
                                    });
                                    currentRow = [];
                                    currentRowWidth = 0;
                                }

                                currentRow.push({ element: item, img, aspectRatio });
                                currentRowWidth += itemWidth;
                            });

                            if (currentRow.length > 0) {
                                const actualRowHeight = (containerWidth - (currentRow.length - 1) * gap) / currentRow.reduce((sum, i) => sum + i.aspectRatio, 0);
                                currentRow.forEach((rowItem) => {
                                    const width = actualRowHeight * rowItem.aspectRatio;
                                    rowItem.element.style.width = width + 'px';
                                    rowItem.element.style.height = actualRowHeight + 'px';
                                    rowItem.img.style.height = actualRowHeight + 'px';
                                    rowItem.img.style.width = 'auto';
                                });
                            }
                        });
                    },
                    async reorderImages() {
                        const imageIds = Array.from(document.querySelectorAll('.sortable-item')).map(item => parseInt(item.getAttribute('data-image-id')));
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        await fetch('{{ route("portfolio.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                gallery_id: this.galleryId,
                                image_ids: imageIds
                            })
                        });
                    },
                    initUploadFocus() {
                        const shouldFocusUpload = @json(request()->boolean('upload'));
                        if (!shouldFocusUpload) return;
                        this.$nextTick(() => {
                            const uploadZone = document.getElementById('gallery-upload-zone');
                            if (!uploadZone) return;
                            uploadZone.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            const dropzone = uploadZone.querySelector('.dropzone');
                            if (dropzone) {
                                dropzone.classList.add('ring-4', 'ring-blue-200');
                                setTimeout(() => dropzone.classList.remove('ring-4', 'ring-blue-200'), 2200);
                            }
                        });
                    }
                };
            }

            let selectedCreditUser = null;
            let creditSearchTimer = null;

            function openCreditsModal() {
                const modal = document.getElementById('creditsModal');
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                setTimeout(() => document.getElementById('creditSearch')?.focus(), 50);
            }

            function closeCreditsModal() {
                const modal = document.getElementById('creditsModal');
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            document.addEventListener('DOMContentLoaded', () => {
                const search = document.getElementById('creditSearch');
                const role = document.getElementById('creditRole');
                search?.addEventListener('input', () => {
                    selectedCreditUser = null;
                    document.getElementById('creditUserId').value = '';
                    clearTimeout(creditSearchTimer);
                    creditSearchTimer = setTimeout(searchCreditUsers, 220);
                });
                role?.addEventListener('change', searchCreditUsers);
            });

            async function searchCreditUsers() {
                const search = document.getElementById('creditSearch');
                const role = document.getElementById('creditRole');
                const results = document.getElementById('creditSearchResults');
                if (!search || !role || !results) return;

                const params = new URLSearchParams({ role: role.value, q: search.value || '' });
                const response = await fetch(`{{ route('portfolio.credits.search') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;

                const data = await response.json();
                results.innerHTML = '';
                data.users.forEach((user) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.innerHTML = `<strong>${escapeHtml(user.label)}</strong><span>@${escapeHtml(user.username)}</span>`;
                    button.addEventListener('click', () => {
                        selectedCreditUser = user;
                        document.getElementById('creditUserId').value = user.id;
                        search.value = `@${user.username}`;
                        results.innerHTML = '';
                    });
                    results.appendChild(button);
                });
            }

            function toggleAllCreditImages() {
                const boxes = Array.from(document.querySelectorAll('[data-credit-image]'));
                const shouldCheck = boxes.some((box) => !box.checked);
                boxes.forEach((box) => { box.checked = shouldCheck; });
            }

            async function submitCredits() {
                const status = document.getElementById('creditStatus');
                const userId = document.getElementById('creditUserId')?.value;
                const role = document.getElementById('creditRole')?.value;
                const applyGallery = document.getElementById('creditWholeGallery')?.checked;
                const imageIds = Array.from(document.querySelectorAll('[data-credit-image]:checked')).map((box) => box.value);

                if (!userId) {
                    status.textContent = 'Choose a member to credit.';
                    return;
                }

                status.textContent = 'Saving...';
                const response = await fetch('{{ route('portfolio.credits.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        credited_user_id: userId,
                        credited_role: role,
                        gallery_id: {{ $gallery->id }},
                        apply_to_gallery: applyGallery,
                        image_type: @json($isPhotographer ? 'photographer' : 'model'),
                        image_ids: imageIds
                    })
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    status.textContent = data.message || 'Could not save credits.';
                    return;
                }

                status.textContent = data.message || 'Credits saved.';
                setTimeout(() => window.location.reload(), 700);
            }

            async function deleteCredit(creditId) {
                const response = await fetch(`{{ url('/portfolio/credits') }}/${creditId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    document.querySelector(`[data-credit-row="${creditId}"]`)?.remove();
                }
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char]));
            }
        </script>
    @endpush

    <style>
        .dropzone {
            min-height: 200px;
        }
        .dropzone.dz-clickable {
            cursor: pointer;
        }
        .dropzone .dz-message {
            margin: 0;
        }
        .dropzone.dz-drag-hover {
            border-color: #1f2937;
            background-color: #f9fafb;
            transform: scale(1.02);
        }
        .dropzone .dz-preview {
            margin: 0;
        }
        .justified-grid {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-start;
        }
        .modal-open {
            overflow: hidden;
        }
        .gallery-credit-button {
            align-items: center;
            background: #111827;
            border: 1px solid #111827;
            border-radius: 10px;
            color: #fff;
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            padding: 9px 16px;
            transition: background .18s ease, transform .18s ease;
        }
        .gallery-credit-button:hover {
            background: #000;
            transform: translateY(-1px);
        }
        .gallery-credit-pills {
            bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            left: 10px;
            position: absolute;
            right: 54px;
            z-index: 11;
        }
        .gallery-credit-pill {
            align-items: center;
            backdrop-filter: blur(8px);
            background: rgba(17, 24, 39, .78);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            gap: 5px;
            max-width: 150px;
            overflow: hidden;
            padding: 5px 8px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .gallery-credit-modal {
            align-items: center;
            background: rgba(15, 23, 42, .56);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 24px;
            position: fixed;
            z-index: 70;
        }
        .gallery-credit-modal.is-open {
            display: flex;
        }
        .gallery-credit-panel {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
            display: flex;
            flex-direction: column;
            max-height: min(860px, 92vh);
            max-width: 980px;
            overflow: hidden;
            width: 100%;
        }
        .gallery-credit-header,
        .gallery-credit-footer {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 22px 24px;
        }
        .gallery-credit-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .gallery-credit-header h3 {
            color: #111827;
            font-size: 24px;
            font-weight: 850;
            margin: 2px 0 4px;
        }
        .gallery-credit-header p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .gallery-credit-kicker {
            color: #94a3b8 !important;
            font-size: 11px !important;
            font-weight: 850;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .gallery-credit-close {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #475569;
            display: inline-flex;
            height: 40px;
            justify-content: center;
            width: 40px;
        }
        .gallery-credit-body {
            overflow-y: auto;
            padding: 22px 24px 26px;
        }
        .gallery-credit-form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(160px, .32fr) 1fr;
        }
        .gallery-credit-field {
            position: relative;
        }
        .gallery-credit-field label {
            color: #334155;
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 7px;
        }
        .gallery-credit-field input,
        .gallery-credit-field select {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            color: #111827;
            min-height: 46px;
            padding: 10px 12px;
            width: 100%;
        }
        .gallery-credit-results {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
            left: 0;
            max-height: 240px;
            overflow-y: auto;
            position: absolute;
            right: 0;
            top: 74px;
            z-index: 4;
        }
        .gallery-credit-results:empty {
            display: none;
        }
        .gallery-credit-results button {
            background: #fff;
            border: 0;
            border-bottom: 1px solid #eef2f7;
            color: #111827;
            display: block;
            padding: 10px 12px;
            text-align: left;
            width: 100%;
        }
        .gallery-credit-results button:hover {
            background: #f8fafc;
        }
        .gallery-credit-results span {
            color: #64748b;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }
        .gallery-credit-check {
            align-items: flex-start;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            display: flex;
            gap: 12px;
            margin: 18px 0;
            padding: 14px;
        }
        .gallery-credit-check small {
            color: #64748b;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }
        .gallery-credit-select-row {
            align-items: end;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .gallery-credit-select-row h4,
        .gallery-credit-existing h4 {
            color: #111827;
            font-size: 15px;
            font-weight: 850;
            margin: 0 0 4px;
        }
        .gallery-credit-select-row p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }
        .gallery-credit-select-row button,
        .gallery-credit-existing-item button,
        .gallery-credit-secondary,
        .gallery-credit-primary {
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            padding: 9px 13px;
        }
        .gallery-credit-select-row button,
        .gallery-credit-secondary {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }
        .gallery-credit-primary {
            align-items: center;
            background: #050505;
            border: 1px solid #050505;
            color: #fff;
            display: inline-flex;
            gap: 8px;
        }
        .gallery-credit-image-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
        }
        .gallery-credit-image-option {
            aspect-ratio: 1 / 1;
            border-radius: 14px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
        }
        .gallery-credit-image-option input {
            opacity: 0;
            position: absolute;
        }
        .gallery-credit-image-option img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .gallery-credit-image-option span {
            align-items: center;
            background: rgba(17, 24, 39, .78);
            border: 2px solid #fff;
            border-radius: 999px;
            color: #fff;
            display: none;
            height: 28px;
            justify-content: center;
            position: absolute;
            right: 8px;
            top: 8px;
            width: 28px;
        }
        .gallery-credit-image-option input:checked + img {
            filter: brightness(.72);
        }
        .gallery-credit-image-option input:checked ~ span {
            display: inline-flex;
        }
        .gallery-credit-existing {
            border-top: 1px solid #e5e7eb;
            margin-top: 22px;
            padding-top: 18px;
        }
        .gallery-credit-existing-list {
            display: grid;
            gap: 8px;
        }
        .gallery-credit-existing-item {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            padding: 10px 12px;
        }
        .gallery-credit-existing-item span {
            color: #64748b;
            display: block;
            font-size: 12px;
            text-transform: capitalize;
        }
        .gallery-credit-existing-item button {
            background: #fff;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .gallery-credit-footer {
            border-top: 1px solid #e5e7eb;
            justify-content: flex-end;
        }
        .gallery-credit-status {
            color: #64748b;
            flex: 1;
            font-size: 13px;
        }
        @media (max-width: 720px) {
            .gallery-credit-form-grid {
                grid-template-columns: 1fr;
            }
            .gallery-credit-modal {
                padding: 10px;
            }
        }
    </style>
</x-app-layout>
