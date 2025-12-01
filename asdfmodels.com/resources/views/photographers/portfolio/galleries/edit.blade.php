<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Gallery') }}
            </h2>
            <a href="{{ route('photographers.portfolio.galleries.show', $gallery->id) }}" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Gallery
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('photographers.portfolio.galleries.update', $gallery->id) }}" x-data="{ visibility: '{{ old('visibility', $gallery->visibility) }}', status: '{{ old('status', $gallery->status) }}', containsNudity: {{ old('contains_nudity', $gallery->contains_nudity ? '1' : '0') == '1' ? 'true' : 'false' }}, customUsers: [] }">
                @csrf
                @method('PATCH')

                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <!-- Header Section -->
                    <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 px-6 md:px-8 py-6">
                        <h3 class="text-2xl font-bold text-gray-900">Gallery Information</h3>
                        <p class="text-sm text-gray-600 mt-1">Update your gallery details and settings</p>
                    </div>

                    <div class="p-6 md:p-8 space-y-8">
                        <!-- Gallery Title -->
                        <div class="space-y-2">
                            <x-input-label for="title" :value="__('Gallery Title')" class="text-sm font-semibold text-gray-900" />
                            <x-text-input 
                                id="title" 
                                name="title" 
                                type="text" 
                                class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200" 
                                :value="old('title', $gallery->title)" 
                                required 
                                autofocus 
                                placeholder="Enter gallery title"
                            />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <x-input-label for="description" :value="__('Description')" class="text-sm font-semibold text-gray-900" />
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4" 
                                class="block w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-gray-800 focus:ring-2 focus:ring-gray-800 focus:ring-opacity-20 transition-all duration-200 resize-none"
                                placeholder="Describe your gallery (optional)"
                            >{{ old('description', $gallery->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Gallery Settings -->
                        <div class="border-t border-gray-200 pt-8">
                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-gray-900">Gallery Settings</h3>
                                <p class="text-sm text-gray-600 mt-1">Configure visibility, content settings, and publication status</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Gallery Visibility -->
                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                    <x-input-label for="visibility" :value="__('Gallery Visibility')" class="text-sm font-semibold text-gray-900 mb-3" />
                                    <div class="space-y-2">
                                        <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="visibility === 'public' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                            <input type="radio" name="visibility" value="public" x-model="visibility" class="sr-only">
                                            <i class="fas fa-globe text-gray-600 w-5 text-center" :class="visibility === 'public' ? 'text-gray-900' : ''"></i>
                                            <span class="ml-3 text-sm font-medium flex-1" :class="visibility === 'public' ? 'text-gray-900' : 'text-gray-600'">Public</span>
                                        </label>
                                        <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="visibility === 'link_only' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                            <input type="radio" name="visibility" value="link_only" x-model="visibility" class="sr-only">
                                            <i class="fas fa-link text-gray-600 w-5 text-center" :class="visibility === 'link_only' ? 'text-gray-900' : ''"></i>
                                            <span class="ml-3 text-sm font-medium flex-1" :class="visibility === 'link_only' ? 'text-gray-900' : 'text-gray-600'">Link Only</span>
                                        </label>
                                        <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="visibility === 'hidden' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                            <input type="radio" name="visibility" value="hidden" x-model="visibility" class="sr-only">
                                            <i class="fas fa-eye-slash text-gray-600 w-5 text-center" :class="visibility === 'hidden' ? 'text-gray-900' : ''"></i>
                                            <span class="ml-3 text-sm font-medium flex-1" :class="visibility === 'hidden' ? 'text-gray-900' : 'text-gray-600'">Hidden</span>
                                        </label>
                                        <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="visibility === 'custom' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                            <input type="radio" name="visibility" value="custom" x-model="visibility" class="sr-only">
                                            <i class="fas fa-users text-gray-600 w-5 text-center" :class="visibility === 'custom' ? 'text-gray-900' : ''"></i>
                                            <span class="ml-3 text-sm font-medium flex-1" :class="visibility === 'custom' ? 'text-gray-900' : 'text-gray-600'">Custom</span>
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('visibility')" class="mt-3" />
                                </div>

                                <!-- Right Column: NSFW Content & Gallery Status -->
                                <div class="space-y-6">
                                    <!-- NSFW Content -->
                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                        <x-input-label :value="__('NSFW Content')" class="text-sm font-semibold text-gray-900 mb-3" />
                                        <label class="flex items-center group cursor-pointer">
                                            <div class="relative">
                                                <input 
                                                    type="checkbox" 
                                                    name="contains_nudity" 
                                                    value="1" 
                                                    {{ old('contains_nudity', $gallery->contains_nudity ? '1' : '0') == '1' ? 'checked' : '' }} 
                                                    x-model="containsNudity"
                                                    class="sr-only"
                                                >
                                                <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="containsNudity ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                    <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="containsNudity ? 'opacity-100' : 'opacity-0'">
                                                        <path d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Contains NSFW content</span>
                                        </label>
                                        <x-input-error :messages="$errors->get('contains_nudity')" class="mt-3" />
                                    </div>

                                    <!-- Gallery Status -->
                                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                                        <x-input-label for="status" :value="__('Gallery Status')" class="text-sm font-semibold text-gray-900 mb-3" />
                                        <div class="space-y-2">
                                            <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="status === 'draft' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                                <input type="radio" name="status" value="draft" x-model="status" class="sr-only">
                                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center transition-all" :class="status === 'draft' ? 'border-gray-800' : 'border-gray-300'">
                                                    <div class="w-2 h-2 bg-gray-800 rounded-full transition-opacity" :class="status === 'draft' ? 'opacity-100' : 'opacity-0'"></div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium flex-1 transition-colors" :class="status === 'draft' ? 'text-gray-900' : 'text-gray-600'">Draft</span>
                                            </label>
                                            <label class="flex items-center p-2 rounded-lg hover:bg-white transition-colors cursor-pointer group" :class="status === 'published' ? 'bg-white border-2 border-gray-800' : 'border-2 border-transparent'">
                                                <input type="radio" name="status" value="published" x-model="status" class="sr-only">
                                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center transition-all" :class="status === 'published' ? 'border-gray-800' : 'border-gray-300'">
                                                    <div class="w-2 h-2 bg-gray-800 rounded-full transition-opacity" :class="status === 'published' ? 'opacity-100' : 'opacity-0'"></div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium flex-1 transition-colors" :class="status === 'published' ? 'text-gray-900' : 'text-gray-600'">Published</span>
                                            </label>
                                        </div>
                                        <x-input-error :messages="$errors->get('status')" class="mt-3" />
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Visibility Users (shown when custom is selected) -->
                            <div 
                                x-show="visibility === 'custom'" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="mt-6 bg-gray-50 rounded-lg p-6 border border-gray-200"
                            >
                                <div class="mb-4">
                                    <x-input-label :value="__('Share with Specific Users')" class="text-sm font-semibold text-gray-900 mb-1" />
                                    <p class="text-sm text-gray-600">Select users who can view this gallery</p>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-4 max-h-64 overflow-y-auto">
                                    <div class="space-y-2">
                                        @php
                                            $allUsers = \App\Models\User::where('is_photographer', false)
                                                ->where('is_admin', false)
                                                ->whereHas('modelProfile', function($q) {
                                                    $q->where('is_public', true);
                                                })
                                                ->orderBy('name')
                                                ->get();
                                            $selectedUsers = old('custom_visibility_users', $gallery->custom_visibility_users ?? []);
                                        @endphp
                                        @foreach($allUsers as $user)
                                            <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group" x-data="{ checked: {{ in_array($user->id, $selectedUsers) ? 'true' : 'false' }} }">
                                                <div class="relative">
                                                    <input 
                                                        type="checkbox" 
                                                        name="custom_visibility_users[]" 
                                                        value="{{ $user->id }}" 
                                                        {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }} 
                                                        x-model="checked"
                                                        class="sr-only"
                                                    >
                                                    <div class="w-5 h-5 border-2 rounded transition-all duration-200 flex items-center justify-center relative" :class="checked ? 'border-gray-800 bg-gray-800' : 'border-gray-300 bg-white'">
                                                        <svg class="absolute w-3.5 h-3.5 transition-opacity duration-200" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" :class="checked ? 'opacity-100' : 'opacity-0'">
                                                            <path d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">{{ $user->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-3 flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Note: Groups feature coming soon</span>
                                </p>
                                <x-input-error :messages="$errors->get('custom_visibility_users')" class="mt-3" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="bg-gray-50 border-t border-gray-200 px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-end gap-3">
                        <a 
                            href="{{ route('photographers.portfolio.galleries.show', $gallery->id) }}" 
                            class="px-6 py-2.5 border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-white hover:border-gray-400 transition-all duration-200 text-center"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="px-6 py-2.5 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 active:bg-gray-950 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


