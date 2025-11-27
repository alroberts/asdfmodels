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

                <form method="POST" action="{{ route('photographers.portfolio.update', $image->id) }}">
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
                                <option value="portrait" {{ old('category', $image->category) === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                <option value="commercial" {{ old('category', $image->category) === 'commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="wedding" {{ old('category', $image->category) === 'wedding' ? 'selected' : '' }}>Wedding</option>
                                <option value="editorial" {{ old('category', $image->category) === 'editorial' ? 'selected' : '' }}>Editorial</option>
                                <option value="artistic" {{ old('category', $image->category) === 'artistic' ? 'selected' : '' }}>Artistic</option>
                                <option value="other" {{ old('category', $image->category) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="model_id" :value="__('Model in Photo (optional)')" />
                            <select id="model_id" name="model_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}" {{ old('model_id', $image->model_id) == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('model_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="shot_date" :value="__('Shot Date (optional)')" />
                            <x-text-input id="shot_date" name="shot_date" type="date" class="block mt-1 w-full" :value="old('shot_date', $image->shot_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('shot_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="display_order" :value="__('Display Order (optional)')" />
                            <x-text-input id="display_order" name="display_order" type="number" class="block mt-1 w-full" :value="old('display_order', $image->display_order)" />
                            <p class="mt-1 text-sm text-gray-600">Lower numbers appear first</p>
                            <x-input-error :messages="$errors->get('display_order')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-6 space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $image->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Featured image</span>
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
                        <a href="{{ route('photographers.portfolio.index') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        <x-primary-button>
                            {{ __('Update Image') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

