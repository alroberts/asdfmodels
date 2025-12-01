@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6/dist/dropzone.css">
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $gallery->title }}
                </h2>
                @if($gallery->description)
                    <p class="text-sm text-gray-600 mt-1">{{ $gallery->description }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('photographers.portfolio.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Portfolio
                </a>
                <a href="{{ route('photographers.portfolio.galleries.edit', $gallery->id) }}" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit Gallery
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Gallery Info -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center md:text-left">
                        <div class="text-2xl font-bold text-gray-900">{{ $gallery->images->count() }}</div>
                        <div class="text-sm text-gray-600">{{ $gallery->images->count() === 1 ? 'Image' : 'Images' }}</div>
                    </div>
                    <div class="text-center md:text-left">
                        <div class="text-sm font-semibold text-gray-900 capitalize">{{ $gallery->visibility }}</div>
                        <div class="text-sm text-gray-600">Visibility</div>
                    </div>
                    <div class="text-center md:text-left">
                        <div class="text-sm font-semibold text-gray-900 capitalize">{{ $gallery->status }}</div>
                        <div class="text-sm text-gray-600">Status</div>
                    </div>
                </div>
            </div>

            <!-- Images Grid with SortableJS -->
            @if($gallery->images->count() > 0)
                <div 
                x-data="galleryManager({{ $gallery->id }})"
                x-init="initSortable(); justifyGrid(); window.addEventListener('resize', () => { justifyGrid(); });"
                    class="justified-grid relative"
                    id="gallery-images-grid"
                    x-ref="gridContainer"
                >
                    @foreach($gallery->images as $image)
                        <div 
                            class="relative overflow-hidden rounded-lg border-2 border-gray-200 hover:border-gray-800 transition-all cursor-move group sortable-item justified-image-item"
                            data-image-id="{{ $image->id }}"
                        >
                            <img src="{{ asset($image->full_path ?? $image->thumbnail_path) }}" 
                                 alt="{{ $image->title ?? 'Image' }}"
                                 class="justified-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 pointer-events-none"
                                 onload="this.setAttribute('data-aspect-ratio', this.naturalWidth / this.naturalHeight); window.dispatchEvent(new Event('resize'));"
                                 data-aspect-ratio="1">
                            <div class="absolute top-2 left-2 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-grip-vertical"></i> Drag to reorder
                            </div>
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
                        </div>
                    @endforeach
                </div>
                
                <!-- Add More Images Dropzone -->
                <div class="mt-6">
                    <div 
                        id="add-images-dropzone"
                        class="dropzone bg-white rounded-xl shadow-lg border-2 border-dashed border-gray-200 p-8 text-center"
                    ></div>
                </div>
            @else
                <!-- Dropzone.js Upload Area -->
                <div 
                    id="gallery-dropzone"
                    class="dropzone bg-white rounded-xl shadow-lg border-2 border-dashed border-gray-200 p-12 text-center"
                ></div>

            @endif
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div id="editImageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="closeEditModal(event)">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 max-w-5xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div id="editModalLoading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                <p class="mt-4 text-gray-600">Loading image data...</p>
            </div>
            <div id="editModalContent" class="hidden">
                <form id="editImageForm" onsubmit="submitEditForm(event)">
                    <!-- Header Section -->
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
                            <!-- Left Column: Image Preview -->
                            <div>
                                <img id="editModalImage" src="" alt="Image" class="w-full h-auto rounded-lg border-2 border-gray-200 object-cover">
                            </div>

                            <!-- Right Column: Form Fields -->
                            <div class="space-y-6">
                                <!-- Image Title -->
                                <div class="space-y-2">
                                    <label for="edit_title" class="text-sm font-semibold text-gray-900">Title (optional)</label>
                                    <input 
                                        type="text" 
                                        id="edit_title" 
                                        name="title" 
                                        class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200" 
                                        placeholder="Enter image title"
                                    >
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <label for="edit_description" class="text-sm font-semibold text-gray-900">Description (optional)</label>
                                    <textarea 
                                        id="edit_description" 
                                        name="description" 
                                        rows="4" 
                                        class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200 resize-none"
                                        placeholder="Describe your image (optional)"
                                    ></textarea>
                                </div>

                                <!-- Models in Photo (Tag Input) -->
                                <div class="space-y-2">
                                    <label for="edit_models" class="text-sm font-semibold text-gray-900">Models in Photo (optional)</label>
                                    <div id="edit_models_container" class="border-2 border-gray-300 rounded-lg px-4 py-2 min-h-[48px] focus-within:border-gray-800 focus-within:ring-2 focus-within:ring-gray-800 focus-within:ring-opacity-20 transition-all duration-200 flex flex-wrap gap-2 items-center">
                                        <input 
                                            type="text" 
                                            id="edit_models_input" 
                                            class="flex-1 min-w-[120px] outline-none text-gray-900 placeholder-gray-400"
                                            placeholder="Type @username and press Enter"
                                            onkeydown="handleModelTagInput(event)"
                                        >
                                    </div>
                                    <input type="hidden" id="edit_models" name="models" value="">
                                    <p class="text-xs text-gray-500 mt-1">Type @username and press Enter to add models</p>
                                </div>

                                <!-- Shot Date -->
                                <div class="space-y-2">
                                    <label for="edit_shot_date" class="text-sm font-semibold text-gray-900">Shot Date (optional)</label>
                                    <input 
                                        type="date" 
                                        id="edit_shot_date" 
                                        name="shot_date" 
                                        class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200"
                                    >
                                </div>

                                <!-- Image Settings -->
                                <div class="border-t border-gray-200 pt-6 space-y-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-4">Image Settings</h3>
                                    </div>

                                    <!-- Cover Image -->
                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                        <label class="flex items-center group cursor-pointer" x-data="{ checked: false }" x-init="checked = document.getElementById('edit_is_cover')?.checked || false">
                                            <div class="relative">
                                                <input 
                                                    type="checkbox" 
                                                    id="edit_is_cover" 
                                                    name="is_cover" 
                                                    value="1" 
                                                    class="sr-only"
                                                    x-model="checked"
                                                >
                                                <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="checked ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                    <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="checked ? 'opacity-100' : 'opacity-0'">
                                                        <path d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Cover Image</span>
                                        </label>
                                        <p class="text-xs text-gray-500 mt-2 ml-8">Set as the gallery cover image</p>
                                    </div>

                                    <!-- NSFW Content -->
                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                        <label class="flex items-center group cursor-pointer" x-data="{ checked: false }" x-init="checked = document.getElementById('edit_contains_nudity')?.checked || false">
                                            <div class="relative">
                                                <input 
                                                    type="checkbox" 
                                                    id="edit_contains_nudity" 
                                                    name="contains_nudity" 
                                                    value="1" 
                                                    class="sr-only"
                                                    x-model="checked"
                                                >
                                                <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="checked ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                    <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="checked ? 'opacity-100' : 'opacity-0'">
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

                    <!-- Actions Footer -->
                    <div class="bg-gray-50 border-t border-gray-200 px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <button 
                            type="button" 
                            onclick="deleteImageFromModal(); event.stopPropagation();" 
                            class="px-6 py-2.5 border-2 border-red-300 text-red-700 rounded-lg font-medium hover:bg-red-50 hover:border-red-400 transition-all duration-200 flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-trash"></i>
                            Delete Image
                        </button>
                        <div class="flex gap-3">
                            <button 
                                type="button" 
                                onclick="closeEditModal()" 
                                class="px-6 py-2.5 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-white hover:border-gray-400 transition-all duration-200 text-center"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 active:bg-gray-950 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const galleryId = {{ $gallery->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const uploadUrl = '{{ route("photographers.portfolio.store") }}';
            const reorderUrl = '{{ route("photographers.portfolio.reorder") }}';
            
            // Initialize Dropzone for empty gallery
            const emptyDropzone = document.getElementById('gallery-dropzone');
            if (emptyDropzone) {
                const dz = new Dropzone(emptyDropzone, {
                    url: uploadUrl,
                    paramName: 'images',
                    maxFilesize: 10, // MB
                    acceptedFiles: 'image/jpeg,image/jpg,image/png',
                    addRemoveLinks: false,
                    dictDefaultMessage: '<i class="fas fa-cloud-upload-alt text-6xl text-gray-300 mb-4"></i><p class="text-lg font-semibold text-gray-900">Click to upload or drag and drop</p><p class="text-sm text-gray-600 mt-1">PNG, JPG up to 10MB each</p>',
                    dictFileTooBig: 'File is too large. Max filesize: 10MB.',
                    dictInvalidFileType: 'Invalid file type. Only JPEG and PNG allowed.',
                    parallelUploads: 1,
                    uploadMultiple: true,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    previewTemplate: `
                        <div class="dz-preview dz-file-preview">
                            <div class="dz-image">
                                <img data-dz-thumbnail />
                            </div>
                            <div class="dz-details">
                                <div class="dz-size"><span data-dz-size></span></div>
                                <div class="dz-filename"><span data-dz-name></span></div>
                            </div>
                            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                            <div class="dz-error-message"><span data-dz-errormessage></span></div>
                            <div class="dz-success-mark">
                                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <title>Check</title>
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.3679711 11.9306128,25.9283877 C10.3701962,27.4888043 10.3701962,30.0226002 11.9306128,31.5830168 L21.7071068,41.3595208 C23.2675234,42.9199374 25.8013193,42.9199374 27.3617359,41.3595208 L43.0693872,25.6518695 C44.6298038,24.0914529 44.6298038,21.557657 43.0693872,19.9972404 C41.5089706,18.4368238 38.9751747,18.4368238 37.4147581,19.9972404 L23.5,33.9119989 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    `,
                    init: function() {
                        const dropzoneInstance = this;
                        let totalFiles = 0;
                        let completedFiles = 0;
                        let hasErrors = false;
                        
                        this.on('addedfiles', function(files) {
                            totalFiles = files.length;
                            completedFiles = 0;
                            hasErrors = false;
                        });
                        
                        this.on('sendingmultiple', function(files, xhr, formData) {
                            formData.append('gallery_id', galleryId);
                            formData.append('is_public', '1');
                            formData.append('is_featured', '0');
                            formData.append('contains_nudity', '0');
                        });
                        
                        this.on('success', function(file, response) {
                            completedFiles++;
                            console.log(`Upload successful: ${file.name} (${completedFiles}/${totalFiles})`);
                            
                            // Check if all files are done
                            if (completedFiles >= totalFiles) {
                                console.log('All files uploaded successfully, reloading...');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        });
                        
                        this.on('error', function(file, message) {
                            completedFiles++;
                            hasErrors = true;
                            console.error(`Upload error for ${file.name}:`, message);
                            
                            // Check if all files are done (even with errors)
                            if (completedFiles >= totalFiles) {
                                if (hasErrors) {
                                    alert('Some files failed to upload. Please check the console for details.');
                                }
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        });
                        
                        this.on('errormultiple', function(files, message) {
                            console.error('Batch upload error:', message);
                        });
                    }
                });
            }
            
            // Initialize Dropzone for adding more images (when gallery has images)
            const addImagesDropzone = document.getElementById('add-images-dropzone');
            if (addImagesDropzone) {
                const dz = new Dropzone(addImagesDropzone, {
                    url: uploadUrl,
                    paramName: 'images',
                    maxFilesize: 10,
                    acceptedFiles: 'image/jpeg,image/jpg,image/png',
                    addRemoveLinks: false,
                    dictDefaultMessage: '<i class="fas fa-plus-circle text-4xl text-gray-400 mb-2"></i><p class="text-sm font-semibold text-gray-700">Add more images</p><p class="text-xs text-gray-500 mt-1">Drag files here or click to browse</p>',
                    dictFileTooBig: 'File is too large. Max filesize: 10MB.',
                    dictInvalidFileType: 'Invalid file type. Only JPEG and PNG allowed.',
                    parallelUploads: 1,
                    uploadMultiple: true,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    previewTemplate: `
                        <div class="dz-preview dz-file-preview">
                            <div class="dz-image">
                                <img data-dz-thumbnail />
                            </div>
                            <div class="dz-details">
                                <div class="dz-size"><span data-dz-size></span></div>
                                <div class="dz-filename"><span data-dz-name></span></div>
                            </div>
                            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                            <div class="dz-error-message"><span data-dz-errormessage></span></div>
                            <div class="dz-success-mark">
                                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <title>Check</title>
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.3679711 11.9306128,25.9283877 C10.3701962,27.4888043 10.3701962,30.0226002 11.9306128,31.5830168 L21.7071068,41.3595208 C23.2675234,42.9199374 25.8013193,42.9199374 27.3617359,41.3595208 L43.0693872,25.6518695 C44.6298038,24.0914529 44.6298038,21.557657 43.0693872,19.9972404 C41.5089706,18.4368238 38.9751747,18.4368238 37.4147581,19.9972404 L23.5,33.9119989 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    `,
                    init: function() {
                        let totalFiles = 0;
                        let completedFiles = 0;
                        let hasErrors = false;
                        
                        this.on('addedfiles', function(files) {
                            totalFiles = files.length;
                            completedFiles = 0;
                            hasErrors = false;
                        });
                        
                        this.on('sendingmultiple', function(files, xhr, formData) {
                            formData.append('gallery_id', galleryId);
                            formData.append('is_public', '1');
                            formData.append('is_featured', '0');
                            formData.append('contains_nudity', '0');
                        });
                        
                        this.on('success', function(file, response) {
                            completedFiles++;
                            console.log(`Upload successful: ${file.name} (${completedFiles}/${totalFiles})`);
                            
                            // Check if all files are done
                            if (completedFiles >= totalFiles) {
                                console.log('All files uploaded successfully, reloading...');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        });
                        
                        this.on('error', function(file, message) {
                            completedFiles++;
                            hasErrors = true;
                            console.error(`Upload error for ${file.name}:`, message);
                            
                            // Check if all files are done (even with errors)
                            if (completedFiles >= totalFiles) {
                                if (hasErrors) {
                                    alert('Some files failed to upload. Please check the console for details.');
                                }
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        });
                        
                        this.on('errormultiple', function(files, message) {
                            console.error('Batch upload error:', message);
                        });
                    }
                });
            }
        });
        
        // Global delete function
        // Edit Modal Functions
        let currentEditImageId = null;
        let modelTags = [];

        // Model Tag Input Functions
        function handleModelTagInput(event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                const input = event.target;
                const value = input.value.trim();
                
                if (value && value.startsWith('@')) {
                    const username = value.substring(1);
                    if (username && !modelTags.includes('@' + username)) {
                        addModelTag('@' + username);
                        input.value = '';
                    }
                }
            }
        }

        function addModelTag(tag) {
            if (!modelTags.includes(tag)) {
                modelTags.push(tag);
                renderModelTags();
            }
        }

        function removeModelTag(tag) {
            modelTags = modelTags.filter(t => t !== tag);
            renderModelTags();
        }

        function renderModelTags() {
            const container = document.getElementById('edit_models_container');
            const input = document.getElementById('edit_models_input');
            const hiddenInput = document.getElementById('edit_models');
            
            // Clear existing tags (except input)
            const existingTags = container.querySelectorAll('.model-tag');
            existingTags.forEach(tag => tag.remove());
            
            // Add tag elements
            modelTags.forEach(tag => {
                const tagElement = document.createElement('span');
                tagElement.className = 'model-tag inline-flex items-center gap-1 bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" onclick="removeModelTag('${tag}')" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                container.insertBefore(tagElement, input);
            });
            
            // Update hidden input
            hiddenInput.value = JSON.stringify(modelTags);
        }

        function populateModelTags(tags) {
            modelTags = tags || [];
            renderModelTags();
        }

        function deleteImageFromModal() {
            if (!currentEditImageId) return;
            
            if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const deleteUrl = '{{ url("/photographers/portfolio") }}/' + currentEditImageId;
            
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    closeEditModal();
                    window.location.reload();
                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Unknown error');
                    });
                }
            })
            .catch(error => {
                console.error('Error deleting image:', error);
                alert('Failed to delete image: ' + error.message);
            });
        }

        async function openEditModal(imageId, imageSrc) {
            currentEditImageId = imageId;
            const modal = document.getElementById('editImageModal');
            const loading = document.getElementById('editModalLoading');
            const content = document.getElementById('editModalContent');
            
            // Show modal and loading state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            
            // Set image src immediately (before loading form data)
            if (imageSrc) {
                document.getElementById('editModalImage').src = imageSrc;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            try {
                // Fetch image data
                const response = await fetch(`{{ url('/photographers/portfolio') }}/${imageId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load image data');
                }
                
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extract image data from the form
                const form = doc.querySelector('form');
                if (!form) {
                    throw new Error('Form not found');
                }
                
                // Get image src from the form area (more specific selector)
                const formImg = form.parentElement?.querySelector('img') || doc.querySelector('.mb-6 img');
                if (formImg && !imageSrc) {
                    document.getElementById('editModalImage').src = formImg.src;
                }
                
                // Populate form fields
                document.getElementById('edit_title').value = form.querySelector('#title')?.value || '';
                document.getElementById('edit_description').value = form.querySelector('#description')?.value || '';
                document.getElementById('edit_shot_date').value = form.querySelector('#shot_date')?.value || '';
                
                // Check if this image is the cover image for the gallery
                const galleryCoverPath = '{{ $gallery->cover_image_path ?? "" }}';
                const currentImageFullPath = form.querySelector('img')?.src || '';
                const currentImagePath = currentImageFullPath.split('/').pop() || '';
                const coverPathOnly = galleryCoverPath ? galleryCoverPath.split('/').pop() : '';
                document.getElementById('edit_is_cover').checked = (coverPathOnly && currentImagePath && coverPathOnly === currentImagePath);
                
                document.getElementById('edit_contains_nudity').checked = form.querySelector('#contains_nudity')?.checked || false;
                
                // Populate models as tags (from tags field or model_id)
                const modelTags = [];
                const modelSelect = form.querySelector('#model_id');
                if (modelSelect && modelSelect.value) {
                    const selectedModel = modelSelect.options[modelSelect.selectedIndex];
                    if (selectedModel && selectedModel.textContent) {
                        modelTags.push(selectedModel.textContent);
                    }
                }
                // Also check if there are tags in the image data
                // For now, we'll use the model_id approach and extend later
                populateModelTags(modelTags);
                
                // Show content
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                
                // Initialize Alpine.js for checkboxes if available
                if (window.Alpine) {
                    window.Alpine.initTree(content);
                }
            } catch (error) {
                console.error('Error loading image data:', error);
                alert('Failed to load image data. Please try again.');
                closeEditModal();
            }
        }

        function closeEditModal(event) {
            if (event && event.target.id !== 'editImageModal' && !event.target.closest('.bg-white')) {
                return;
            }
            const modal = document.getElementById('editImageModal');
            modal.classList.add('hidden');
            currentEditImageId = null;
        }

        async function submitEditForm(event) {
            event.preventDefault();
            
            if (!currentEditImageId) return;
            
            const form = document.getElementById('editImageForm');
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Convert FormData to object for JSON
            const data = {
                contains_nudity: document.getElementById('edit_contains_nudity').checked,
                is_cover: document.getElementById('edit_is_cover').checked,
            };
            
            // Add models as tags
            const modelsInput = document.getElementById('edit_models');
            if (modelsInput && modelsInput.value) {
                try {
                    const tags = JSON.parse(modelsInput.value);
                    data.tags = tags;
                } catch (e) {
                    data.tags = [];
                }
            } else {
                data.tags = [];
            }
            
            // Add gallery_id for cover image handling
            data.gallery_id = {{ $gallery->id }};
            
            formData.forEach((value, key) => {
                if (key !== 'contains_nudity' && key !== 'is_cover' && key !== 'models') {
                    data[key] = value || null;
                }
            });
            
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';
            
            try {
                const response = await fetch(`{{ url('/photographers/portfolio') }}/${currentEditImageId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    closeEditModal();
                    // Optionally reload the page to show updated data
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert('Failed to update image: ' + (errorData.message || 'Unknown error'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            } catch (error) {
                console.error('Error updating image:', error);
                alert('An error occurred while updating the image.');
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }

        function deleteImage(imageId, buttonElement) {
            if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const imageElement = buttonElement.closest('.sortable-item');
            
            // Disable button and show loading state
            buttonElement.disabled = true;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';

            const deleteUrl = '{{ url("/photographers/portfolio") }}/' + imageId;
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Remove the image element with animation
                    imageElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        imageElement.remove();
                        // Recalculate justified grid
                        const gridContainer = document.getElementById('gallery-images-grid');
                        if (gridContainer && window.Alpine) {
                            const alpineData = Alpine.$data(gridContainer);
                            if (alpineData && alpineData.justifyGrid) {
                                alpineData.justifyGrid();
                            }
                        }
                        // Reload page to update image count
                        window.location.reload();
                    }, 300);
                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Unknown error');
                    });
                }
            })
            .catch(error => {
                console.error('Error deleting image:', error);
                alert('Error deleting image: ' + error.message);
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-trash text-sm"></i>';
            });
        }
        
        // Alpine.js component for gallery management with SortableJS
        function galleryManager(galleryId) {
            return {
                galleryId: galleryId,
                sortable: null,
                initSortable() {
                    this.$nextTick(() => {
                        const grid = document.getElementById('gallery-images-grid');
                        if (!grid || !window.Sortable) return;
                        
                        this.sortable = new Sortable(grid, {
                            animation: 150,
                            ghostClass: 'opacity-50',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'opacity-30',
                            onEnd: (evt) => {
                                this.reorderImages(evt.oldIndex, evt.newIndex);
                                this.justifyGrid();
                            }
                        });
                    });
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
                                // Finalize current row
                                const actualRowHeight = (containerWidth - (currentRow.length - 1) * gap) / currentRow.reduce((sum, i) => sum + i.aspectRatio, 0);
                                currentRow.forEach((rowItem) => {
                                    const width = actualRowHeight * rowItem.aspectRatio;
                                    rowItem.element.style.width = width + 'px';
                                    rowItem.element.style.height = actualRowHeight + 'px';
                                    rowItem.img.style.height = actualRowHeight + 'px';
                                    rowItem.img.style.width = 'auto';
                                });
                                
                                // Start new row
                                currentRow = [];
                                currentRowWidth = 0;
                            }
                            
                            currentRow.push({
                                element: item,
                                img: img,
                                aspectRatio: aspectRatio
                            });
                            currentRowWidth += itemWidth;
                        });
                        
                        // Finalize last row
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
                async reorderImages(oldIndex, newIndex) {
                    if (oldIndex === newIndex) return;
                    
                    const items = document.querySelectorAll('.sortable-item');
                    const imageIds = Array.from(items).map(item => 
                        parseInt(item.getAttribute('data-image-id'))
                    );
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    try {
                        const response = await fetch('{{ route("photographers.portfolio.reorder") }}', {
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
                        
                        if (!response.ok) {
                            console.error('Failed to reorder images');
                        }
                    } catch (error) {
                        console.error('Error reordering images:', error);
                    }
                }
            };
        }
    </script>
    
    <style>
        /* Custom Dropzone styling */
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
        
        /* Dropzone preview styling */
        .dropzone .dz-preview {
            display: inline-block;
            margin: 8px;
            vertical-align: top;
            min-width: 200px;
            max-width: 300px;
        }
        .dropzone .dz-preview .dz-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 8px;
        }
        .dropzone .dz-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .dropzone .dz-preview .dz-details {
            padding: 8px;
            font-size: 12px;
            color: #666;
        }
        .dropzone .dz-preview .dz-progress {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .dropzone .dz-preview .dz-progress .dz-upload {
            display: block;
            height: 100%;
            background: #1f2937;
            width: 0%;
            transition: width 0.3s;
        }
        .dropzone .dz-preview .dz-success-mark,
        .dropzone .dz-preview .dz-error-mark {
            display: none;
        }
        .dropzone .dz-preview.dz-success .dz-success-mark {
            display: block;
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
        }
        .dropzone .dz-preview.dz-error .dz-error-mark {
            display: block;
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            color: #dc2626;
        }
        
        /* Justified Grid styling */
        .justified-grid {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .justified-image-item {
            flex-shrink: 0;
        }
        .justified-img {
            display: block;
        }
        
        /* SortableJS styling */
        .sortable-item {
            cursor: move;
        }
        .sortable-item:active {
            cursor: grabbing;
        }
        .sortable-chosen {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
</x-app-layout>

