<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Album') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="POST" action="{{ route('albums.update', $album->id) }}">
                    @csrf
                    @method('patch')

                    <div class="mb-6">
                        <x-input-label for="name" :value="__('Album Name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $album->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="description" :value="__('Description (optional)')" />
                        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">{{ old('description', $album->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="cover_image_id" :value="__('Cover Image (optional)')" />
                        <select id="cover_image_id" name="cover_image_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                            <option value="">None</option>
                            @foreach($images as $image)
                                <option value="{{ $image->id }}" {{ old('cover_image_id', $album->cover_image_id) == $image->id ? 'selected' : '' }}>
                                    {{ $image->title ?: 'Image #' . $image->id }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('cover_image_id')" class="mt-2" />
                    </div>

                    <div class="mb-6 space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="contains_nudity" value="1" {{ old('contains_nudity', $album->contains_nudity) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Album contains nudity</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $album->is_public) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Make album public</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('albums.show', $album->id) }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        <x-primary-button>
                            {{ __('Update Album') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

