<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Albums') }}
            </h2>
            <a href="{{ route('albums.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                Create Album
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if($albums->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($albums as $album)
                        <div class="bg-white shadow sm:rounded-lg overflow-hidden border-2 border-black hover:shadow-xl transition-shadow">
                            @if($album->coverImage)
                                <a href="{{ route('albums.show', $album->id) }}">
                                    <img src="{{ asset($album->coverImage->thumbnail_path) }}" alt="{{ $album->name }}" class="w-full h-48 object-cover">
                                </a>
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-images text-4xl text-gray-400"></i>
                                </div>
                            @endif
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-black mb-2">
                                    <a href="{{ route('albums.show', $album->id) }}" class="hover:underline">{{ $album->name }}</a>
                                </h3>
                                @if($album->description)
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $album->description }}</p>
                                @endif
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                                    <span>{{ $album->images->count() }} images</span>
                                    @if($album->contains_nudity)
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Contains Nudity</span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('albums.edit', $album->id) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                                    <form method="POST" action="{{ route('albums.destroy', $album->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this album? Images will not be deleted.')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                    <p class="text-gray-600 mb-4">No albums created yet.</p>
                    <a href="{{ route('albums.create') }}" class="inline-block bg-black text-white px-6 py-3 rounded hover:bg-gray-800">
                        Create Your First Album
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

