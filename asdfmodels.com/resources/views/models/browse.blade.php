<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Browse Models') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('models.browse') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="search" :value="__('Search')" />
                            <x-text-input id="search" name="search" type="text" class="block mt-1 w-full" :value="old('search', $filters['search'] ?? '')" placeholder="Name..." />
                        </div>

                        <div>
                            <x-input-label for="gender" :value="__('Gender')" />
                            <select id="gender" name="gender" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">All</option>
                                <option value="male" {{ ($filters['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ ($filters['gender'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" name="location" type="text" class="block mt-1 w-full" :value="old('location', $filters['location'] ?? '')" placeholder="City or Country..." />
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
                        <label class="flex items-center">
                            <input type="checkbox" name="verified" value="1" {{ ($filters['verified'] ?? '') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Verified only</span>
                        </label>

                        <x-primary-button>Filter</x-primary-button>
                        <a href="{{ route('models.browse') }}" class="text-gray-600 hover:text-gray-800">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Models Grid -->
            @if($models->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($models as $model)
                        <a href="{{ route('models.show', $model->user_id) }}" class="bg-white shadow sm:rounded-lg overflow-hidden border-2 border-black hover:shadow-xl transition-shadow">
                            @if($model->profile_photo_path)
                                <img src="{{ asset($model->profile_photo_path) }}" alt="{{ $model->user->name }}" class="w-full aspect-square object-cover">
                            @else
                                <div class="w-full aspect-square bg-gray-200 flex items-center justify-center">
                                    <span class="text-4xl text-gray-600">{{ substr($model->user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="p-3">
                                <h3 class="font-semibold text-black mb-1">{{ $model->user->name }}</h3>
                                @if($model->location_city || $model->location_country)
                                    <p class="text-sm text-gray-600">{{ $model->location_city }}{{ $model->location_city && $model->location_country ? ', ' : '' }}{{ $model->location_country }}</p>
                                @endif
                                @if($model->isVerified())
                                    <span class="inline-block mt-2 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                        <i class="fas fa-check"></i> Verified
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $models->links() }}
                </div>
            @else
                <div class="bg-white shadow sm:rounded-lg p-8 text-center">
                    <p class="text-gray-600">No models found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

