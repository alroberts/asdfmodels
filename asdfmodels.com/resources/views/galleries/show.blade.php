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
        $collaborators = $credits
            ->groupBy('credited_user_id')
            ->map(function ($userCredits) use ($gallery) {
                $firstCredit = $userCredits->first();
                $user = $firstCredit?->creditedUser;
                $profile = $user?->is_photographer ? $user?->photographerProfile : $user?->modelProfile;

                return [
                    'id' => $user?->id,
                    'label' => $user?->display_name ?: $user?->name,
                    'username' => $user?->username,
                    'role' => $firstCredit?->credited_role,
                    'avatar' => $profile?->profile_photo_path ? asset($profile->profile_photo_path) : null,
                    'galleryCreditId' => optional($userCredits->first(fn ($credit) => class_basename($credit->creditable_type) === 'PortfolioAlbum'))->id,
                    'galleryTagged' => (bool) $userCredits->first(fn ($credit) => class_basename($credit->creditable_type) === 'PortfolioAlbum'),
                    'imageCredits' => $userCredits
                        ->filter(fn ($credit) => class_basename($credit->creditable_type) !== 'PortfolioAlbum')
                        ->mapWithKeys(fn ($credit) => [(string) $credit->creditable_id => $credit->id])
                        ->all(),
                ];
            })
            ->filter(fn ($collaborator) => !empty($collaborator['id']))
            ->values();
    @endphp

    <x-slot name="header">
        <div class="space-y-3">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-2 lg:flex-row lg:flex-wrap lg:items-center lg:gap-x-3 lg:gap-y-2">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        <span data-gallery-title-text>{{ $galleryTitle }}</span>
                    </h2>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700">
                        <i class="fas fa-images text-xs text-gray-500"></i>
                        <span>{{ $gallery->images->count() }} {{ $gallery->images->count() === 1 ? 'image' : 'images' }}</span>
                    </span>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-800">
                        <i class="fas fa-eye text-xs text-blue-600"></i>
                        <span data-gallery-visibility-text>{{ $visibilityLabel }}</span>
                    </span>
                    <span class="hidden text-gray-300 lg:inline">|</span>
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium {{ $gallery->contains_nudity ? 'text-amber-800' : 'text-emerald-800' }}">
                        <i class="fas {{ $gallery->contains_nudity ? 'fa-triangle-exclamation text-amber-600' : 'fa-shield-heart text-emerald-600' }} text-xs"></i>
                        <span data-gallery-rating-text>{{ $statusLabel }}</span>
                    </span>
                </div>
                <p class="max-w-4xl text-sm leading-6 text-gray-600 {{ $gallery->description ? '' : 'hidden' }}" data-gallery-description-text>{{ $gallery->description }}</p>
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

            @if($ownerCanManage && $gallery->images->count() === 0)
                <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" onclick="openGallerySettingsModal()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition-colors hover:border-gray-400 hover:bg-gray-50">
                        <i class="fas fa-pen-to-square text-sm"></i>
                        <span>Edit Gallery</span>
                    </button>
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
                            <button type="button" onclick="openGallerySettingsModal()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition-colors hover:border-gray-400 hover:bg-gray-50">
                                <i class="fas fa-pen-to-square text-sm"></i>
                                <span>Edit Gallery</span>
                            </button>
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

                @if($ownerCanManage)
                    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Order</span>
                            <button type="button" @click="sortByDate('asc')" class="gallery-tool-button" :class="{ 'is-active': sortOrder === 'asc' }">
                                <i class="fas fa-arrow-up-1-9"></i>
                                <span>Oldest first</span>
                            </button>
                            <button type="button" @click="sortByDate('desc')" class="gallery-tool-button" :class="{ 'is-active': sortOrder === 'desc' }">
                                <i class="fas fa-arrow-down-9-1"></i>
                                <span>Newest first</span>
                            </button>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="toggleBulkDeleteMode()" class="gallery-tool-button" :class="{ 'is-danger-active': isBulkDeleteMode }">
                                <i class="fas" :class="isBulkDeleteMode ? 'fa-xmark' : 'fa-check-double'"></i>
                                <span x-text="isBulkDeleteMode ? 'Cancel Selection' : 'Bulk Delete'"></span>
                            </button>
                            <template x-if="isBulkDeleteMode">
                                <button type="button" @click="deleteSelectedImages()" class="gallery-tool-button is-danger" :disabled="selectedImageIds.length === 0" :class="{ 'is-disabled': selectedImageIds.length === 0 }">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete <span x-text="selectedImageIds.length"></span></span>
                                </button>
                            </template>
                        </div>
                    </div>
                @endif

                <div x-show="isReorderMode" x-cloak class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Rearranging is on. Drag images into the order you want, then press <span class="font-semibold">Done Rearranging</span>.
                </div>
                <div x-show="isBulkDeleteMode" x-cloak class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    Select one or more images to delete. This permanently removes the selected image files.
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
                            :class="{
                                'cursor-move ring-2 ring-amber-300 ring-offset-2': isReorderMode,
                                'cursor-pointer': isBulkDeleteMode,
                                'ring-4 ring-red-300 ring-offset-2 border-red-400': selectedImageIds.includes({{ $image->id }}),
                                'cursor-default': !isReorderMode && !isBulkDeleteMode
                            }"
                            data-image-id="{{ $image->id }}"
                            data-created-at="{{ optional($image->created_at)->timestamp ?? 0 }}"
                            @click="toggleImageSelection({{ $image->id }})"
                        >
                            <img src="{{ asset($image->full_path ?? $image->thumbnail_path) }}"
                                 alt="{{ $image->title ?? 'Image' }}"
                                 class="justified-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 pointer-events-none"
                                 onload="this.setAttribute('data-aspect-ratio', this.naturalWidth / this.naturalHeight); window.dispatchEvent(new Event('resize'));"
                                 data-aspect-ratio="1">

                            @if($ownerCanManage)
                                <button
                                    type="button"
                                    x-show="isBulkDeleteMode"
                                    x-cloak
                                    @click.stop="toggleImageSelection({{ $image->id }})"
                                    class="absolute top-2 left-2 z-20 flex h-9 w-9 items-center justify-center rounded-full border-2 border-white shadow-lg transition"
                                    :class="selectedImageIds.includes({{ $image->id }}) ? 'bg-red-600 text-white' : 'bg-white/90 text-gray-700'"
                                    title="Select image"
                                >
                                    <i class="fas" :class="selectedImageIds.includes({{ $image->id }}) ? 'fa-check' : 'fa-circle'"></i>
                                </button>
                                <div x-show="isReorderMode" x-cloak class="absolute top-14 left-2 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-grip-vertical"></i> Drag to reorder
                                </div>
                                <button
                                    type="button"
                                    onclick="setCoverImage({{ $image->id }}, this); event.stopPropagation();"
                                    class="absolute top-2 left-2 {{ $isCoverImage ? 'bg-amber-500 hover:bg-amber-500' : 'bg-black/70 hover:bg-amber-500 opacity-0 group-hover:opacity-100' }} flex h-9 w-9 items-center justify-center text-white rounded-full transition-opacity shadow-lg z-10"
                                    :class="{ 'hidden': isBulkDeleteMode }"
                                    title="{{ $isCoverImage ? 'Current cover image' : 'Set as cover image' }}"
                                >
                                    <i class="fas fa-star text-sm"></i>
                                </button>
                                <button
                                    type="button"
                                    onclick="openEditModal({{ $image->id }}, '{{ asset($image->full_path ?? $image->thumbnail_path) }}'); event.stopPropagation();"
                                    class="absolute top-2 right-2 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                                    :class="{ 'hidden': isBulkDeleteMode }"
                                    title="Edit image"
                                >
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button
                                    type="button"
                                    onclick="deleteImage({{ $image->id }}, this); event.stopPropagation();"
                                    class="absolute bottom-2 right-2 bg-red-600 hover:bg-red-700 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10"
                                    :class="{ 'hidden': isBulkDeleteMode }"
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
                                <div class="gallery-credit-avatars">
                                    @foreach($imageCredits->take(4) as $credit)
                                        @php
                                            $creditedProfile = $credit->creditedUser?->is_photographer
                                                ? $credit->creditedUser?->photographerProfile
                                                : $credit->creditedUser?->modelProfile;
                                            $creditedName = $credit->creditedUser?->display_name ?? $credit->creditedUser?->name;
                                        @endphp
                                        <span class="gallery-credit-avatar" title="{{ $creditedName }}: {{ str_replace('_', ' ', $credit->status) }}">
                                            @if($creditedProfile?->profile_photo_path)
                                                <img src="{{ asset($creditedProfile->profile_photo_path) }}" alt="">
                                            @else
                                                {{ mb_substr($creditedName ?? '?', 0, 1) }}
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($imageCredits->count() > 4)
                                        <span class="gallery-credit-avatar is-count">+{{ $imageCredits->count() - 4 }}</span>
                                    @endif
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
        <div id="gallerySettingsModal" class="gallery-settings-modal" aria-hidden="true" onclick="closeGallerySettingsModal(event)">
            <div class="gallery-settings-panel" onclick="event.stopPropagation()">
                <form id="gallerySettingsForm" onsubmit="submitGallerySettings(event)">
                    <div class="gallery-settings-header">
                        <div>
                            <p class="gallery-credit-kicker">Gallery Settings</p>
                            <h3>Edit Gallery</h3>
                            <p>Update the public details and visibility for this gallery. Cover image is managed directly from the image grid.</p>
                        </div>
                        <button type="button" class="gallery-credit-close" onclick="closeGallerySettingsModal()" aria-label="Close gallery settings">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="gallery-settings-body">
                        <div class="gallery-settings-field">
                            <label for="gallery_settings_title">Gallery Title</label>
                            <input id="gallery_settings_title" name="title" type="text" value="{{ $galleryTitle }}" required maxlength="255">
                        </div>

                        <div class="gallery-settings-field">
                            <label for="gallery_settings_description">Description</label>
                            <textarea id="gallery_settings_description" name="description" rows="4" maxlength="2000">{{ $gallery->description }}</textarea>
                        </div>

                        <div class="gallery-settings-grid">
                            <div class="gallery-settings-field">
                                <label for="gallery_settings_visibility">Visibility</label>
                                <select id="gallery_settings_visibility" name="visibility" required>
                                    @foreach([
                                        'public' => 'Public',
                                        'link_only' => 'Link Only',
                                        'hidden' => 'Hidden',
                                        'custom' => 'Custom',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($gallery->visibility ?? 'public') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="gallery-settings-field">
                                <label for="gallery_settings_status">Status</label>
                                <select id="gallery_settings_status" name="status" required>
                                    @foreach([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(($gallery->status ?? 'published') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="gallery-settings-check">
                            <input id="gallery_settings_contains_nudity" name="contains_nudity" type="checkbox" value="1" @checked($gallery->contains_nudity)>
                            <span>
                                <strong>NSFW Content</strong>
                                <small>Mark this gallery when the overall gallery contains NSFW material.</small>
                            </span>
                        </label>

                        <p id="gallerySettingsStatus" class="gallery-settings-status" aria-live="polite"></p>
                    </div>

                    <div class="gallery-settings-footer">
                        <button type="button" class="gallery-credit-secondary" onclick="closeGallerySettingsModal()">Cancel</button>
                        <button type="submit" class="gallery-credit-primary">
                            <i class="fas fa-save"></i>
                            <span>Save Settings</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
                        <p>Add collaborators once, set the images they appear in, then save the tag invitations together.</p>
                    </div>
                    <button type="button" class="gallery-credit-close" onclick="closeCreditsModal()" aria-label="Close credits">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="gallery-credit-body">
                    <div class="gallery-credit-form-grid">
                        <div class="gallery-credit-field">
                            <label for="creditSearch">Search for Member</label>
                            <input id="creditSearch" type="search" placeholder="Search by @username or name" autocomplete="off">
                            <div id="creditSearchResults" class="gallery-credit-results"></div>
                            <input id="creditUserId" type="hidden">
                        </div>
                        <div class="gallery-credit-field">
                            <label for="creditRole">Role</label>
                            <select id="creditRole">
                                <option value="model">Model</option>
                                <option value="photographer">Photographer</option>
                            </select>
                        </div>
                        <button type="button" onclick="addCollaborator()" class="gallery-credit-primary gallery-credit-add-button">
                            <i class="fas fa-plus"></i>
                            <span>Tag</span>
                        </button>
                    </div>

                    <section class="gallery-collaborators-section">
                        <div class="gallery-credit-select-row">
                            <div>
                                <h4>Collaborators</h4>
                                <p>Add everyone involved, then select one collaborator to assign them to images.</p>
                            </div>
                            <button type="button" onclick="stageAllCollaboratorsInGallery()">Tag all in gallery</button>
                        </div>
                        <div id="collaboratorList" class="gallery-collaborator-list"></div>
                    </section>

                    <div class="gallery-credit-select-row">
                        <div>
                            <h4>Assign to individual images</h4>
                            <p>Select a collaborator above, then click thumbnails to add or remove them from that image.</p>
                        </div>
                        <span id="activeCollaboratorHint" class="gallery-credit-status">Choose a collaborator to begin.</span>
                    </div>

                    <div class="gallery-credit-image-grid">
                        @foreach($gallery->images as $image)
                            <button type="button" class="gallery-credit-image-option" data-credit-image="{{ $image->id }}" onclick="toggleCollaboratorImage({{ $image->id }})">
                                <img src="{{ asset($image->thumbnail_path ?? $image->full_path) }}" alt="{{ $image->title ?? 'Gallery image' }}">
                                <span><i class="fas fa-check"></i></span>
                                <div class="gallery-credit-image-avatars" data-credit-image-avatars="{{ $image->id }}"></div>
                            </button>
                        @endforeach
                    </div>

                </div>

                <div class="gallery-credit-footer">
                    <span id="creditStatus" class="gallery-credit-status"></span>
                    <button type="button" onclick="closeCreditsModal()" class="gallery-credit-secondary">Cancel</button>
                    <button type="button" onclick="saveCollaboratorTags()" class="gallery-credit-primary" data-save-collaborator-tags>
                        <i class="fas fa-user-tag"></i>
                        Save Tags
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

            function openGallerySettingsModal() {
                const modal = document.getElementById('gallerySettingsModal');
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                setTimeout(() => document.getElementById('gallery_settings_title')?.focus(), 50);
            }

            function closeGallerySettingsModal(event) {
                if (event && event.target.id !== 'gallerySettingsModal') {
                    return;
                }

                const modal = document.getElementById('gallerySettingsModal');
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            async function submitGallerySettings(event) {
                event.preventDefault();
                const form = event.target;
                const status = document.getElementById('gallerySettingsStatus');
                const submit = form.querySelector('button[type="submit"]');
                const formData = new FormData(form);
                formData.append('_method', 'PATCH');
                formData.set('contains_nudity', document.getElementById('gallery_settings_contains_nudity')?.checked ? '1' : '0');

                if (status) {
                    status.textContent = 'Saving...';
                    status.classList.remove('is-error');
                }
                if (submit) submit.disabled = true;

                try {
                    const response = await fetch('{{ route('portfolio.galleries.update', $gallery->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || 'Could not save gallery settings.');
                    }

                    const gallery = data.gallery || {};
                    document.querySelector('[data-gallery-title-text]').textContent = gallery.title || formData.get('title');
                    const description = document.querySelector('[data-gallery-description-text]');
                    if (description) {
                        description.textContent = gallery.description || '';
                        description.classList.toggle('hidden', !gallery.description);
                    }
                    document.querySelector('[data-gallery-visibility-text]').textContent = labelForVisibility(gallery.visibility || formData.get('visibility'));
                    document.querySelector('[data-gallery-rating-text]').textContent = gallery.contains_nudity ? 'NSFW Content' : 'Standard Content';

                    if (status) status.textContent = data.message || 'Gallery settings saved.';
                    setTimeout(() => closeGallerySettingsModal(), 450);
                } catch (error) {
                    if (status) {
                        status.textContent = error.message || 'Could not save gallery settings.';
                        status.classList.add('is-error');
                    }
                } finally {
                    if (submit) submit.disabled = false;
                }
            }

            function labelForVisibility(value) {
                return {
                    public: 'Public',
                    link_only: 'Link Only',
                    hidden: 'Hidden',
                    custom: 'Custom',
                }[value] || 'Public';
            }

            function galleryManager(galleryId) {
                return {
                    galleryId,
                    sortable: null,
                    isReorderMode: false,
                    isBulkDeleteMode: false,
                    selectedImageIds: [],
                    sortOrder: null,
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
                                    this.sortOrder = null;
                                    this.reorderImages();
                                    this.justifyGrid();
                                }
                            });
                        });
                    },
                    toggleReorderMode() {
                        this.isReorderMode = !this.isReorderMode;
                        if (this.isReorderMode) {
                            this.isBulkDeleteMode = false;
                            this.selectedImageIds = [];
                        }
                        if (this.sortable) {
                            this.sortable.option('disabled', !this.isReorderMode);
                        }
                    },
                    toggleBulkDeleteMode() {
                        this.isBulkDeleteMode = !this.isBulkDeleteMode;
                        this.selectedImageIds = [];
                        if (this.isBulkDeleteMode) {
                            this.isReorderMode = false;
                            if (this.sortable) {
                                this.sortable.option('disabled', true);
                            }
                        }
                    },
                    toggleImageSelection(imageId) {
                        if (!this.isBulkDeleteMode) return;
                        if (this.selectedImageIds.includes(imageId)) {
                            this.selectedImageIds = this.selectedImageIds.filter((id) => id !== imageId);
                        } else {
                            this.selectedImageIds = [...this.selectedImageIds, imageId];
                        }
                    },
                    async sortByDate(direction) {
                        const grid = document.getElementById('gallery-images-grid');
                        if (!grid) return;

                        this.isReorderMode = false;
                        this.isBulkDeleteMode = false;
                        this.selectedImageIds = [];
                        if (this.sortable) {
                            this.sortable.option('disabled', true);
                        }

                        const items = Array.from(grid.querySelectorAll('.sortable-item'));
                        items.sort((a, b) => {
                            const aTime = Number(a.dataset.createdAt || 0);
                            const bTime = Number(b.dataset.createdAt || 0);
                            return direction === 'asc' ? aTime - bTime : bTime - aTime;
                        });
                        items.forEach((item) => grid.appendChild(item));
                        this.sortOrder = direction;
                        this.justifyGrid();
                        await this.reorderImages();
                    },
                    async deleteSelectedImages() {
                        if (this.selectedImageIds.length === 0) return;
                        const count = this.selectedImageIds.length;
                        if (!confirm(`Delete ${count} selected ${count === 1 ? 'image' : 'images'}? This cannot be undone.`)) {
                            return;
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const ids = [...this.selectedImageIds];

                        for (const imageId of ids) {
                            const response = await fetch(`{{ url('/portfolio') }}/${imageId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (response.ok) {
                                document.querySelector(`[data-image-id="${imageId}"]`)?.remove();
                            }
                        }

                        this.selectedImageIds = [];
                        this.isBulkDeleteMode = false;
                        this.justifyGrid();
                        window.location.reload();
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
            let collaborators = @js($collaborators);
            let originalCollaborators = JSON.parse(JSON.stringify(collaborators));
            let activeCollaboratorId = collaborators[0]?.id || null;

            function openCreditsModal() {
                const modal = document.getElementById('creditsModal');
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                renderCollaborators();
                renderCollaboratorAssignments();
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
                role?.addEventListener('change', () => {
                    if (selectedCreditUser) {
                        selectedCreditUser.role = role.value;
                    }
                });
                renderCollaborators();
                renderCollaboratorAssignments();
            });

            async function searchCreditUsers() {
                const search = document.getElementById('creditSearch');
                const results = document.getElementById('creditSearchResults');
                if (!search || !results) return;

                const params = new URLSearchParams({ q: search.value || '' });
                const response = await fetch(`{{ route('portfolio.credits.search') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;

                const data = await response.json();
                results.innerHTML = '';
                data.users.forEach((user) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.innerHTML = `<strong>${escapeHtml(user.label)}</strong><span>@${escapeHtml(user.username)} · ${escapeHtml(user.role)}</span>`;
                    button.addEventListener('click', () => {
                        selectedCreditUser = user;
                        document.getElementById('creditUserId').value = user.id;
                        search.value = `@${user.username}`;
                        document.getElementById('creditRole').value = user.role;
                        results.innerHTML = '';
                    });
                    results.appendChild(button);
                });
            }

            function addCollaborator() {
                const status = document.getElementById('creditStatus');
                const role = document.getElementById('creditRole')?.value;

                if (!selectedCreditUser) {
                    status.textContent = 'Choose a member first.';
                    return;
                }

                const existing = collaborators.find((collaborator) => Number(collaborator.id) === Number(selectedCreditUser.id));
                if (existing) {
                    existing.role = role || selectedCreditUser.role;
                } else {
                    collaborators.push({
                        id: selectedCreditUser.id,
                        label: selectedCreditUser.label,
                        username: selectedCreditUser.username,
                        role: role || selectedCreditUser.role,
                        avatar: selectedCreditUser.avatar || null,
                        galleryCreditId: null,
                        imageCredits: {},
                        galleryTagged: false,
                    });
                }

                activeCollaboratorId = selectedCreditUser.id;
                selectedCreditUser = null;
                document.getElementById('creditSearch').value = '';
                document.getElementById('creditUserId').value = '';
                document.getElementById('creditSearchResults').innerHTML = '';
                status.textContent = 'Collaborator added. Click images to assign them.';
                renderCollaborators();
                renderCollaboratorAssignments();
            }

            function renderCollaborators() {
                const list = document.getElementById('collaboratorList');
                if (!list) return;
                list.innerHTML = '';

                if (collaborators.length === 0) {
                    list.innerHTML = '<p class="gallery-collaborator-empty">No collaborators added yet.</p>';
                    return;
                }

                collaborators.forEach((collaborator) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `gallery-collaborator-card ${Number(activeCollaboratorId) === Number(collaborator.id) ? 'is-active' : ''}`;
                    button.innerHTML = `
                        <span class="gallery-collaborator-avatar">${collaborator.avatar ? `<img src="${collaborator.avatar}" alt="">` : escapeHtml((collaborator.label || '?').charAt(0))}</span>
                        <span class="gallery-collaborator-copy">
                            <strong>${escapeHtml(collaborator.label)}</strong>
                            <small>@${escapeHtml(collaborator.username)} · ${escapeHtml(collaborator.role)}</small>
                        </span>
                        <span class="gallery-collaborator-actions">
                            <em>${collaborator.galleryTagged ? 'Gallery' : `${Object.keys(collaborator.imageCredits || {}).length} images`}</em>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    `;
                    button.addEventListener('click', () => {
                        activeCollaboratorId = collaborator.id;
                        renderCollaborators();
                        renderCollaboratorAssignments();
                    });
                    list.appendChild(button);

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'gallery-collaborator-remove';
                    remove.innerHTML = '<i class="fas fa-trash"></i><span>Remove</span>';
                    remove.addEventListener('click', (event) => {
                        event.stopPropagation();
                    stageRemoveCollaborator(collaborator.id);
                    });
                    button.appendChild(remove);
                });
            }

            function renderCollaboratorAssignments() {
                const active = collaborators.find((collaborator) => Number(collaborator.id) === Number(activeCollaboratorId));
                const hint = document.getElementById('activeCollaboratorHint');
                if (hint) {
                    hint.textContent = active ? `Assigning ${active.label}` : 'Choose a collaborator to begin.';
                }

                document.querySelectorAll('[data-credit-image]').forEach((button) => {
                    const imageId = button.dataset.creditImage;
                    button.classList.toggle('is-active', Boolean(active?.imageCredits?.[imageId]));
                });

                document.querySelectorAll('[data-credit-image-avatars]').forEach((container) => {
                    const imageId = container.dataset.creditImageAvatars;
                    const attached = collaborators.filter((collaborator) => collaborator.imageCredits?.[imageId]);
                    container.innerHTML = attached.slice(0, 4).map((collaborator) => `
                        <span class="gallery-credit-mini-avatar" title="${escapeHtml(collaborator.label)}">
                            ${collaborator.avatar ? `<img src="${collaborator.avatar}" alt="">` : escapeHtml((collaborator.label || '?').charAt(0))}
                        </span>
                    `).join('');
                    if (attached.length > 4) {
                        container.innerHTML += `<span class="gallery-credit-mini-avatar is-count">+${attached.length - 4}</span>`;
                    }
                });
            }

            async function toggleCollaboratorImage(imageId) {
                const status = document.getElementById('creditStatus');
                const collaborator = collaborators.find((item) => Number(item.id) === Number(activeCollaboratorId));
                if (!collaborator) {
                    status.textContent = 'Choose a collaborator first.';
                    return;
                }

                const imageKey = String(imageId);

                if (Object.prototype.hasOwnProperty.call(collaborator.imageCredits || {}, imageKey)) {
                    delete collaborator.imageCredits[imageKey];
                    status.textContent = `${collaborator.label} removed from image. Press Save Tags to apply.`;
                } else {
                    collaborator.imageCredits = collaborator.imageCredits || {};
                    collaborator.imageCredits[imageKey] = collaborator.imageCredits[imageKey] || null;
                    status.textContent = `${collaborator.label} assigned to image. Press Save Tags to apply.`;
                }

                renderCollaborators();
                renderCollaboratorAssignments();
            }

            function stageAllCollaboratorsInGallery() {
                const status = document.getElementById('creditStatus');
                const imageIds = Array.from(document.querySelectorAll('[data-credit-image]')).map((button) => button.dataset.creditImage);

                if (collaborators.length === 0) {
                    status.textContent = 'Add at least one collaborator first.';
                    return;
                }

                collaborators.forEach((collaborator) => {
                    collaborator.galleryTagged = true;
                    collaborator.imageCredits = collaborator.imageCredits || {};
                    imageIds.forEach((imageId) => {
                        collaborator.imageCredits[String(imageId)] = collaborator.imageCredits[String(imageId)] || null;
                    });
                });

                status.textContent = 'All collaborators staged for the gallery. Press Save Tags to apply.';
                renderCollaborators();
                renderCollaboratorAssignments();
            }

            async function saveCollaboratorTags() {
                const status = document.getElementById('creditStatus');
                const saveButton = document.querySelector('[data-save-collaborator-tags]');

                if (collaborators.length === 0 && originalCollaborators.length === 0) {
                    status.textContent = 'Add at least one collaborator first.';
                    return;
                }

                status.textContent = 'Saving tags...';
                if (saveButton) saveButton.disabled = true;

                try {
                    await removeDeletedCredits();

                    for (const collaborator of collaborators) {
                        const original = originalCollaborators.find((item) => Number(item.id) === Number(collaborator.id));
                        const desiredImageIds = Object.keys(collaborator.imageCredits || {});
                        const originalImageCredits = original?.imageCredits || {};
                        const imagesToCreate = desiredImageIds.filter((imageId) => !originalImageCredits[imageId]);

                        if (collaborator.galleryTagged && !collaborator.galleryCreditId) {
                            const data = await createCredit(collaborator, true, []);
                            const galleryCredit = (data.credits || []).find((credit) => String(credit.creditable_type || '').includes('PortfolioAlbum'));
                            if (galleryCredit?.id) {
                                collaborator.galleryCreditId = galleryCredit.id;
                            }
                        }

                        if (!collaborator.galleryTagged && collaborator.galleryCreditId) {
                            await deleteCredit(collaborator.galleryCreditId, false);
                            collaborator.galleryCreditId = null;
                        }

                        if (imagesToCreate.length > 0) {
                            const data = await createCredit(collaborator, false, imagesToCreate);
                            (data.credits || []).forEach((credit) => {
                                if (credit?.id && !String(credit.creditable_type || '').includes('PortfolioAlbum')) {
                                    collaborator.imageCredits[String(credit.creditable_id)] = credit.id;
                                }
                            });
                        }
                    }

                    originalCollaborators = JSON.parse(JSON.stringify(collaborators));
                    status.textContent = 'Tags saved.';
                } catch (error) {
                    status.textContent = error.message || 'Could not save tags.';
                } finally {
                    if (saveButton) saveButton.disabled = false;
                    renderCollaborators();
                    renderCollaboratorAssignments();
                }
            }

            async function createCredit(collaborator, applyGallery, imageIds = []) {
                const response = await fetch('{{ route('portfolio.credits.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        credited_user_id: collaborator.id,
                        credited_role: collaborator.role,
                        gallery_id: {{ $gallery->id }},
                        apply_to_gallery: applyGallery,
                        image_type: @json($isPhotographer ? 'photographer' : 'model'),
                        image_ids: imageIds
                    })
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Could not save credits.');
                }

                return data;
            }

            async function removeDeletedCredits() {
                for (const original of originalCollaborators) {
                    const current = collaborators.find((item) => Number(item.id) === Number(original.id));

                    if (!current) {
                        const creditIds = [
                            original.galleryCreditId,
                            ...Object.values(original.imageCredits || {}),
                        ].filter(Boolean);

                        for (const creditId of creditIds) {
                            await deleteCredit(creditId, false);
                        }
                        continue;
                    }

                    if (original.galleryCreditId && !current.galleryTagged) {
                        await deleteCredit(original.galleryCreditId, false);
                    }

                    for (const [imageId, creditId] of Object.entries(original.imageCredits || {})) {
                        if (creditId && !Object.prototype.hasOwnProperty.call(current.imageCredits || {}, imageId)) {
                            await deleteCredit(creditId, false);
                        }
                    }
                }
            }

            function stageRemoveCollaborator(collaboratorId) {
                const collaborator = collaborators.find((item) => Number(item.id) === Number(collaboratorId));
                if (!collaborator) return;

                if (!confirm(`Remove ${collaborator.label} from this gallery and its images?`)) {
                    return;
                }

                collaborators = collaborators.filter((item) => Number(item.id) !== Number(collaboratorId));
                activeCollaboratorId = collaborators[0]?.id || null;
                document.getElementById('creditStatus').textContent = 'Collaborator staged for removal. Press Save Tags to apply.';
                renderCollaborators();
                renderCollaboratorAssignments();
            }

            async function deleteCredit(creditId, removeRow = true) {
                const response = await fetch(`{{ url('/portfolio/credits') }}/${creditId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok && removeRow) {
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
        .gallery-tool-button {
            align-items: center;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            color: #374151;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 7px;
            justify-content: center;
            min-height: 36px;
            padding: 8px 12px;
            transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }
        .gallery-tool-button:hover {
            border-color: #9ca3af;
            transform: translateY(-1px);
        }
        .gallery-tool-button.is-active {
            background: #111827;
            border-color: #111827;
            color: #fff;
        }
        .gallery-tool-button.is-danger,
        .gallery-tool-button.is-danger-active {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .gallery-tool-button.is-danger:not(.is-disabled):hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }
        .gallery-tool-button.is-disabled {
            cursor: not-allowed;
            opacity: .45;
            transform: none;
        }
        .gallery-settings-modal {
            align-items: center;
            background: rgba(15, 23, 42, .56);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 24px;
            position: fixed;
            z-index: 72;
        }
        .gallery-settings-modal.is-open {
            display: flex;
        }
        .gallery-settings-panel {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
            max-height: min(760px, 92vh);
            max-width: 760px;
            overflow: hidden;
            width: 100%;
        }
        .gallery-settings-header,
        .gallery-settings-footer {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 22px 24px;
        }
        .gallery-settings-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .gallery-settings-header h3 {
            color: #111827;
            font-size: 24px;
            font-weight: 850;
            margin: 2px 0 4px;
        }
        .gallery-settings-header p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .gallery-settings-body {
            display: grid;
            gap: 18px;
            max-height: calc(92vh - 180px);
            overflow-y: auto;
            padding: 22px 24px;
        }
        .gallery-settings-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gallery-settings-field label {
            color: #334155;
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 7px;
        }
        .gallery-settings-field input,
        .gallery-settings-field textarea,
        .gallery-settings-field select {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            color: #111827;
            min-height: 46px;
            padding: 10px 12px;
            width: 100%;
        }
        .gallery-settings-field textarea {
            resize: vertical;
        }
        .gallery-settings-check {
            align-items: flex-start;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            display: flex;
            gap: 12px;
            padding: 14px;
        }
        .gallery-settings-check input {
            margin-top: 4px;
        }
        .gallery-settings-check strong {
            color: #111827;
            display: block;
            font-size: 14px;
        }
        .gallery-settings-check small {
            color: #64748b;
            display: block;
            font-size: 12px;
            margin-top: 2px;
        }
        .gallery-settings-footer {
            border-top: 1px solid #e5e7eb;
        }
        .gallery-settings-status {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            min-height: 18px;
        }
        .gallery-settings-status.is-error {
            color: #b91c1c;
        }
        .gallery-credit-avatars {
            bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            left: 10px;
            position: absolute;
            right: 54px;
            z-index: 11;
        }
        .gallery-credit-avatar,
        .gallery-credit-mini-avatar {
            align-items: center;
            background: #111827;
            border: 2px solid #fff;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            height: 30px;
            justify-content: center;
            margin-right: -7px;
            overflow: hidden;
            width: 30px;
        }
        .gallery-credit-avatar img,
        .gallery-credit-mini-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .gallery-credit-avatar.is-count,
        .gallery-credit-mini-avatar.is-count {
            background: #374151;
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
            grid-template-columns: minmax(260px, 1fr) minmax(150px, .28fr) auto;
            align-items: end;
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
        .gallery-credit-add-button {
            min-height: 46px;
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
        .gallery-collaborators-section {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            margin: 18px 0;
            padding: 16px;
        }
        .gallery-collaborator-list {
            display: grid;
            gap: 10px;
        }
        .gallery-collaborator-empty {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .gallery-collaborator-card {
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            color: inherit;
            display: grid;
            gap: 12px;
            grid-template-columns: auto 1fr auto auto;
            padding: 10px;
            text-align: left;
            transition: border-color .16s ease, box-shadow .16s ease;
        }
        .gallery-collaborator-card.is-active {
            border-color: #111827;
            box-shadow: 0 0 0 2px rgba(17,24,39,.08);
        }
        .gallery-collaborator-avatar {
            align-items: center;
            background: #f3f4f6;
            border-radius: 999px;
            color: #4b5563;
            display: inline-flex;
            font-weight: 850;
            height: 42px;
            justify-content: center;
            overflow: hidden;
            width: 42px;
        }
        .gallery-collaborator-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .gallery-collaborator-copy strong,
        .gallery-collaborator-copy small {
            display: block;
        }
        .gallery-collaborator-copy small,
        .gallery-collaborator-actions {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }
        .gallery-collaborator-actions {
            align-items: center;
            display: inline-flex;
            gap: 8px;
        }
        .gallery-collaborator-remove {
            align-items: center;
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 999px;
            color: #b91c1c;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            gap: 6px;
            padding: 7px 10px;
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
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: 14px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            width: 100%;
        }
        .gallery-credit-image-option img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .gallery-credit-image-option > span {
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
        .gallery-credit-image-option.is-active {
            border-color: #111827;
        }
        .gallery-credit-image-option.is-active img {
            filter: brightness(.72);
        }
        .gallery-credit-image-option.is-active > span {
            display: inline-flex;
        }
        .gallery-credit-image-avatars {
            bottom: 8px;
            display: flex;
            left: 8px;
            position: absolute;
            z-index: 2;
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
            .gallery-settings-grid {
                grid-template-columns: 1fr;
            }
            .gallery-settings-modal {
                padding: 10px;
            }
            .gallery-credit-form-grid {
                grid-template-columns: 1fr;
            }
            .gallery-collaborator-card {
                grid-template-columns: auto 1fr;
            }
            .gallery-collaborator-actions,
            .gallery-collaborator-remove {
                grid-column: 2;
                justify-self: start;
            }
            .gallery-credit-modal {
                padding: 10px;
            }
        }
    </style>
</x-app-layout>
