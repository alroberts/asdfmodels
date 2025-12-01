<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Portfolio') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('photographers.portfolio.galleries.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition-colors flex items-center gap-2">
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

    <div class="py-6 md:py-12" x-data="portfolioManager()" x-init="init()" @upload-success.window="handleUploadSuccess($event.detail)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 font-medium text-sm text-green-600 bg-green-50 border-2 border-green-500 p-4 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                    <div class="text-2xl md:text-3xl font-bold text-purple-600">{{ $galleries->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Galleries</div>
                </div>
                <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                    <div class="text-2xl md:text-3xl font-bold text-yellow-600">{{ $galleries->sum('images_count') }}</div>
                    <div class="text-sm text-gray-600 mt-1">Total Images</div>
                </div>
                <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                    <div class="text-2xl md:text-3xl font-bold text-green-600">{{ $galleries->where('is_public', true)->sum('images_count') }}</div>
                    <div class="text-sm text-gray-600 mt-1">Public Images</div>
                </div>
                <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                    <div class="text-2xl md:text-3xl font-bold text-blue-600">{{ $galleries->where('is_featured', true)->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Featured Galleries</div>
                </div>
            </div>

            <!-- Galleries Grid -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">My Galleries</h3>
                </div>
                
                @if($galleries->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($galleries as $gallery)
                    <div class="bg-white border-2 border-gray-300 rounded-lg overflow-hidden hover:border-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl group cursor-pointer"
                         onclick="window.location.href='{{ route('photographers.portfolio.galleries.show', $gallery->id) }}'">
                        <!-- Cover Image -->
                        <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                            @if($gallery->cover_image_path)
                                <img src="{{ asset($gallery->cover_image_path) }}" 
                                     alt="{{ $gallery->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @elseif($gallery->images_count > 0)
                                @php
                                    $firstImage = $gallery->images()->orderBy('gallery_image.display_order')->first();
                                @endphp
                                @if($firstImage)
                                    <img src="{{ asset($firstImage->thumbnail_path) }}" 
                                         alt="{{ $gallery->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                        <i class="fas fa-images text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <i class="fas fa-folder text-4xl text-gray-400"></i>
                                </div>
                            @endif
                            
                            <!-- Overlay on Hover -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-200 flex items-center justify-center">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity text-white text-center px-4">
                                    <i class="fas fa-eye text-2xl mb-2"></i>
                                    <p class="text-sm font-medium">View Gallery</p>
                                </div>
                            </div>
                            
                            <!-- Featured Badge -->
                            @if($gallery->is_featured)
                            <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                <i class="fas fa-star"></i> Featured
                            </div>
                            @endif
                            
                            <!-- Image Count Badge -->
                            <div class="absolute bottom-2 left-2 bg-black bg-opacity-70 text-white px-2 py-1 rounded text-xs font-medium">
                                <i class="fas fa-images mr-1"></i>{{ $gallery->images_count }} {{ $gallery->images_count === 1 ? 'image' : 'images' }}
                            </div>
                        </div>
                        
                        <!-- Gallery Info -->
                        <div class="p-4">
                            <h4 class="font-bold text-gray-900 mb-1 line-clamp-1">{{ $gallery->title }}</h4>
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
                    <p class="text-gray-500 text-sm mb-4">Create your first gallery to organize your portfolio images</p>
                    <a href="{{ route('photographers.portfolio.galleries.create') }}" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition-colors">
                        <i class="fas fa-folder-plus mr-2"></i>Create Gallery
                    </a>
                </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Upload Images Modal -->
    <x-modal name="upload-images" maxWidth="4xl">
        <div class="p-6" x-data="uploadManager()" x-init="init()" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Add Photos</h3>
                <button @click="$dispatch('close-modal', 'upload-images')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Drag and Drop Zone -->
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
                <div 
                    class="space-y-4 transition-all duration-300"
                    x-show="selectedFiles.length === 0"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <i 
                        class="fas fa-cloud-upload-alt text-5xl transition-all duration-300" 
                        :class="{
                            'text-gray-800 scale-110 animate-pulse': isDragging,
                            'text-gray-400': !isDragging
                        }"
                    ></i>
                    <div>
                        <p class="text-lg font-semibold text-gray-900 transition-colors duration-300" :class="isDragging ? 'text-gray-800' : 'text-gray-900'">
                            <span class="text-gray-800 underline">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-sm text-gray-600 mt-1">PNG, JPG up to 10MB each</p>
                    </div>
                </div>
            </div>

            <!-- Selected Files Preview -->
            <div 
                x-show="selectedFiles.length > 0" 
                class="mb-6"
                x-transition:enter="ease-out duration-500"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <h4 
                    class="text-lg font-semibold text-gray-900 mb-4"
                    x-transition:enter="ease-out duration-300 delay-100"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    Selected Images
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-2 border-2 border-gray-200 rounded-lg">
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <div 
                            class="relative group"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90 translate-y-4"
                            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                            :style="`transition-delay: ${index * 50}ms`"
                        >
                            <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-300 transform transition-transform duration-200 group-hover:scale-105">
                                <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover">
                            </div>
                            <button 
                                @click.stop="removeFile(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-10"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white text-xs p-1 truncate" x-text="file.name"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Upload Options -->
            <div x-show="selectedFiles.length > 0" class="space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Gallery Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Add to Gallery <span class="text-red-600">*</span>
                        </label>
                        <select x-model="uploadOptions.galleryId" required class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:border-gray-800 focus:ring-0">
                            <option value="">Select a gallery...</option>
                            @foreach($galleries as $gallery)
                                <option value="{{ $gallery->id }}">{{ $gallery->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Required - Photos must be added to at least one gallery</p>
                    </div>

                    <!-- Category -->
                    <div>
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
                    <!-- Model Tagging -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Model in Photo (optional)
                        </label>
                        <select x-model="uploadOptions.modelId" class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:border-gray-800 focus:ring-0">
                            <option value="">None</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" x-model="uploadOptions.isPublic" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Make images public</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" x-model="uploadOptions.isFeatured" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Mark as featured</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" x-model="uploadOptions.containsNudity" class="rounded border-gray-300 text-gray-800 focus:ring-gray-800">
                        <span class="ml-2 text-sm text-gray-700">Images contain nudity</span>
                    </label>
                </div>
            </div>

            <!-- Upload Progress -->
            <div x-show="isUploading" class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Uploading...</span>
                    <span class="text-sm text-gray-600" x-text="uploadProgress + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div 
                        class="bg-gray-800 h-2.5 rounded-full transition-all duration-300"
                        :style="'width: ' + uploadProgress + '%'"
                    ></div>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-text="uploadStatus"></p>
            </div>

            <!-- Error Message -->
            <div x-show="errorMessage" class="mb-6 p-4 bg-red-50 border-2 border-red-500 rounded-lg">
                <p class="text-sm text-red-700" x-text="errorMessage"></p>
            </div>

            <!-- Action Buttons -->
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
                    :disabled="selectedFiles.length === 0 || isUploading || !uploadOptions.galleryId"
                    class="px-6 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <i class="fas fa-upload" x-show="!isUploading"></i>
                    <i class="fas fa-spinner fa-spin" x-show="isUploading"></i>
                    <span x-text="isUploading ? 'Adding Photos...' : 'Add Photos'"></span>
                </button>
            </div>
        </div>
    </x-modal>

    <script>
        function portfolioManager() {
            return {
                init() {
                    // Initialize any portfolio-specific functionality
                },
                handleUploadSuccess(detail) {
                    // Reload page to show new images
                    if (detail && detail.count > 0) {
                        window.location.reload();
                    }
                }
            };
        }

        function uploadManager() {
            return {
                isDragging: false,
                selectedFiles: [],
                isUploading: false,
                uploadProgress: 0,
                uploadStatus: '',
                errorMessage: '',
                uploadOptions: {
                    galleryId: '',
                    category: '',
                    modelId: '',
                    isPublic: true,
                    isFeatured: false,
                    containsNudity: false,
                },
                init() {
                    // Listen for modal close events
                    window.addEventListener('close-modal', (e) => {
                        if (e.detail === 'upload-images') {
                            this.reset();
                        }
                    });
                },
                handleDrop(event) {
                    this.isDragging = false;
                    const files = Array.from(event.dataTransfer.files).filter(file => 
                        file.type.startsWith('image/')
                    );
                    this.addFiles(files);
                },
                handleFiles(event) {
                    const files = Array.from(event.target.files);
                    this.addFiles(files);
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

                    if (!this.uploadOptions.galleryId) {
                        this.errorMessage = 'Please select a gallery to add photos to.';
                        return;
                    }

                    this.isUploading = true;
                    this.uploadProgress = 0;
                    this.errorMessage = '';
                    this.uploadStatus = 'Preparing files...';

                    const formData = new FormData();
                    
                    // Add files
                    this.selectedFiles.forEach((item, index) => {
                        formData.append(`images[${index}]`, item.file);
                    });

                    // Add options
                    if (this.uploadOptions.galleryId) {
                        formData.append('gallery_id', this.uploadOptions.galleryId);
                    }
                    if (this.uploadOptions.category) {
                        formData.append('category', this.uploadOptions.category);
                    }
                    if (this.uploadOptions.modelId) {
                        formData.append('model_id', this.uploadOptions.modelId);
                    }
                    formData.append('is_public', this.uploadOptions.isPublic ? '1' : '0');
                    formData.append('is_featured', this.uploadOptions.isFeatured ? '1' : '0');
                    formData.append('contains_nudity', this.uploadOptions.containsNudity ? '1' : '0');

                    // Get CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    try {
                        this.uploadStatus = 'Uploading images...';
                        
                        const xhr = new XMLHttpRequest();
                        
                        // Track upload progress
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                            }
                        });

                        xhr.addEventListener('load', () => {
                            if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                this.uploadStatus = 'Processing images...';
                                this.uploadProgress = 100;
                                
                                setTimeout(() => {
                                    this.$dispatch('upload-success', response);
                                    this.$dispatch('close-modal', 'upload-images');
                                }, 500);
                            } else {
                                const response = JSON.parse(xhr.responseText);
                                this.errorMessage = response.message || 'Upload failed. Please try again.';
                                this.isUploading = false;
                            }
                        });

                        xhr.addEventListener('error', () => {
                            this.errorMessage = 'Network error. Please check your connection and try again.';
                            this.isUploading = false;
                        });

                        xhr.open('POST', '{{ route("photographers.portfolio.store") }}');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.send(formData);

                    } catch (error) {
                        this.errorMessage = 'An error occurred. Please try again.';
                        this.isUploading = false;
                        console.error('Upload error:', error);
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
                        modelId: '',
                        isPublic: true,
                        isFeatured: false,
                        containsNudity: false,
                    };
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                }
            };
        }
    </script>
</x-app-layout>
