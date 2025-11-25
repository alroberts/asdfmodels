<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Album Header -->
            <div class="bg-white shadow sm:rounded-lg mb-6 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-black mb-2">{{ $album->name }}</h1>
                        @if($album->description)
                            <p class="text-gray-700">{{ $album->description }}</p>
                        @endif
                    </div>
                    @auth
                        @if($album->user_id === Auth::id())
                            <a href="{{ route('albums.edit', $album->id) }}" class="text-blue-600 hover:underline">Edit Album</a>
                        @endif
                    @endauth
                </div>
                @if($album->contains_nudity)
                    <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle"></i>
                            This album contains nudity. You must be 18+ to view.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Images Grid -->
            @if($album->images->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($album->images as $image)
                            <div class="relative group aspect-square overflow-hidden rounded-lg">
                                <img src="{{ asset($image->thumbnail_path) }}" alt="{{ $image->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer" onclick="openLightbox('{{ asset($image->full_path) }}')">
                                @if($image->contains_nudity)
                                    <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">18+</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                    <p class="text-gray-600">This album is empty.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center" onclick="closeLightbox()">
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300">&times;</button>
    </div>

    <script>
        function openLightbox(imageSrc) {
            document.getElementById('lightbox-image').src = imageSrc;
            document.getElementById('lightbox').classList.remove('hidden');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
        }
    </script>
</x-app-layout>

