<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Photographer Header -->
            <div class="bg-white shadow sm:rounded-lg mb-6 p-6">
                <h1 class="text-3xl font-bold text-black mb-2">{{ $photographer->name }}</h1>
                <p class="text-gray-600">Photographer</p>
            </div>

            <!-- Tagged Images -->
            @if($taggedImages->count() > 0)
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-black mb-4">Work with Models</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($taggedImages as $image)
                            <div class="relative group aspect-square overflow-hidden rounded-lg">
                                <a href="{{ route('models.show', $image->model_id) }}">
                                    <img src="{{ asset($image->thumbnail_path) }}" alt="Image" class="w-full h-full object-cover hover:scale-105 transition-transform">
                                </a>
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-xs">
                                    <p class="truncate">{{ $image->model->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                    <p class="text-gray-600">No tagged images yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

