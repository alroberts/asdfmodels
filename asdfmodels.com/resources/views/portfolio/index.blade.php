<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Portfolio') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('albums.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Albums
                </a>
                <a href="{{ route('portfolio.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                    Upload Images
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Polaroids Section -->
            @if($polaroids->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-semibold text-black mb-4">Polaroids</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($polaroids as $image)
                            <div class="relative group aspect-square overflow-hidden rounded-lg">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="Polaroid" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center">
                                    <form method="POST" action="{{ route('portfolio.destroy', $image->id) }}" class="hidden group-hover:block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-white hover:text-red-400" onclick="return confirm('Delete this image?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- All Images -->
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-black mb-4">All Images</h3>
                @if($images->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($images as $image)
                            <div class="relative group aspect-square overflow-hidden rounded-lg">
                                <a href="{{ route('portfolio.edit', $image->id) }}">
                                    <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover">
                                </a>
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center space-x-2">
                                    <a href="{{ route('portfolio.edit', $image->id) }}" class="hidden group-hover:block text-white hover:text-blue-400">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('portfolio.destroy', $image->id) }}" class="hidden group-hover:block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-white hover:text-red-400" onclick="return confirm('Delete this image?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @if($image->is_polaroid)
                                    <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">Polaroid</span>
                                @endif
                                @if($image->contains_nudity)
                                    <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">18+</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $images->links() }}
                    </div>
                @else
                    <p class="text-gray-600">No images uploaded yet. <a href="{{ route('portfolio.create') }}" class="text-blue-600 hover:underline">Upload your first image</a></p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

