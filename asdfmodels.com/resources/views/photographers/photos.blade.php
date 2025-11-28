<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Your Photos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border-2 border-green-500 rounded-lg">
                    <p class="text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Profile Created Successfully!</h3>
                    <p class="text-gray-600">Now let's add your photos. Upload a profile photo of yourself, and if you entered a company name, you can also upload your logo.</p>
                </div>

                <form method="POST" action="{{ route('photographers.profile.upload-photos') }}" enctype="multipart/form-data" x-data="{ profilePhotoPreview: null, logoPreview: null }">
                    @csrf

                    <!-- Profile Photo Upload -->
                    <div class="mb-6">
                        <x-input-label for="profile_photo" :value="__('Profile Photo')" />
                        <div class="mt-2">
                            <input 
                                type="file" 
                                id="profile_photo" 
                                name="profile_photo" 
                                accept="image/jpeg,image/jpg,image/png"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-2 file:border-gray-800 file:text-sm file:font-semibold file:bg-white file:text-gray-800 hover:file:bg-gray-50"
                                @change="
                                    if ($event.target.files[0]) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => profilePhotoPreview = e.target.result;
                                        reader.readAsDataURL($event.target.files[0]);
                                    }
                                "
                            >
                            <p class="mt-2 text-sm text-gray-600">Upload a photo of yourself. Maximum file size: 5MB.</p>
                            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                            
                            <div x-show="profilePhotoPreview" class="mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                <img :src="profilePhotoPreview" alt="Profile photo preview" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                            </div>
                            
                            @if($profile->profile_photo_path)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current photo:</p>
                                    <img src="{{ asset($profile->profile_photo_path) }}" alt="Current profile photo" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Logo Upload (only if professional_name is set) -->
                    @if($profile->professional_name)
                        <div class="mb-6">
                            <x-input-label for="logo" :value="__('Company Logo')" />
                            <p class="mt-1 text-sm text-gray-600 mb-2">Since you entered "{{ $profile->professional_name }}", you can upload your company logo.</p>
                            <div class="mt-2">
                                <input 
                                    type="file" 
                                    id="logo" 
                                    name="logo" 
                                    accept="image/jpeg,image/jpg,image/png,image/svg+xml"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-2 file:border-gray-800 file:text-sm file:font-semibold file:bg-white file:text-gray-800 hover:file:bg-gray-50"
                                    @change="
                                        if ($event.target.files[0]) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => logoPreview = e.target.result;
                                            reader.readAsDataURL($event.target.files[0]);
                                        }
                                    "
                                >
                                <p class="mt-2 text-sm text-gray-600">Upload your company logo. Maximum file size: 2MB. Supports JPG, PNG, and SVG.</p>
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                                
                                <div x-show="logoPreview" class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                                    <img :src="logoPreview" alt="Logo preview" class="max-w-xs max-h-32 object-contain rounded-lg border-2 border-gray-300">
                                </div>
                                
                                @if($profile->logo_path)
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Current logo:</p>
                                        <img src="{{ asset($profile->logo_path) }}" alt="Current logo" class="max-w-xs max-h-32 object-contain rounded-lg border-2 border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end space-x-4 mt-8">
                        <a href="{{ route('photographers.profile.edit') }}" class="text-gray-600 hover:text-gray-800">Skip for now</a>
                        <x-primary-button>
                            {{ __('Continue to Portfolio') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

