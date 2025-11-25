<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Portfolio Images') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="POST" action="{{ route('portfolio.store') }}" enctype="multipart/form-data" x-data="{ files: [], isPolaroid: false, containsNudity: false }">
                    @csrf

                    <!-- File Upload -->
                    <div class="mb-6">
                        <x-input-label for="images" :value="__('Select Images')" />
                        <input 
                            type="file" 
                            id="images" 
                            name="images[]" 
                            multiple 
                            accept="image/jpeg,image/jpg,image/png"
                            class="block mt-1 w-full border-2 border-black rounded-md"
                            @change="files = Array.from($event.target.files)"
                            required
                        >
                        <p class="mt-2 text-sm text-gray-600">You can select multiple images. Maximum file size: 10MB per image.</p>
                        <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                        
                        <div x-show="files.length > 0" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Selected files:</p>
                            <ul class="list-disc list-inside text-sm text-gray-600">
                                <template x-for="file in files" :key="file.name">
                                    <li x-text="file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="mb-6 space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="is_polaroid" 
                                    value="1" 
                                    x-model="isPolaroid"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">These are polaroids (unedited, natural photos)</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="contains_nudity" 
                                    value="1" 
                                    x-model="containsNudity"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Images contain nudity</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="is_public" 
                                    value="1" 
                                    checked
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Make images public</span>
                            </label>
                        </div>

                        <div>
                            <x-input-label for="category" :value="__('Category (optional)')" />
                            <select id="category" name="category" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                <option value="fashion">Fashion</option>
                                <option value="beauty">Beauty</option>
                                <option value="commercial">Commercial</option>
                                <option value="editorial">Editorial</option>
                                <option value="artistic">Artistic</option>
                                <option value="other">Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="photographer_id" :value="__('Photographer (optional)')" />
                            <select id="photographer_id" name="photographer_id" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">None</option>
                                @php
                                    $photographers = \App\Models\User::where('is_photographer', true)->orderBy('name')->get();
                                @endphp
                                @foreach($photographers as $photographer)
                                    <option value="{{ $photographer->id }}">{{ $photographer->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-600">Link this image to a photographer on the platform.</p>
                            <x-input-error :messages="$errors->get('photographer_id')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Polaroid Guidelines -->
                    <div x-show="isPolaroid" x-transition class="mb-6 p-4 bg-yellow-50 border-2 border-yellow-200 rounded-lg">
                        <h4 class="font-semibold text-yellow-800 mb-2">Polaroid Guidelines</h4>
                        <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                            <li>No editing or filters</li>
                            <li>Natural lighting</li>
                            <li>Minimal or no makeup</li>
                            <li>Clear view of features</li>
                            <li>No retouching</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('portfolio.index') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
                        <x-primary-button>
                            {{ __('Upload Images') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

