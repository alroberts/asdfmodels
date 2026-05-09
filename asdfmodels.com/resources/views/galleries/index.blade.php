<x-app-layout>
    @php
        $isPhotographer = ($role ?? 'model') === 'photographer';
        $relatedFieldName = $isPhotographer ? 'gallery_id' : 'album_id';
        $galleryLabelField = $isPhotographer ? 'title' : 'name';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Portfolio') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('portfolio.galleries.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition-colors flex items-center gap-2">
                    <i class="fas fa-folder-plus"></i> New Gallery
                </a>
                @if($galleries->count() > 0)
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-images' }))" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-upload"></i> Add Photos
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 md:py-12" x-data="portfolioManager({ initialUploadIntent: @js($initialUploadIntent ?? null), selectedGalleryId: @js($selectedGalleryId ?? null), polaroidCount: @js($polaroidCount ?? 0) })" x-init="init()" @upload-success.window="handleUploadSuccess($event.detail)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 font-medium text-sm text-green-600 bg-green-50 border-2 border-green-500 p-4 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-2 {{ count($stats ?? []) >= 5 ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-4 mb-8">
                @foreach($stats as $stat)
                    <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                        <div class="text-2xl md:text-3xl font-bold {{ $stat['class'] ?? 'text-gray-900' }}">{{ $stat['value'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            @if(!$isPhotographer)
                <div class="mb-6 rounded-xl border {{ ($polaroidCount ?? 0) > 0 ? 'border-gray-200 bg-white' : 'border-amber-200 bg-amber-50' }} p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ ($polaroidCount ?? 0) > 0 ? 'bg-gray-900' : 'bg-amber-500' }} text-white">
                                <i class="fas fa-camera-retro text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Polaroids</h3>
                                @if(($polaroidCount ?? 0) > 0)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $polaroidCount }} {{ $polaroidCount === 1 ? 'polaroid is' : 'polaroids are' }} currently live on your profile.
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-gray-600">
                                        Add a small set of natural, unedited reference images for photographers to review.
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0">
                            <button
                                type="button"
                                @click="{{ ($polaroidCount ?? 0) > 0 ? '$dispatch(\'open-modal\', \'manage-polaroids\')' : 'openUploadModal({ mode: \'polaroids\' })' }}"
                                class="inline-flex items-center justify-center gap-2 rounded-lg {{ ($polaroidCount ?? 0) > 0 ? 'border border-gray-300 bg-white text-gray-800 hover:border-gray-400 hover:bg-gray-50' : 'bg-amber-500 text-white hover:bg-amber-600' }} px-4 py-2.5 text-sm font-semibold transition-colors"
                            >
                                <i class="fas {{ ($polaroidCount ?? 0) > 0 ? 'fa-sliders' : 'fa-plus' }} text-sm"></i>
                                <span>{{ ($polaroidCount ?? 0) > 0 ? 'Manage Polaroids' : 'Upload Polaroids' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">My Galleries</h3>
                </div>

                @if($galleries->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($galleries as $gallery)
                            @php
                                $title = $gallery->{$galleryLabelField} ?? 'Untitled Gallery';
                                $coverPath = $gallery->cover_image_path ?? ($gallery->coverImage?->thumbnail_path ?? null);
                                $firstImage = $gallery->relationLoaded('images') ? $gallery->images->first() : null;
                                $firstImageThumb = $firstImage?->thumbnail_path ?? null;
                                $imageCount = $gallery->images_count ?? $gallery->images->count();
                            @endphp
                            <div class="bg-white border-2 border-gray-300 rounded-lg overflow-hidden hover:border-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl group cursor-pointer"
                                 onclick="window.location.href='{{ route('portfolio.galleries.show', $gallery->id) }}'">
                                <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                    @if($coverPath)
                                        <img src="{{ asset($coverPath) }}"
                                             alt="{{ $title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @elseif($firstImageThumb)
                                        <img src="{{ asset($firstImageThumb) }}"
                                             alt="{{ $title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <i class="fas fa-folder-open text-4xl text-gray-400"></i>
                                        </div>
                                    @endif

                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-200 flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity text-white text-center px-4">
                                            <i class="fas fa-eye text-2xl mb-2"></i>
                                            <p class="text-sm font-medium">View Gallery</p>
                                        </div>
                                    </div>

                                    @if(data_get($gallery, 'is_featured', false))
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                            <i class="fas fa-star"></i> Featured
                                        </div>
                                    @endif

                                    <div class="absolute bottom-2 left-2 bg-black bg-opacity-70 text-white px-2 py-1 rounded text-xs font-medium">
                                        <i class="fas fa-images mr-1"></i>{{ $imageCount }} {{ $imageCount === 1 ? 'image' : 'images' }}
                                    </div>
                                </div>

                                <div class="p-4">
                                    <h4 class="font-bold text-gray-900 mb-1 line-clamp-1">{{ $title }}</h4>
                                    @if($gallery->description)
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ $gallery->description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>
                                            @if($gallery->is_public)
                                                <i class="fas fa-globe text-green-600"></i> Public
                                            @else
                                                <i class="fas fa-lock text-gray-400"></i> Private
                                            @endif
                                        </span>
                                        <span>{{ $gallery->created_at->format('M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-12 text-center">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 text-lg mb-2">No galleries yet</p>
                        <p class="text-gray-500 text-sm mb-4">Create your first gallery to organise your portfolio images.</p>
                        <a href="{{ route('portfolio.galleries.create') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition-colors">
                            <i class="fas fa-folder-plus mr-2"></i>Create Your First Gallery
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-modal name="upload-images" maxWidth="4xl">
        <div class="p-6" x-data="uploadManager()" x-init="init()" @click.stop data-polaroid-upload-target>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Add Photos</h3>
                <button @click="$dispatch('close-modal', 'upload-images')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop($event)"
                @click="$refs.fileInput.click()"
                :class="{
                    'border-gray-800 bg-gray-50 scale-[1.02]': isDragging,
                    'border-gray-300 bg-white': !isDragging
                }"
                class="border-2 border-dashed rounded-lg p-12 text-center cursor-pointer transition-all duration-300 ease-in-out mb-6"
            >
                <input
                    type="file"
                    x-ref="fileInput"
                    @change="handleFiles($event)"
                    multiple
                    accept="image/jpeg,image/jpg,image/png"
                    class="hidden"
                >
                <div class="space-y-4 transition-all duration-300" x-show="selectedFiles.length === 0">
                    <i class="fas fa-cloud-upload-alt text-5xl transition-all duration-300" :class="isDragging ? 'text-gray-800 scale-110 animate-pulse' : 'text-gray-400'"></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">
                            <span class="text-gray-800 underline">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-sm text-gray-600 mt-1">PNG, JPG up to 10MB each</p>
                    </div>
                </div>
            </div>

            <div x-show="selectedFiles.length > 0" class="mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Selected Images</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-2 border-2 border-gray-200 rounded-lg">
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <div class="relative group">
                            <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-300">
                                <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover">
                            </div>
                            <button @click.stop="removeFile(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-10">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white text-xs p-1 truncate" x-text="file.name"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="selectedFiles.length > 0" class="space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-show="!uploadOptions.isPolaroid" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Add to Gallery
                            <span x-show="gallerySelectionRequired && !uploadOptions.isPolaroid" x-cloak class="text-red-600">*</span>
                        </label>
                        <select x-model="uploadOptions.galleryId" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:border-gray-800 focus:ring-0">
                            <option value="">Select a gallery...</option>
                            @foreach($galleries as $gallery)
                                <option value="{{ $gallery->id }}">{{ $gallery->{$galleryLabelField} }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500" x-text="uploadOptions.isPolaroid ? 'Optional for polaroids. Leave blank if these should only live in the polaroid section.' : 'Required - Photos should be added to a gallery for easier management.'"></p>
                    </div>

                    <div x-show="!uploadOptions.isPolaroid" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category (optional)
                        </label>
                        <select x-model="uploadOptions.category" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:border-gray-800 focus:ring-0">
                            <option value="">None</option>
                            <option value="fashion">Fashion</option>
                            <option value="portrait">Portrait</option>
                            <option value="commercial">Commercial</option>
                            <option value="wedding">Wedding</option>
                            <option value="editorial">Editorial</option>
                            <option value="artistic">Artistic</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-show="!uploadOptions.isPolaroid" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $relatedLabel }}
                        </label>
                        <select x-model="uploadOptions.relatedEntityId" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:border-gray-800 focus:ring-0">
                            <option value="">None</option>
                            @foreach($relatedEntities as $entity)
                                <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center" x-show="!uploadOptions.isPolaroid" x-cloak>
                        <input type="checkbox" x-model="uploadOptions.isPublic" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Make images public</span>
                    </label>
                    <label class="flex items-center" x-show="!uploadOptions.isPolaroid" x-cloak>
                        <input type="checkbox" x-model="uploadOptions.isFeatured" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Mark as featured</span>
                    </label>
                    @if($supportsPolaroids)
                        <label class="flex items-center" x-show="!uploadOptions.isPolaroid" x-cloak>
                            <input type="checkbox" x-model="uploadOptions.isPolaroid" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                            <span class="ml-2 text-sm text-gray-700">Mark as polaroid</span>
                        </label>
                    @endif
                    <label class="flex items-center" x-show="!uploadOptions.isPolaroid" x-cloak>
                        <input type="checkbox" x-model="uploadOptions.containsNudity" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Images contain nudity</span>
                    </label>
                </div>
            </div>

            <div x-show="isUploading" class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Uploading...</span>
                    <span class="text-sm text-gray-600" x-text="uploadProgress + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-gray-800 h-2.5 rounded-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-text="uploadStatus"></p>
            </div>

            <div x-show="errorMessage" class="mb-6 p-4 bg-red-50 border-2 border-red-500 rounded-lg">
                <p class="text-sm text-red-700" x-text="errorMessage"></p>
            </div>

            <div x-show="uploadOptions.isPolaroid" x-cloak class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-amber-500 text-white">
                        <i class="fas fa-camera-retro text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-amber-900">Polaroid Mode</h4>
                            <p class="mt-1 text-sm text-amber-800">These uploads will be saved as polaroids and surfaced in the model’s polaroid section on the public profile.</p>
                            <p class="mt-2 text-xs font-medium uppercase tracking-wide text-amber-700">Saved as public polaroids automatically.</p>
                        </div>
                    </div>
                </div>

            <div class="flex items-center justify-end space-x-4">
                <button
                    @click="$dispatch('close-modal', 'upload-images')"
                    :disabled="isUploading"
                    class="px-4 py-2 text-gray-700 border-2 border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Cancel
                </button>
                <button
                    @click="uploadFiles()"
                    :disabled="selectedFiles.length === 0 || isUploading || (gallerySelectionRequired && !uploadOptions.isPolaroid && !uploadOptions.galleryId)"
                    class="px-6 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <i class="fas fa-upload" x-show="!isUploading"></i>
                    <i class="fas fa-spinner fa-spin" x-show="isUploading"></i>
                    <span x-text="isUploading ? 'Adding Photos...' : 'Add Photos'"></span>
                </button>
            </div>
        </div>
    </x-modal>

    @if(!$isPhotographer)
        <x-polaroid-manager-modal :polaroids="$polaroids ?? collect()" :polaroid-label-options="$polaroidLabelOptions ?? []" />
    @endif

    <script>
        function uploadManager() {
            return {
                isDragging: false,
                selectedFiles: [],
                isUploading: false,
                uploadProgress: 0,
                uploadStatus: '',
                errorMessage: '',
                gallerySelectionRequired: @json($uploadGalleryRequired),
                role: @json($role),
                polaroidCount: config.polaroidCount || 0,
                galleryFieldName: @json($relatedFieldName),
                relatedFieldName: @json($relatedField),
                uploadOptions: {
                    galleryId: '',
                    category: '',
                    relatedEntityId: '',
                    isPublic: true,
                    isFeatured: false,
                    isPolaroid: false,
                    containsNudity: false,
                },
                init() {
                    window.addEventListener('close-modal', (e) => {
                        if (e.detail === 'upload-images') {
                            this.reset();
                        }
                    });

                    window.addEventListener('configure-upload-modal', (event) => {
                        const detail = event.detail || {};
                        this.uploadOptions.isPolaroid = detail.mode === 'polaroids';
                        this.uploadOptions.galleryId = detail.mode === 'polaroids' ? '' : (detail.galleryId ? String(detail.galleryId) : '');
                        this.uploadOptions.category = detail.mode === 'polaroids' ? '' : this.uploadOptions.category;
                        this.uploadOptions.isFeatured = detail.mode === 'polaroids' ? false : this.uploadOptions.isFeatured;
                        this.uploadOptions.containsNudity = false;
                    });

                    if (new URLSearchParams(window.location.search).get('manage') === 'polaroids' && this.role !== 'photographer') {
                        this.$nextTick(() => {
                            if (this.polaroidCount > 0) {
                                this.$dispatch('open-modal', 'manage-polaroids');
                            } else {
                                this.openUploadModal({ mode: 'polaroids' });
                            }
                        });
                    }
                },
                handleDrop(event) {
                    this.isDragging = false;
                    const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'));
                    this.addFiles(files);
                },
                handleFiles(event) {
                    this.addFiles(Array.from(event.target.files));
                },
                addFiles(files) {
                    files.forEach(file => {
                        if (file.size > 10 * 1024 * 1024) {
                            this.errorMessage = `${file.name} is too large. Maximum size is 10MB.`;
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.selectedFiles.push({
                                file: file,
                                name: file.name,
                                preview: e.target.result,
                                size: file.size
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                },
                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                },
                async uploadFiles() {
                    if (this.selectedFiles.length === 0) {
                        this.errorMessage = 'Please select at least one photo.';
                        return;
                    }

                    if (this.gallerySelectionRequired && !this.uploadOptions.isPolaroid && !this.uploadOptions.galleryId) {
                        this.errorMessage = 'Please select a gallery to add photos to.';
                        return;
                    }

                    this.isUploading = true;
                    this.uploadProgress = 0;
                    this.errorMessage = '';
                    this.uploadStatus = 'Preparing files...';

                    const formData = new FormData();

                    this.selectedFiles.forEach((item, index) => {
                        formData.append(`images[${index}]`, item.file);
                    });

                    if (this.uploadOptions.galleryId) {
                        formData.append(this.galleryFieldName, this.uploadOptions.galleryId);
                    }
                    if (!this.uploadOptions.isPolaroid && this.uploadOptions.category) {
                        formData.append('category', this.uploadOptions.category);
                    }
                    if (this.uploadOptions.relatedEntityId) {
                        formData.append(this.relatedFieldName, this.uploadOptions.relatedEntityId);
                    }

                    formData.append('is_public', this.uploadOptions.isPublic ? '1' : '0');
                    formData.append('is_featured', this.uploadOptions.isPolaroid ? '0' : (this.uploadOptions.isFeatured ? '1' : '0'));
                    formData.append('contains_nudity', '0');
                    if (this.role === 'model') {
                        formData.append('is_polaroid', this.uploadOptions.isPolaroid ? '1' : '0');
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        this.uploadStatus = 'Uploading images...';

                        const xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                            }
                        });

                        xhr.addEventListener('load', () => {
                            const response = xhr.responseText ? JSON.parse(xhr.responseText) : {};
                            if (xhr.status >= 200 && xhr.status < 300) {
                                this.uploadStatus = 'Processing images...';
                                this.uploadProgress = 100;
                                setTimeout(() => {
                                    this.$dispatch('upload-success', response);
                                    this.$dispatch('close-modal', 'upload-images');
                                }, 500);
                            } else {
                                this.errorMessage = response.message || 'Upload failed. Please try again.';
                                this.isUploading = false;
                            }
                        });

                        xhr.addEventListener('error', () => {
                            this.errorMessage = 'Network error. Please check your connection and try again.';
                            this.isUploading = false;
                        });

                        xhr.open('POST', '{{ route("portfolio.store") }}');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.send(formData);
                    } catch (error) {
                        this.errorMessage = 'An error occurred. Please try again.';
                        this.isUploading = false;
                    }
                },
                reset() {
                    this.selectedFiles = [];
                    this.isUploading = false;
                    this.uploadProgress = 0;
                    this.uploadStatus = '';
                    this.errorMessage = '';
                    this.uploadOptions = {
                        galleryId: '',
                        category: '',
                        relatedEntityId: '',
                        isPublic: true,
                        isFeatured: false,
                        isPolaroid: false,
                        containsNudity: false,
                    };
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                }
            };
        }

        function portfolioManager(config = {}) {
            return {
                initialUploadIntent: config.initialUploadIntent,
                selectedGalleryId: config.selectedGalleryId,
                polaroidLabels: config.polaroidLabels || {},
                savedPolaroidLabels: { ...(config.polaroidLabels || {}) },
                polaroidLabelOptions: config.polaroidLabelOptions || {},
                savingPolaroidLabels: false,
                polaroidToast: '',
                polaroidToastType: 'success',
                openUploadModal(options = {}) {
                    window.dispatchEvent(new CustomEvent('configure-upload-modal', {
                        detail: {
                            mode: options.mode || 'images',
                            galleryId: options.galleryId || ''
                        }
                    }));
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upload-images' }));
                },
                init() {
                    if (this.initialUploadIntent) {
                        setTimeout(() => {
                            this.openUploadModal({
                                mode: this.initialUploadIntent,
                                galleryId: this.selectedGalleryId || ''
                            });
                        }, 120);
                    }
                },
                polaroidLabelText(value) {
                    return this.polaroidLabelOptions[value] || '';
                },
                isPolaroidLabelDirty(imageId) {
                    const key = String(imageId);
                    return (this.polaroidLabels[key] || '') !== (this.savedPolaroidLabels[key] || '');
                },
                hasPolaroidLabelChanges() {
                    return Object.keys(this.polaroidLabels).some((imageId) => this.isPolaroidLabelDirty(imageId));
                },
                showPolaroidToast(message, type = 'success') {
                    this.polaroidToast = message;
                    this.polaroidToastType = type;

                    setTimeout(() => {
                        if (this.polaroidToast === message) {
                            this.polaroidToast = '';
                        }
                    }, 2500);
                },
                async savePolaroidLabels() {
                    if (!this.hasPolaroidLabelChanges()) {
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    this.savingPolaroidLabels = true;

                    try {
                        const response = await fetch(`{{ route('portfolio.polaroids.labels.update') }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                labels: this.polaroidLabels
                            })
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(payload.message || 'Failed to update polaroid labels.');
                        }

                        this.savedPolaroidLabels = { ...this.polaroidLabels };
                        this.showPolaroidToast(payload.message || 'Polaroid labels saved.');
                    } catch (error) {
                        this.showPolaroidToast(error.message || 'Failed to update polaroid labels.', 'error');
                    } finally {
                        this.savingPolaroidLabels = false;
                    }
                },
                async deletePolaroid(imageId) {
                    if (!confirm('Delete this polaroid? This action cannot be undone.')) {
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(`{{ url('/portfolio') }}/${imageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to delete polaroid.');
                    }
                },
                handleUploadSuccess(detail) {
                    if (detail && detail.count > 0) {
                        window.location.reload();
                    }
                }
            };
        }
    </script>
</x-app-layout>
