<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Browse Photographers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6 border-2 border-black">
                <form method="GET" action="{{ route('photographers.browse') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="search" :value="__('Search')" />
                            <x-text-input id="search" name="search" type="text" class="block mt-1 w-full" :value="old('search', $filters['search'] ?? '')" placeholder="Name..." />
                        </div>

                        <div>
                            <x-input-label for="sort" :value="__('Sort By')" />
                            <select id="sort" name="sort" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="newest" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                                <option value="name" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <x-primary-button>Filter</x-primary-button>
                        <a href="{{ route('photographers.browse') }}" class="text-gray-600 hover:text-gray-800">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Photographers Grid -->
            @if($photographers->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($photographers as $photographer)
                        <a href="{{ route('photographers.show', $photographer->id) }}" class="bg-white shadow sm:rounded-lg overflow-hidden border-2 border-black hover:shadow-xl transition-shadow">
                            @if($photographer->photographerImages->count() > 0 && $photographer->photographerImages->first()->image_path)
                                <img src="{{ asset($photographer->photographerImages->first()->image_path) }}" alt="{{ $photographer->name }}" class="w-full aspect-square object-cover">
                            @else
                                <div class="w-full aspect-square bg-gray-200 flex items-center justify-center">
                                    <span class="text-4xl text-gray-600">{{ substr($photographer->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="p-3">
                                <h3 class="font-semibold text-black mb-1">{{ $photographer->name }}</h3>
                                @if($photographer->photographerImages->count() > 0)
                                    <p class="text-sm text-gray-600">{{ $photographer->photographerImages->count() }} {{ $photographer->photographerImages->count() === 1 ? 'photo' : 'photos' }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $photographers->links() }}
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center border-2 border-black">
                    <p class="text-gray-600">No photographers found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

