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
                <a href="{{ route('photographers.portfolio.create') }}" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-upload"></i> Upload Images
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 md:py-12" x-data="portfolioManager()" x-init="init()">
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
                    <div class="text-sm text-gray-600 mt-1">Public</div>
                </div>
                <div class="bg-white border-2 border-gray-800 rounded-lg p-4 text-center">
                    <div class="text-2xl md:text-3xl font-bold text-blue-600">{{ $uncategorizedImages->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Uncategorized</div>
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

            <!-- Uncategorized Images Section -->
            @if($uncategorizedImages->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Uncategorized Images</h3>
                    <span class="text-sm text-gray-600">{{ $uncategorizedImages->count() }} images</span>
                </div>
                
                <div class="bg-white border-2 border-gray-300 rounded-lg p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($uncategorizedImages->take(12) as $image)
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
                    @if($uncategorizedImages->count() > 12)
                    <div class="mt-4 text-center">
                        <a href="{{ route('photographers.portfolio.uncategorized') }}" class="text-gray-600 hover:text-gray-900 underline text-sm">
                            View all {{ $uncategorizedImages->count() }} uncategorized images
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        function portfolioManager() {
            return {
                init() {
                    // Initialize any portfolio-specific functionality
                }
            };
        }
    </script>
</x-app-layout>
