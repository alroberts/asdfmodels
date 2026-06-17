@php
    $isEditing = ($mode ?? 'create') === 'edit';
    $isPhotographer = $role === 'photographer';
    $record = $gallery ?? $album ?? null;
    $titleField = 'title';
    $titleValue = old($titleField, $record?->title ?? $record?->name);
    $descriptionValue = old('description', $record?->description);
    $coverImages = collect($images ?? $allImages ?? []);
    $selectedPhotographerCover = $isPhotographer
        ? optional($coverImages->firstWhere('full_path', $record?->cover_image_path))->id
        : null;
    $coverValue = old('cover_image_id', $isPhotographer ? $selectedPhotographerCover : $record?->cover_image_id);
    $showInlineUploader = !$isEditing;
    $galleryUploadField = $isPhotographer ? 'gallery_id' : 'album_id';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $pageTitle }}
            </h2>
            <a href="{{ $backRoute }}" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> {{ $backLabel }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ $formAction }}"
                x-data="{ visibility: '{{ old('visibility', $record?->visibility ?? 'public') }}', status: '{{ old('status', $record?->status ?? 'draft') }}', containsNudity: {{ old('contains_nudity', ($record?->contains_nudity ? '1' : '0')) == '1' ? 'true' : 'false' }}, isPublic: {{ old('is_public', $record?->is_public ?? true) ? 'true' : 'false' }} }"
                @if($showInlineUploader)
                    data-gallery-create-form
                    data-upload-url="{{ route('portfolio.store') }}"
                    data-gallery-field="{{ $galleryUploadField }}"
                    data-role="{{ $role }}"
                @endif
            >
                @csrf
                @if($isEditing)
                    @method('PATCH')
                @endif

                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 md:px-8 py-6">
                        <h3 class="text-2xl font-bold text-gray-900">Gallery Information</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $introText }}</p>
                    </div>

                    <div class="p-6 md:p-8 space-y-8">
                        <div class="space-y-2">
                            <x-input-label :for="$titleField" :value="__('Gallery Title')" class="text-sm font-semibold text-gray-900" />
                            <x-text-input
                                :id="$titleField"
                                :name="$titleField"
                                type="text"
                                class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200"
                                :value="$titleValue"
                                required
                                autofocus
                                placeholder="Enter gallery title"
                            />
                            <x-input-error :messages="$errors->get($titleField)" class="mt-2" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="description" :value="__('Description')" class="text-sm font-semibold text-gray-900" />
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200 resize-none"
                                placeholder="Describe your gallery (optional)"
                            >{{ $descriptionValue }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        @if($showInlineUploader)
                            <div class="border-t border-gray-200 pt-8">
                                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Gallery Images</h3>
                                        <p class="text-sm text-gray-600 mt-1">Add images now, or create the gallery empty and upload later.</p>
                                    </div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400" data-create-upload-summary>Optional</p>
                                </div>

                                <div
                                    class="rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center transition-colours hover:border-gray-500 hover:bg-white"
                                    data-create-upload-zone
                                    role="button"
                                    tabindex="0"
                                >
                                    <input type="file" accept="image/jpeg,image/jpg,image/png" multiple class="sr-only" data-create-upload-input>
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm">
                                        <i class="fas fa-cloud-arrow-up text-2xl"></i>
                                    </div>
                                    <p class="mt-4 text-base font-semibold text-gray-900">Drag images here or click to browse</p>
                                    <p class="mt-1 text-sm text-gray-500">JPG or PNG, up to 10MB each. Upload starts after the gallery is created.</p>
                                </div>

                                <div class="mt-4 hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm" data-create-upload-previews-wrapper>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" data-create-upload-previews></div>
                                </div>

                                <p class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" data-create-upload-error></p>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <label class="flex cursor-pointer items-start gap-4">
                                    <span class="relative mt-0.5">
                                        <input type="checkbox" name="share_in_feed" value="1" class="peer sr-only" @checked(old('share_in_feed'))>
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg border-2 border-gray-300 bg-white transition-all peer-checked:border-gray-900 peer-checked:bg-gray-900">
                                            <svg class="h-4 w-4" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </span>
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-gray-900">Share in Feed</span>
                                        <span class="mt-1 block text-sm text-gray-600">Create a feed post for this gallery so your connections can discover it from their dashboard.</span>
                                    </span>
                                </label>
                            </div>
                        @endif

                        @if($isEditing && $coverImages->count() > 0)
                            <div class="space-y-2">
                                <x-input-label for="cover_image_id" :value="__('Cover Image')" class="text-sm font-semibold text-gray-900" />
                                <div
                                    x-data="{ selectedCoverImage: @js((string) ($coverValue ?? '')) }"
                                    class="space-y-4"
                                >
                                    <input type="hidden" id="cover_image_id" name="cover_image_id" x-model="selectedCoverImage">
                                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                                        @foreach($coverImages as $image)
                                            @php
                                                $imageSrc = asset($image->thumbnail_path ?? $image->full_path);
                                            @endphp
                                            <button
                                                type="button"
                                                @click="selectedCoverImage = '{{ $image->id }}'"
                                                class="group relative overflow-hidden rounded-xl border-2 bg-white text-left transition-all duration-200"
                                                :class="selectedCoverImage === '{{ $image->id }}' ? 'border-amber-400 ring-2 ring-amber-200 shadow-md' : 'border-gray-200 hover:border-gray-400 hover:shadow-sm'"
                                            >
                                                <img
                                                    src="{{ $imageSrc }}"
                                                    alt="Gallery image"
                                                    class="h-36 w-full object-cover"
                                                >
                                                <div
                                                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full border-2 shadow-sm transition-all duration-200"
                                                    :class="selectedCoverImage === '{{ $image->id }}' ? 'border-amber-500 bg-amber-500 text-white' : 'border-white/80 bg-black/50 text-transparent group-hover:text-white'"
                                                >
                                                    <i class="fas fa-star text-xs"></i>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Pick the image you want to feature as the gallery cover.</p>
                                <x-input-error :messages="$errors->get('cover_image_id')" class="mt-2" />
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-8">
                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-gray-900">Gallery Settings</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $settingsIntro }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colours">
                                    <x-input-label for="visibility" :value="__('Gallery Visibility')" class="text-sm font-semibold text-gray-900 mb-3" />
                                    <div class="space-y-2">
                                        @foreach([
                                            ['value' => 'public', 'label' => 'Public', 'icon' => 'globe'],
                                            ['value' => 'link_only', 'label' => 'Link Only', 'icon' => 'link'],
                                            ['value' => 'hidden', 'label' => 'Hidden', 'icon' => 'eye-slash'],
                                            ['value' => 'custom', 'label' => 'Custom', 'icon' => 'users'],
                                        ] as $option)
                                            <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colours cursor-pointer group" :class="visibility === '{{ $option['value'] }}' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                                <input type="radio" name="visibility" value="{{ $option['value'] }}" x-model="visibility" class="sr-only">
                                                <i class="fas fa-{{ $option['icon'] }} text-gray-600 w-5 text-center" :class="visibility === '{{ $option['value'] }}' ? 'text-gray-900' : ''"></i>
                                                <span class="ml-3 text-sm font-medium flex-1" :class="visibility === '{{ $option['value'] }}' ? 'text-gray-900' : 'text-gray-600'">{{ $option['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get('visibility')" class="mt-3" />
                                </div>

                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colours">
                                        <x-input-label :value="__('NSFW Content')" class="text-sm font-semibold text-gray-900 mb-3" />
                                        <label class="flex items-center group cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" name="contains_nudity" value="1" x-model="containsNudity" class="sr-only">
                                                <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="containsNudity ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                    <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="containsNudity ? 'opacity-100' : 'opacity-0'">
                                                        <path d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colours">Contains NSFW content</span>
                                        </label>
                                        <x-input-error :messages="$errors->get('contains_nudity')" class="mt-3" />
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colours">
                                        <x-input-label for="status" :value="__('Gallery Status')" class="text-sm font-semibold text-gray-900 mb-3" />
                                        <div class="space-y-2">
                                            @foreach([
                                                ['value' => 'draft', 'label' => 'Draft'],
                                                ['value' => 'published', 'label' => 'Published'],
                                            ] as $option)
                                                <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colours cursor-pointer group" :class="status === '{{ $option['value'] }}' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                                    <input type="radio" name="status" value="{{ $option['value'] }}" x-model="status" class="sr-only">
                                                    <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center transition-all" :class="status === '{{ $option['value'] }}' ? 'border-gray-800' : 'border-gray-300'">
                                                        <div class="w-2 h-2 bg-gray-800 rounded-full transition-opacity" :class="status === '{{ $option['value'] }}' ? 'opacity-100' : 'opacity-0'"></div>
                                                    </div>
                                                    <span class="ml-3 text-sm font-medium flex-1 transition-colours" :class="status === '{{ $option['value'] }}' ? 'text-gray-900' : 'text-gray-600'">{{ $option['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('status')" class="mt-3" />
                                    </div>
                                </div>
                            </div>

                            <div
                                x-show="visibility === 'custom'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="mt-6 bg-gray-50 rounded-lg p-6 border border-gray-200"
                            >
                                <div class="mb-4">
                                    <x-input-label :value="__('Share with Specific Users')" class="text-sm font-semibold text-gray-900 mb-1" />
                                    <p class="text-sm text-gray-600">Select users who can view this gallery.</p>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-4 max-h-64 overflow-y-auto">
                                    <div class="space-y-2">
                                        @php
                                            $allUsers = \App\Models\User::where('is_admin', false)
                                                ->where('id', '!=', auth()->id())
                                                ->orderBy('name')
                                                ->get();
                                            $selectedUsers = old('custom_visibility_users', $record?->custom_visibility_users ?? []);
                                        @endphp
                                        @foreach($allUsers as $user)
                                            <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colours cursor-pointer group" x-data="{ checked: {{ in_array($user->id, $selectedUsers) ? 'true' : 'false' }} }">
                                                <div class="relative">
                                                    <input type="checkbox" name="custom_visibility_users[]" value="{{ $user->id }}" {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }} x-model="checked" class="sr-only">
                                                    <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="checked ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                        <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="checked ? 'opacity-100' : 'opacity-0'">
                                                            <path d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colours">{{ $user->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('custom_visibility_users')" class="mt-3" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 border-t border-gray-200 px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-end gap-3">
                        <a
                            href="{{ $backRoute }}"
                            class="px-6 py-2.5 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-white hover:border-gray-400 transition-all duration-200 text-center"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            data-gallery-create-submit
                            class="px-6 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 active:bg-gray-950 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2"
                        >
                            <i class="fas {{ $submitIcon }}"></i>
                            <span data-gallery-create-submit-text>{{ $submitLabel }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($showInlineUploader)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.querySelector('[data-gallery-create-form]');
                    if (!form) return;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const uploadUrl = form.dataset.uploadUrl;
                    const galleryField = form.dataset.galleryField;
                    const role = form.dataset.role;
                    const uploadZone = form.querySelector('[data-create-upload-zone]');
                    const uploadInput = form.querySelector('[data-create-upload-input]');
                    const previewsWrapper = form.querySelector('[data-create-upload-previews-wrapper]');
                    const previewsContainer = form.querySelector('[data-create-upload-previews]');
                    const summary = form.querySelector('[data-create-upload-summary]');
                    const errorBox = form.querySelector('[data-create-upload-error]');
                    const submitButton = form.querySelector('[data-gallery-create-submit]');
                    const submitText = form.querySelector('[data-gallery-create-submit-text]');
                    const selectedFiles = [];

                    const showError = (message) => {
                        if (!errorBox) return;
                        errorBox.textContent = message;
                        errorBox.classList.toggle('hidden', !message);
                    };

                    const setBusy = (busy, label = null) => {
                        if (submitButton) submitButton.disabled = busy;
                        if (submitButton) submitButton.classList.toggle('opacity-60', busy);
                        if (submitButton) submitButton.classList.toggle('cursor-wait', busy);
                        if (submitText && label) submitText.textContent = label;
                    };

                    const updateSummary = () => {
                        if (!summary) return;
                        summary.textContent = selectedFiles.length
                            ? `${selectedFiles.length} ${selectedFiles.length === 1 ? 'image' : 'images'} ready`
                            : 'Optional';
                    };

                    const renderPreviews = () => {
                        if (!previewsContainer || !previewsWrapper) return;
                        previewsContainer.innerHTML = '';
                        previewsWrapper.classList.toggle('hidden', selectedFiles.length === 0);

                        selectedFiles.forEach((file, index) => {
                            const previewUrl = URL.createObjectURL(file);
                            const card = document.createElement('div');
                            card.className = 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm';
                            card.dataset.uploadIndex = String(index);
                            card.innerHTML = `
                                <div class="flex gap-4 p-3">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                        <img src="${previewUrl}" alt="" class="h-full w-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900"></p>
                                                <p class="mt-1 text-xs text-gray-500"></p>
                                            </div>
                                            <button type="button" class="text-gray-400 transition hover:text-red-600" data-remove-upload title="Remove upload">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                        <div class="mt-3">
                                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full w-0 rounded-full bg-gray-900 transition-all duration-300" data-progress-bar></div>
                                            </div>
                                            <span class="mt-2 block text-xs font-medium text-gray-500" data-upload-status>Ready</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            card.querySelector('p').textContent = file.name;
                            card.querySelectorAll('p')[1].textContent = `${(file.size / 1024 / 1024).toFixed(1)} MB`;
                            card.querySelector('[data-remove-upload]').addEventListener('click', () => {
                                selectedFiles.splice(index, 1);
                                renderPreviews();
                                updateSummary();
                            });
                            previewsContainer.appendChild(card);
                            card.querySelector('img')?.addEventListener('load', () => URL.revokeObjectURL(previewUrl), { once: true });
                        });
                    };

                    const addFiles = (files) => {
                        showError('');
                        Array.from(files).forEach((file) => {
                            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) return;
                            if (file.size > 10 * 1024 * 1024) return;
                            selectedFiles.push(file);
                        });
                        renderPreviews();
                        updateSummary();
                    };

                    uploadZone?.addEventListener('click', () => uploadInput?.click());
                    uploadZone?.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            uploadInput?.click();
                        }
                    });
                    uploadZone?.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        uploadZone.classList.add('border-gray-800', 'bg-white');
                    });
                    uploadZone?.addEventListener('dragleave', () => {
                        uploadZone.classList.remove('border-gray-800', 'bg-white');
                    });
                    uploadZone?.addEventListener('drop', (event) => {
                        event.preventDefault();
                        uploadZone.classList.remove('border-gray-800', 'bg-white');
                        addFiles(event.dataTransfer.files);
                    });
                    uploadInput?.addEventListener('change', () => {
                        addFiles(uploadInput.files);
                        uploadInput.value = '';
                    });

                    const updateProgress = (fileIndex, progress, status) => {
                        const card = previewsContainer?.querySelector(`[data-upload-index="${fileIndex}"]`);
                        if (!card) return;
                        card.querySelector('[data-progress-bar]').style.width = `${progress}%`;
                        card.querySelector('[data-upload-status]').textContent = status;
                    };

                    const uploadFile = (file, fileIndex, galleryId) => new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('images[]', file);
                        formData.append(galleryField, galleryId);
                        formData.append('is_public', '1');
                        formData.append('is_featured', '0');
                        formData.append('contains_nudity', form.querySelector('[name="contains_nudity"]')?.checked ? '1' : '0');
                        if (role === 'model') {
                            formData.append('is_polaroid', '0');
                        }

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', uploadUrl);
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.upload.addEventListener('progress', (event) => {
                            if (event.lengthComputable) {
                                updateProgress(fileIndex, Math.round((event.loaded / event.total) * 100), `Uploading ${Math.round((event.loaded / event.total) * 100)}%`);
                            }
                        });
                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                updateProgress(fileIndex, 100, 'Uploaded');
                                resolve();
                                return;
                            }

                            updateProgress(fileIndex, 100, 'Upload failed');
                            reject(new Error('One or more images failed to upload.'));
                        };
                        xhr.onerror = () => {
                            updateProgress(fileIndex, 100, 'Upload failed');
                            reject(new Error('One or more images failed to upload.'));
                        };
                        updateProgress(fileIndex, 5, 'Uploading...');
                        xhr.send(formData);
                    });

                    const uploadFiles = async (galleryId) => {
                        let cursor = 0;
                        const workers = Array.from({ length: Math.min(3, selectedFiles.length) }, async () => {
                            while (cursor < selectedFiles.length) {
                                const index = cursor++;
                                await uploadFile(selectedFiles[index], index, galleryId);
                            }
                        });
                        await Promise.all(workers);
                    };

                    form.addEventListener('submit', async (event) => {
                        if (selectedFiles.length === 0) return;

                        event.preventDefault();
                        showError('');
                        setBusy(true, 'Creating gallery...');

                        try {
                            const createData = new FormData(form);
                            const createResponse = await fetch(form.action, {
                                method: 'POST',
                                body: createData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });

                            const createPayload = await createResponse.json();
                            if (!createResponse.ok || !createPayload.gallery_id) {
                                throw new Error(createPayload.message || 'Gallery could not be created.');
                            }

                            setBusy(true, `Uploading ${selectedFiles.length} ${selectedFiles.length === 1 ? 'image' : 'images'}...`);
                            await uploadFiles(createPayload.gallery_id);
                            window.asdfSound?.play('done');
                            window.location.href = createPayload.redirect_url;
                        } catch (error) {
                            showError(error.message || 'Something went wrong while creating the gallery.');
                            setBusy(false, @json($submitLabel));
                        }
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
