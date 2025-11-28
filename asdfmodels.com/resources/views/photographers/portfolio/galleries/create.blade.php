<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Gallery') }}
            </h2>
            <a href="{{ route('photographers.portfolio.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>Back to Portfolio
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('photographers.portfolio.galleries.store') }}" x-data="{ selectedImages: [], coverImageId: null }">
                @csrf

                <div class="bg-white border-2 border-gray-800 rounded-lg shadow-lg p-6 md:p-8 space-y-6">
                    <!-- Title -->
                    <div>
                        <x-input-label for="title" :value="__('Gallery Title')" />
                        <x-text-input id="title" name="title" type="text" class="block mt-1 w-full" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <!-- Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700">Feature this gallery</span>
                                <p class="text-xs text-gray-500">Show in featured section</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700">Make gallery public</span>
                                <p class="text-xs text-gray-500">Visible to others</p>
                            </div>
                        </label>
                    </div>

                    <!-- Cover Image Selection -->
                    <div>
                        <x-input-label :value="__('Cover Image (Optional)')" />
                        <p class="text-sm text-gray-600 mb-4">Choose an image to use as the gallery cover</p>
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 max-h-64 overflow-y-auto border-2 border-gray-200 rounded-lg p-4">
                            @foreach($images as $image)
                            <div class="relative aspect-square cursor-pointer group"
                                 @click="coverImageId = coverImageId === {{ $image->id }} ? null : {{ $image->id }}"
                                 :class="coverImageId === {{ $image->id }} ? 'ring-4 ring-black' : 'ring-2 ring-gray-200 hover:ring-gray-400'">
                                <img src="{{ asset($image->thumbnail_path) }}" 
                                     alt="Cover option"
                                     class="w-full h-full object-cover rounded">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center rounded"
                                     :class="coverImageId === {{ $image->id }} ? 'bg-opacity-50' : ''">
                                    <i class="fas fa-check-circle text-white text-xl"
                                       :class="coverImageId === {{ $image->id }} ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="cover_image_id" x-model="coverImageId">
                    </div>

                    <!-- Image Selection -->
                    <div>
                        <x-input-label :value="__('Add Images to Gallery')" />
                        <p class="text-sm text-gray-600 mb-4">Select images to include in this gallery (you can add more later)</p>
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 max-h-96 overflow-y-auto border-2 border-gray-200 rounded-lg p-4">
                            @foreach($images as $image)
                            <div class="relative aspect-square cursor-pointer group"
                                 @click="if (selectedImages.includes({{ $image->id }})) { selectedImages = selectedImages.filter(id => id !== {{ $image->id }}); } else { selectedImages.push({{ $image->id }}); }"
                                 :class="selectedImages.includes({{ $image->id }}) ? 'ring-4 ring-black' : 'ring-2 ring-gray-200 hover:ring-gray-400'">
                                <img src="{{ asset($image->thumbnail_path) }}" 
                                     alt="Image"
                                     class="w-full h-full object-cover rounded">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center rounded"
                                     :class="selectedImages.includes({{ $image->id }}) ? 'bg-opacity-50' : ''">
                                    <i class="fas fa-check-circle text-white text-xl"
                                       :class="selectedImages.includes({{ $image->id }}) ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <template x-for="imageId in selectedImages" :key="imageId">
                            <input type="hidden" name="image_ids[]" :value="imageId">
                        </template>
                        <p class="text-sm text-gray-500 mt-2" x-show="selectedImages.length > 0">
                            <span x-text="selectedImages.length"></span> image(s) selected
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-4 border-t-2 border-gray-200">
                        <a href="{{ route('photographers.portfolio.index') }}" class="px-6 py-2 border-2 border-gray-300 rounded hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800 transition-colors">
                            Create Gallery
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

