<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Image') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6">
                    <img src="{{ asset($image->medium_path) }}" alt="{{ $image->title }}" class="max-w-full h-auto rounded-lg border-2 border-black">
                </div>

                <form method="POST" action="{{ route('portfolio.update', $image->id) }}">
                    @csrf
                    @method('patch')

                    <div class="mb-6">
                        <x-input-label for="title" :value="__('Title (optional)')" />
                        <x-text-input id="title" name="title" type="text" class="block mt-1 w-full" :value="old('title', $image->title)" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="description" :value="__('Description (optional)')" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">{{ old('description', $image->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="category" :value="__('Category')" />
                            <select id="category" name="category" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                <option value="fashion" {{ old('category', $image->category) === 'fashion' ? 'selected' : '' }}>Fashion</option>
                                <option value="beauty" {{ old('category', $image->category) === 'beauty' ? 'selected' : '' }}>Beauty</option>
                                <option value="commercial" {{ old('category', $image->category) === 'commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="editorial" {{ old('category', $image->category) === 'editorial' ? 'selected' : '' }}>Editorial</option>
                                <option value="artistic" {{ old('category', $image->category) === 'artistic' ? 'selected' : '' }}>Artistic</option>
                                <option value="other" {{ old('category', $image->category) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="album_id" :value="__('Album')" />
                            <select id="album_id" name="album_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                @foreach($albums as $album)
                                    <option value="{{ $album->id }}" {{ old('album_id', $image->album_id) == $album->id ? 'selected' : '' }}>{{ $album->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('album_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="photographer_id" :value="__('Photographer')" />
                            <select id="photographer_id" name="photographer_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                @foreach($photographers as $photographer)
                                    <option value="{{ $photographer->id }}" {{ old('photographer_id', $image->photographer_id) == $photographer->id ? 'selected' : '' }}>{{ $photographer->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('photographer_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="shot_date" :value="__('Shot Date (optional)')" />
                            <x-text-input id="shot_date" name="shot_date" type="date" class="block mt-1 w-full" :value="old('shot_date', $image->shot_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('shot_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-6 space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $image->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Featured image</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_polaroid" value="1" {{ old('is_polaroid', $image->is_polaroid) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Polaroid</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="contains_nudity" value="1" {{ old('contains_nudity', $image->contains_nudity) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Contains nudity</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $image->is_public) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Public</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('portfolio.index') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        <x-primary-button>
                            {{ __('Update Image') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

