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

            <!-- Images Grid -->
            @if($gallery->images->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach($gallery->images as $image)
                        <div class="relative aspect-square overflow-hidden rounded-lg border-2 border-gray-200 hover:border-gray-800 transition-all cursor-pointer group"
                             onclick="window.location.href='{{ route('photographers.portfolio.edit', $image->id) }}'">
                            <img src="{{ asset($image->thumbnail_path) }}" 
                                 alt="{{ $image->title ?? 'Image' }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center">
                                <i class="fas fa-edit text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div 
                    x-data="galleryUploader({{ $gallery->id }})"
                    x-init="
                        window.addEventListener('resize', () => { justifyGrid(); });
                        $watch('selectedFiles', () => { $nextTick(() => { justifyGrid(); }); });
                    "
                    @dragover.prevent="handleDragOver($event)"
                    @dragleave.prevent="handleDragLeave($event)"
                    @drop.prevent="handleDrop($event)"
                    @click="$refs.fileInput.click()"
                    :class="{
                        'border-gray-800 bg-gray-50 scale-[1.02]': isDragging,
                        'border-gray-200 bg-white': !isDragging
                    }"
                    class="bg-white rounded-xl shadow-lg border-2 border-dashed p-12 text-center cursor-pointer transition-all duration-300 ease-in-out"
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
                            class="fas fa-cloud-upload-alt text-6xl transition-all duration-300" 
                            :class="{
                                'text-gray-800 scale-110 animate-pulse': isDragging,
                                'text-gray-300': !isDragging
                            }"
                        ></i>
                        <div>
                            <p class="text-lg font-semibold text-gray-900 transition-colors duration-300" :class="isDragging ? 'text-gray-800' : 'text-gray-900'">
                                <span class="text-gray-800 underline">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-sm text-gray-600 mt-1">PNG, JPG up to 10MB each</p>
                        </div>
                    </div>

                    <!-- Selected Files Preview -->
                    <div 
                        x-show="selectedFiles.length > 0" 
                        class="mt-6"
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
                            <span x-text="selectedFiles.length"></span> <span x-text="selectedFiles.length === 1 ? 'Photo' : 'Photos'"></span> Selected
                        </h4>
                        <div class="p-2 border-2 border-gray-200 rounded-lg bg-gray-50">
                            <div class="justified-grid relative" x-ref="gridContainer">
                                <!-- Drop marker positioned between items -->
                                <div 
                                    x-show="draggedIndex !== null && dragOverIndex !== null && dropPosition !== null"
                                    class="drop-marker"
                                    :style="dropMarkerStyle"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-0.8"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-0.8"
                                >
                                    <div class="drop-marker-line"></div>
                                </div>
                                
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div 
                                        class="relative group justified-item justified-image-item cursor-move transition-all duration-200 ease-out"
                                        :class="{
                                            'opacity-30 scale-95': draggedIndex === index,
                                            'translate-x-4': draggedIndex !== null && draggedIndex !== index && dragOverIndex !== null && 
                                                            draggedIndex < dragOverIndex && index > draggedIndex && index <= dragOverIndex,
                                            '-translate-x-4': draggedIndex !== null && draggedIndex !== index && dragOverIndex !== null && 
                                                             draggedIndex > dragOverIndex && index < draggedIndex && index >= dragOverIndex
                                        }"
                                        draggable="true"
                                        @dragstart.stop="dragStart($event, index)"
                                        @dragover.stop.prevent="dragOver($event, index)"
                                        @dragenter.stop.prevent="dragEnter($event, index)"
                                        @dragleave.stop="dragLeave($event)"
                                        @drop.stop.prevent="drop($event, index)"
                                        @dragend.stop="dragEnd($event)"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform scale-90 translate-y-4"
                                        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                                        :style="`transition-delay: ${index * 50}ms`"
                                        :data-item-index="index"
                                    >
                                        <div class="rounded-lg overflow-hidden border-2 border-gray-300 bg-gray-200 transform transition-transform duration-200 group-hover:scale-105 h-full">
                                            <img :src="file.preview" :alt="file.name" class="justified-img pointer-events-none" :data-index="index">
                                        </div>
                                        <button 
                                            @click.stop="removeFile(index)"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-10"
                                        >
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white text-xs p-1 truncate opacity-0 group-hover:opacity-100 transition-opacity duration-200" x-text="file.name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <style>
                            .justified-grid {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 1rem;
                                align-items: flex-start;
                            }
                            .justified-item {
                                flex-shrink: 0;
                                margin-bottom: 1rem;
                            }
                            .justified-img {
                                height: 100%;
                                width: auto;
                                object-fit: contain;
                                display: block;
                            }
                            .drop-marker {
                                pointer-events: none;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                padding: 0 1rem;
                                transform: translateX(-50%);
                            }
                            .drop-marker-line {
                                width: 3px;
                                height: 250px;
                                background: #000000;
                                border-radius: 2px;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
                                transition: height 0.2s ease;
                            }
                        </style>
                    </div>

                    <!-- Upload Progress -->
                    <div x-show="isUploading" class="mt-6">
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
                    <div x-show="errorMessage" class="mt-6 p-4 bg-red-50 border-2 border-red-500 rounded-lg">
                        <p class="text-sm text-red-700" x-text="errorMessage"></p>
                    </div>

                    <!-- Upload Button (shown when files selected) -->
                    <div 
                        x-show="selectedFiles.length > 0 && !isUploading" 
                        class="mt-6"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                    >
                        <button 
                            @click.stop="uploadFiles()"
                            class="px-6 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 active:bg-gray-950 transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105 active:scale-95 flex items-center justify-center gap-2 mx-auto"
                        >
                            <i class="fas fa-upload"></i>
                            Upload <span x-text="selectedFiles.length"></span> <span x-text="selectedFiles.length === 1 ? 'Photo' : 'Photos'"></span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function galleryUploader(galleryId) {
            return {
                galleryId: galleryId,
                isDragging: false,
                selectedFiles: [],
                isUploading: false,
                uploadProgress: 0,
                uploadStatus: '',
                errorMessage: '',
                draggedIndex: null,
                dragOverIndex: null,
                dropPosition: null, // 'before' or 'after' the dragOverIndex
                dropMarkerStyle: 'display: none;',
                updateDropMarker() {
                    if (this.draggedIndex === null || this.dragOverIndex === null || this.dropPosition === null) {
                        this.dropMarkerStyle = 'display: none;';
                        return;
                    }
                    
                    this.$nextTick(() => {
                        const container = this.$refs.gridContainer;
                        if (!container) return;
                        
                        const items = container.querySelectorAll('.justified-image-item');
                        if (items.length <= this.dragOverIndex) return;
                        
                        const targetItem = items[this.dragOverIndex];
                        if (!targetItem) return;
                        
                        const rect = targetItem.getBoundingClientRect();
                        const containerRect = container.getBoundingClientRect();
                        
                        let left, top;
                        if (this.dropPosition === 'before') {
                            left = rect.left - containerRect.left - 20; // Position before the item
                            top = rect.top - containerRect.top;
                        } else {
                            left = rect.right - containerRect.left + 20; // Position after the item
                            top = rect.top - containerRect.top;
                        }
                        
                        const lineHeight = Math.max(250, rect.height);
                        this.dropMarkerStyle = `position: absolute; left: ${left}px; top: ${top}px; z-index: 30; pointer-events: none;`;
                        
                        // Update line height
                        this.$nextTick(() => {
                            const marker = container.querySelector('.drop-marker');
                            if (marker) {
                                const line = marker.querySelector('.drop-marker-line');
                                if (line) {
                                    line.style.height = lineHeight + 'px';
                                }
                            }
                        });
                    });
                },
                handleDragOver(event) {
                    // Only trigger dropzone animation if dragging files (external drag)
                    // Not if dragging images to reorder (internal drag)
                    // If we're currently reordering (draggedIndex is set), don't trigger dropzone animation
                    if (this.draggedIndex !== null) {
                        return;
                    }
                    // Check if dataTransfer contains files (external drag)
                    const types = Array.from(event.dataTransfer.types || []);
                    const hasFiles = types.includes('Files');
                    if (hasFiles) {
                        this.isDragging = true;
                    }
                },
                handleDragLeave(event) {
                    // Only clear dragging state if we're actually leaving the dropzone
                    // and not just moving between child elements
                    const rect = event.currentTarget.getBoundingClientRect();
                    const x = event.clientX;
                    const y = event.clientY;
                    
                    // Check if we're still within the dropzone bounds
                    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                        this.isDragging = false;
                    }
                },
                handleDrop(event) {
                    this.isDragging = false;
                    // Only handle file drops, not internal reordering
                    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                        const files = Array.from(event.dataTransfer.files).filter(file => 
                            file.type.startsWith('image/')
                        );
                        this.addFiles(files);
                    }
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
                            const img = new Image();
                            img.onload = () => {
                                this.selectedFiles.push({
                                    file: file,
                                    name: file.name,
                                    size: file.size,
                                    preview: e.target.result,
                                    width: img.width,
                                    height: img.height,
                                    aspectRatio: img.width / img.height
                                });
                                this.$nextTick(() => {
                                    this.justifyGrid();
                                });
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                },
                justifyGrid() {
                    this.$nextTick(() => {
                        const container = this.$refs.gridContainer;
                        if (!container) return;
                        
                        // Only select image items, exclude placeholders
                        const items = container.querySelectorAll('.justified-image-item');
                        if (items.length === 0) return;
                        
                        const containerWidth = container.offsetWidth;
                        const targetRowHeight = 250;
                        const gap = 16;
                        let currentRow = [];
                        let currentRowWidth = 0;
                        
                        items.forEach((item, domIndex) => {
                            const img = item.querySelector('.justified-img');
                            if (!img) return;
                            
                            // Get the actual index from the data attribute or find it
                            const actualIndex = parseInt(img.getAttribute('data-index')) ?? domIndex;
                            const file = this.selectedFiles[actualIndex];
                            if (!file || !file.aspectRatio) return;
                            
                            const itemWidth = targetRowHeight * file.aspectRatio;
                            
                            if (currentRowWidth + itemWidth + (currentRow.length * gap) > containerWidth && currentRow.length > 0) {
                                // Finalize current row
                                const actualRowHeight = (containerWidth - (currentRow.length - 1) * gap) / currentRow.reduce((sum, i) => sum + i.aspectRatio, 0);
                                currentRow.forEach((rowItem, idx) => {
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
                                aspectRatio: file.aspectRatio
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
                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    this.errorMessage = '';
                    this.$nextTick(() => {
                        this.justifyGrid();
                    });
                },
                dragStart(event, index) {
                    this.draggedIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/html', event.target.outerHTML);
                    // Don't set opacity here, let CSS handle it
                },
                dragEnter(event, index) {
                    if (this.draggedIndex !== null && this.draggedIndex !== index) {
                        this.calculateDropPosition(event, index);
                    }
                },
                dragOver(event, index) {
                    if (this.draggedIndex !== null && this.draggedIndex !== index) {
                        event.dataTransfer.dropEffect = 'move';
                        this.calculateDropPosition(event, index);
                    }
                },
                calculateDropPosition(event, index) {
                    if (this.draggedIndex === null || this.draggedIndex === index) {
                        return;
                    }
                    
                    const rect = event.currentTarget.getBoundingClientRect();
                    const mouseY = event.clientY;
                    const centerY = rect.top + rect.height / 2;
                    
                    // Determine if we're dropping before or after based on mouse position
                    const position = mouseY < centerY ? 'before' : 'after';
                    
                    this.dragOverIndex = index;
                    this.dropPosition = position;
                    this.updateDropMarker();
                },
                dragLeave(event) {
                    // Only clear if we're actually leaving the element
                    const rect = event.currentTarget.getBoundingClientRect();
                    const x = event.clientX;
                    const y = event.clientY;
                    
                    // Check if mouse is still within the element bounds
                    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                        // Small delay to avoid flickering when moving between child elements
                        setTimeout(() => {
                            const newRect = event.currentTarget.getBoundingClientRect();
                            const stillOutside = event.clientX < newRect.left || event.clientX > newRect.right || 
                                                 event.clientY < newRect.top || event.clientY > newRect.bottom;
                            if (stillOutside) {
                                this.dragOverIndex = null;
                                this.dropPosition = null;
                                this.dropMarkerStyle = 'display: none;';
                            }
                        }, 100);
                    }
                },
                drop(event, dropIndex) {
                    if (this.draggedIndex === null || this.draggedIndex === dropIndex) {
                        this.dragOverIndex = null;
                        this.dropPosition = null;
                        return;
                    }
                    
                    // Calculate final drop index based on position
                    let finalIndex = dropIndex;
                    if (this.dropPosition === 'after') {
                        finalIndex = dropIndex + 1;
                    } else {
                        finalIndex = dropIndex;
                    }
                    
                    // Adjust if dragging from after to before
                    if (this.draggedIndex > dropIndex && this.dropPosition === 'before') {
                        finalIndex = dropIndex;
                    } else if (this.draggedIndex < dropIndex && this.dropPosition === 'after') {
                        finalIndex = dropIndex + 1;
                    } else if (this.draggedIndex < dropIndex) {
                        finalIndex = dropIndex;
                    } else if (this.draggedIndex > dropIndex) {
                        finalIndex = dropIndex + 1;
                    }
                    
                    // Ensure finalIndex is within bounds
                    finalIndex = Math.max(0, Math.min(finalIndex, this.selectedFiles.length));
                    
                    const draggedItem = this.selectedFiles[this.draggedIndex];
                    this.selectedFiles.splice(this.draggedIndex, 1);
                    this.selectedFiles.splice(finalIndex, 0, draggedItem);
                    
                    this.draggedIndex = null;
                    this.dragOverIndex = null;
                    this.dropPosition = null;
                    
                    this.$nextTick(() => {
                        this.justifyGrid();
                    });
                },
                dragEnd(event) {
                    this.draggedIndex = null;
                    this.dragOverIndex = null;
                    this.dropPosition = null;
                    this.dropMarkerStyle = 'display: none;';
                },
                async uploadFiles() {
                    if (this.selectedFiles.length === 0) return;

                    this.isUploading = true;
                    this.uploadProgress = 0;
                    this.errorMessage = '';
                    this.uploadStatus = 'Preparing files...';

                    const formData = new FormData();
                    
                    // Add files
                    this.selectedFiles.forEach((item, index) => {
                        formData.append(`images[${index}]`, item.file);
                    });

                    // Add gallery_id (required)
                    formData.append('gallery_id', this.galleryId);
                    
                    // Default options
                    formData.append('is_public', '1');
                    formData.append('is_featured', '0');
                    formData.append('contains_nudity', '0');

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
                                    window.location.reload();
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
                }
            };
        }
    </script>
</x-app-layout>

