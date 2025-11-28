<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Photographer Profile') }}
        </h2>
    </x-slot>
    
    @push('styles')
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    @endpush
    
    @push('scripts')
    <!-- Cropper.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    @endpush

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border-2 border-green-500 rounded-lg">
                    <p class="text-green-800 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Verification Banner (Dismissable) -->
            @if(!$profile->isVerified())
                <div x-data="{ dismissed: localStorage.getItem('verification_banner_dismissed') === 'true' }" 
                     x-show="!dismissed"
                     x-transition
                     class="mb-6 relative bg-gradient-to-br from-yellow-50 via-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-lg shadow-lg overflow-hidden">
                    <button @click="dismissed = true; localStorage.setItem('verification_banner_dismissed', 'true')" 
                            class="absolute top-3 right-3 text-yellow-700 hover:text-yellow-900 transition-colors z-10">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                    <div class="p-6 pr-12">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shield-check text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-block bg-yellow-600 text-white text-xs font-semibold px-2 py-1 rounded uppercase">Premium</span>
                                    <h3 class="text-lg font-bold text-yellow-900">Get Verified</h3>
                                </div>
                                <p class="text-yellow-800 mb-3 text-sm">Unlock your professional potential with verified status:</p>
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-1.5 mb-4 text-yellow-800 text-sm">
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-yellow-600"></i> Increased credibility</li>
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-yellow-600"></i> Featured on homepage</li>
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-yellow-600"></i> More portfolio uploads</li>
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-yellow-600"></i> Exclusive features</li>
                                </ul>
                                <a href="{{ route('verification.create') }}" 
                                   class="inline-flex items-center gap-2 bg-yellow-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-yellow-700 transition-colors shadow-sm">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>Get Verified Now</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
                $servicesOptions = \App\Helpers\PhotographerOptions::services();
                $oldSpecialties = old('specialties', $profile->specialties ?? []);
                $oldServices = old('services_offered', $profile->services_offered ?? []);
                
                if (!is_array($oldSpecialties)) {
                    $oldSpecialties = [];
                }
                if (!is_array($oldServices)) {
                    $oldServices = [];
                }
                
                $oldEquipment = old('equipment', $profile->equipment ?? []);
                if (is_array($oldEquipment) && !isset($oldEquipment['cameras'])) {
                    $equipment = [
                        'cameras' => [],
                        'lenses' => [],
                        'lighting' => [],
                        'other' => $oldEquipment
                    ];
                } else {
                    $equipment = [
                        'cameras' => $oldEquipment['cameras'] ?? [],
                        'lenses' => $oldEquipment['lenses'] ?? [],
                        'lighting' => $oldEquipment['lighting'] ?? [],
                        'other' => $oldEquipment['other'] ?? []
                    ];
                }
                
                $locationCountryCode = old('location_country_code', $profile->location_country_code ?? null);
                $locationCity = old('location_city', $profile->location_city ?? null);
                $locationGeonameId = old('location_geoname_id', $profile->location_geoname_id ?? null);
                
                $initialData = [
                    'specialties' => $oldSpecialties,
                    'services' => $oldServices,
                    'equipment' => $equipment,
                    'locationCountryCode' => $locationCountryCode ?: '',
                    'locationCity' => $locationCity ?: '',
                    'locationGeonameId' => $locationGeonameId ?: null
                ];
            @endphp

            <script>
                window.photographerProfileInitialData = @json($initialData);
            </script>

            <!-- Tabbed Interface -->
            <div x-data="{ activeTab: 'basic' }" class="bg-white shadow-lg rounded-xl overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b-2 border-gray-200 bg-gray-50">
                    <div class="flex overflow-x-auto">
                        <button @click="activeTab = 'basic'" 
                                :class="activeTab === 'basic' ? 'border-b-4 border-black text-black font-semibold bg-white' : 'text-gray-600 hover:text-black hover:bg-gray-100'"
                                class="px-6 py-4 text-sm font-medium transition-all whitespace-nowrap">
                            <i class="fas fa-user mr-2"></i>Basic Info
                        </button>
                        <button @click="activeTab = 'professional'" 
                                :class="activeTab === 'professional' ? 'border-b-4 border-black text-black font-semibold bg-white' : 'text-gray-600 hover:text-black hover:bg-gray-100'"
                                class="px-6 py-4 text-sm font-medium transition-all whitespace-nowrap">
                            <i class="fas fa-briefcase mr-2"></i>Professional
                        </button>
                        <button @click="activeTab = 'equipment'" 
                                :class="activeTab === 'equipment' ? 'border-b-4 border-black text-black font-semibold bg-white' : 'text-gray-600 hover:text-black hover:bg-gray-100'"
                                class="px-6 py-4 text-sm font-medium transition-all whitespace-nowrap">
                            <i class="fas fa-camera mr-2"></i>Equipment
                        </button>
                        <button @click="activeTab = 'contact'" 
                                :class="activeTab === 'contact' ? 'border-b-4 border-black text-black font-semibold bg-white' : 'text-gray-600 hover:text-black hover:bg-gray-100'"
                                class="px-6 py-4 text-sm font-medium transition-all whitespace-nowrap">
                            <i class="fas fa-envelope mr-2"></i>Contact & Social
                        </button>
                        <button @click="activeTab = 'settings'" 
                                :class="activeTab === 'settings' ? 'border-b-4 border-black text-black font-semibold bg-white' : 'text-gray-600 hover:text-black hover:bg-gray-100'"
                                class="px-6 py-4 text-sm font-medium transition-all whitespace-nowrap">
                            <i class="fas fa-cog mr-2"></i>Settings
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('photographers.profile.update') }}"
                      x-data="photographerProfileForm()"
                      x-init="init(window.photographerProfileInitialData || {})">
                    @csrf
                    @method('patch')

                    <div class="p-6 md:p-8">
                        <!-- Basic Information Tab -->
                        <div x-show="activeTab === 'basic'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-black mb-4">Basic Information</h3>
                                
                                @php
                                    $countriesData = config('countries');
                                    $hasLocation = ($profile->location_country_code || $profile->location_city);
                                    $displayCountry = $profile->location_country_code ? ($countriesData[$profile->location_country_code] ?? $profile->location_country ?? '') : '';
                                    $displayCity = $profile->location_city ?? '';
                                    $displayLocation = trim(($displayCity ? $displayCity . ', ' : '') . ($displayCountry ?: ''));
                                @endphp

                                <!-- Contact Card Layout -->
                                <div class="bg-white border-2 border-gray-800 rounded-lg p-6 md:p-8">
                                    <div class="flex flex-col md:flex-row gap-6 md:gap-8">
                                        <!-- Left: Profile Photo and Logo -->
                                        <div class="flex-shrink-0 flex flex-col items-center gap-4">
                                            <div x-data="imageCropper('profile_photo', false)">
                                                <div class="relative group cursor-pointer" @click="$refs.profilePhotoInput.click()">
                                                    <div class="relative w-32 h-32 md:w-40 md:h-40 rounded-lg border-2 border-gray-800 overflow-hidden bg-gray-100">
                                                        @if($profile->profile_photo_path)
                                                            <img src="{{ asset($profile->profile_photo_path) }}" alt="Profile photo" class="w-full h-full object-cover" x-show="!previewUrl">
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                                <i class="fas fa-user text-4xl text-gray-400"></i>
                                                            </div>
                                                        @endif
                                                        <img x-show="previewUrl" :src="previewUrl" alt="Preview" class="w-full h-full object-cover absolute inset-0">
                                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                                                            <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity text-sm font-medium">
                                                                <i class="fas fa-camera mr-1"></i>Change Photo
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <input type="file" x-ref="profilePhotoInput" id="profile_photo" name="profile_photo" @change="handleFileSelect($event)" accept="image/jpeg,image/jpg,image/png,image/heic,image/heif" style="display: none;">
                                                    <input type="hidden" name="profile_photo_crop_data" x-model="cropData" />
                                                    
                                                    <!-- Crop Modal -->
                                                    <div x-show="showCropModal" x-cloak class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
                                                        <div class="bg-white rounded-lg p-6 w-full max-w-5xl max-h-[95vh] flex flex-col" @click.stop>
                                                            <h4 class="text-xl font-bold mb-2">Crop Your Photo</h4>
                                                            <p class="text-sm text-gray-600 mb-4">Drag the crop area to position your photo. Drag the handles to resize.</p>
                                                            <div class="flex-1 overflow-auto flex items-center justify-center" style="min-height: 500px; max-height: calc(95vh - 200px);">
                                                                <div class="w-full" style="max-width: 800px;">
                                                                    <img x-ref="cropImage" style="display: block; max-width: 100%; max-height: 70vh;" @load="initCropper()">
                                                                </div>
                                                            </div>
                                                            <div class="mt-4 flex justify-end gap-4">
                                                                <button type="button" @click="cancelCrop()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                                                                <button type="button" @click="applyCrop()" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Apply Crop</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Company Logo -->
                                            @if($profile->logo_path)
                                            <div x-data="logoUploader()" class="w-32 h-32 md:w-40 md:h-40">
                                                <img src="{{ asset($profile->logo_path) }}" alt="Company logo" class="w-full h-full object-contain cursor-pointer hover:opacity-75 transition-opacity" @click="$refs.logoInput.click()">
                                                <input type="file" x-ref="logoInput" id="logo" name="logo" accept="image/jpeg,image/jpg,image/png" style="display: none;" @change="handleFileSelect($event)">
                                            </div>
                                            @else
                                            <div x-data="logoUploader()" class="w-32 h-32 md:w-40 md:h-40">
                                                <div class="w-full h-full border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-gray-500 transition-colors bg-gray-50" @click="$refs.logoInput.click()" title="Add company logo">
                                                    <i class="fas fa-image text-gray-400 text-2xl"></i>
                                                </div>
                                                <input type="file" x-ref="logoInput" id="logo" name="logo" accept="image/jpeg,image/jpg,image/png" style="display: none;" @change="handleFileSelect($event)">
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Right: Contact Info -->
                                        <div class="flex-1 space-y-4">
                                            <!-- Name -->
                                            <div class="group relative" 
                                                 x-data="{ editing: false, value: '{{ old('name', $user->name) }}' }">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div x-show="!editing" 
                                                             @click="editing = true"
                                                             class="cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors">
                                                            <div class="text-xs text-gray-500 mb-1">Name</div>
                                                            <div class="text-xl font-bold text-black" x-text="value || 'Click to add name'"></div>
                                                        </div>
                                                        <div x-show="editing" x-transition class="relative">
                                                            <x-text-input 
                                                                type="text" 
                                                                x-model="value"
                                                                @blur="editing = false"
                                                                @keydown.enter="editing = false"
                                                                @keydown.escape="editing = false; value = '{{ old('name', $user->name) }}'"
                                                                class="block w-full text-xl font-bold" 
                                                                placeholder="Your name"
                                                                autofocus />
                                                            <input type="hidden" name="name" x-model="value" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Company Name -->
                                            <div class="group relative" 
                                                 x-data="{ editing: false, value: '{{ old('professional_name', $profile->professional_name ?? '') }}' }">
                                                <div x-show="!editing" 
                                                     @click="editing = true"
                                                     class="cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors">
                                                    <div class="text-xs text-gray-500 mb-1">Company</div>
                                                    <div class="text-lg font-semibold text-gray-700" x-text="value || 'Click to add company name'"></div>
                                                </div>
                                                <div x-show="editing" x-transition class="relative">
                                                    <x-text-input 
                                                        type="text" 
                                                        x-model="value"
                                                        @blur="editing = false"
                                                        @keydown.enter="editing = false"
                                                        @keydown.escape="editing = false; value = '{{ old('professional_name', $profile->professional_name ?? '') }}'"
                                                        class="block w-full text-lg font-semibold" 
                                                        placeholder="Company name"
                                                        autofocus />
                                                    <input type="hidden" name="professional_name" x-model="value" />
                                                </div>
                                            </div>

                                            <!-- Location -->
                                            <div class="group relative" 
                                                 x-data="{ 
                                                     editing: false,
                                                     init() {
                                                         this.editing = !@js($hasLocation);
                                                     }
                                                 }">
                                                <div x-show="!editing && @js($hasLocation)" 
                                                     @click="editing = true"
                                                     class="cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors">
                                                    <div class="text-xs text-gray-500 mb-1">Location</div>
                                                    <div class="text-base text-gray-700">{{ $displayLocation ?: 'Not set' }}</div>
                                                </div>
                                                <div x-show="editing || !@js($hasLocation)" 
                                                     x-transition
                                                     class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <div class="text-xs text-gray-500 mb-1">Country</div>
                                                        <div class="relative" 
                                                             x-data="searchableDropdown()" 
                                                             x-init="initCountries(@js($countriesData), '{{ old('location_country_code', $profile->location_country_code) }}')">
                                                            <div class="relative">
                                                                <x-text-input 
                                                                    id="location_country_code" 
                                                                    type="text" 
                                                                    x-model="searchInput"
                                                                    @input="filterCountries()"
                                                                    @focus="showDropdown = true; if(filteredCountries.length === 0 && countries.length > 0) { filteredCountries = countries.slice(0, 50); }"
                                                                    @blur="setTimeout(() => showDropdown = false, 200)"
                                                                    @keydown.arrow-down.prevent="highlightNext()"
                                                                    @keydown.arrow-up.prevent="highlightPrevious()"
                                                                    @keydown.enter.prevent="selectHighlighted()"
                                                                    @keydown.escape="showDropdown = false"
                                                                    class="block w-full pr-10" 
                                                                    placeholder="Type to search countries..." 
                                                                    autocomplete="off" />
                                                                <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="location_country_code" x-model="selectedValue" />
                                                            <div x-show="showDropdown && filteredCountries.length > 0" 
                                                                 x-cloak
                                                                 x-transition
                                                                 x-init="
                                                                    $watch('showDropdown', value => {
                                                                        if (value) {
                                                                            setTimeout(() => {
                                                                                const dropdown = $el;
                                                                                const input = dropdown.previousElementSibling.querySelector('input');
                                                                                const rect = input.getBoundingClientRect();
                                                                                const viewportHeight = window.innerHeight;
                                                                                const spaceBelow = viewportHeight - rect.bottom;
                                                                                const spaceAbove = rect.top;
                                                                                
                                                                                if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                                                    dropdown.classList.add('bottom-full');
                                                                                    dropdown.classList.remove('mt-1');
                                                                                    dropdown.classList.add('mb-1');
                                                                                    dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                                                } else {
                                                                                    dropdown.classList.remove('bottom-full', 'mb-1');
                                                                                    dropdown.classList.add('mt-1');
                                                                                    dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                                                }
                                                                            }, 10);
                                                                        }
                                                                    });
                                                                 "
                                                                 class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                                 style="max-height: 240px;">
                                                                <template x-for="(country, index) in filteredCountries" :key="country.code">
                                                                    <div @click="selectCountry(country); $dispatch('location-updated', {country: country.code})" 
                                                                         @mouseenter="highlightedIndex = index"
                                                                         :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === country.code, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== country.code }"
                                                                         class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                                        <div class="font-medium" x-text="country.name"></div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            <div x-show="showDropdown && filteredCountries.length === 0" 
                                                                 x-cloak
                                                                 class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl p-4 text-center text-gray-500">
                                                                No countries found
                                                            </div>
                                                        </div>
                                                        <x-input-error :messages="$errors->get('location_country_code')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <div class="text-xs text-gray-500 mb-1">City</div>
                                                        <div class="relative"
                                                             x-data="locationAutocomplete()" 
                                                             x-init="init('{{ old('location_country_code', $profile->location_country_code) }}', '{{ old('location_city', $profile->location_city) }}', {{ old('location_geoname_id', $profile->location_geoname_id ?? 'null') }})">
                                                            <x-text-input 
                                                                id="location_city" 
                                                                name="location_city" 
                                                                type="text" 
                                                                x-model="cityInput"
                                                                @input="searchCities()"
                                                                @focus="showSuggestions = true"
                                                                @blur="setTimeout(() => showSuggestions = false, 200)"
                                                                class="block w-full" 
                                                                placeholder="Start typing city name..." 
                                                                autocomplete="off" />
                                                            <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId" />
                                                            <input type="hidden" name="location_country" x-model="selectedCountryName" />
                                                            
                                                            <div x-show="showSuggestions && suggestions.length > 0" 
                                                                 x-cloak
                                                                 x-init="
                                                                    $watch('showSuggestions', value => {
                                                                        if (value) {
                                                                            setTimeout(() => {
                                                                                const dropdown = $el;
                                                                                const input = dropdown.previousElementSibling.previousElementSibling;
                                                                                const rect = input.getBoundingClientRect();
                                                                                const viewportHeight = window.innerHeight;
                                                                                const spaceBelow = viewportHeight - rect.bottom;
                                                                                const spaceAbove = rect.top;
                                                                                
                                                                                if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                                                                    dropdown.classList.add('bottom-full');
                                                                                    dropdown.classList.remove('mt-1');
                                                                                    dropdown.classList.add('mb-1');
                                                                                    dropdown.style.maxHeight = Math.min(spaceAbove - 20, 240) + 'px';
                                                                                } else {
                                                                                    dropdown.classList.remove('bottom-full', 'mb-1');
                                                                                    dropdown.classList.add('mt-1');
                                                                                    dropdown.style.maxHeight = Math.min(spaceBelow - 20, 240) + 'px';
                                                                                }
                                                                            }, 10);
                                                                        }
                                                                    });
                                                                 "
                                                                 class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                                 style="max-height: 240px;">
                                                                <template x-for="(suggestion, index) in suggestions" :key="index">
                                                                    <div @click="selectCity(suggestion); $dispatch('location-updated', {city: suggestion.city, geonameId: suggestion.id})" 
                                                                         class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                                        <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                                        <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <x-input-error :messages="$errors->get('location_city')" class="mt-2" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bio -->
                                            <div class="group relative" 
                                                 x-data="{ editing: false, value: '{{ old('bio', $profile->bio) }}' }">
                                                <div x-show="!editing" 
                                                     @click="editing = true"
                                                     class="cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors">
                                                    <div class="text-xs text-gray-500 mb-1">Bio</div>
                                                    <div class="text-sm text-gray-700 whitespace-pre-line" x-text="value || 'Click to add bio'"></div>
                                                </div>
                                                <div x-show="editing" x-transition class="relative">
                                                    <textarea 
                                                        x-model="value"
                                                        @blur="editing = false"
                                                        @keydown.escape="editing = false; value = '{{ old('bio', $profile->bio) }}'"
                                                        rows="4"
                                                        class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400" 
                                                        placeholder="Tell us about yourself and your photography style..."
                                                        autofocus></textarea>
                                                    <input type="hidden" name="bio" x-model="value" />
                                                </div>
                                                <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Tab -->
                        <div x-show="activeTab === 'professional'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-black mb-4">Professional Information</h3>
                                
                                @php
                                    $experienceLevels = [
                                        'beginner' => 'Beginner',
                                        'intermediate' => 'Intermediate',
                                        'professional' => 'Professional'
                                    ];
                                    $hasExperienceLevel = $profile->experience_level && isset($experienceLevels[$profile->experience_level]);
                                    $displayExperienceLevel = $hasExperienceLevel ? $experienceLevels[$profile->experience_level] : '';
                                @endphp

                                <div class="mb-6">
                                    <x-input-label for="experience_level" :value="__('Experience Level')" />
                                    <div x-data="{ editing: !@js($hasExperienceLevel) }">
                                        <div x-show="!editing && @js($hasExperienceLevel)" class="mt-1">
                                            <div class="flex items-center justify-between p-3 border-2 border-gray-300 rounded-md bg-gray-50">
                                                <span class="text-gray-900 font-medium">@js($displayExperienceLevel)</span>
                                                <button type="button" @click="editing = true" class="text-sm text-gray-600 hover:text-black underline">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </button>
                                            </div>
                                        </div>
                                        <div x-show="editing || !@js($hasExperienceLevel)" 
                                             x-transition
                                             class="relative mt-1" 
                                             x-data="customSelect({
                                                 options: [
                                                     { value: '', label: 'Select...' },
                                                     { value: 'beginner', label: 'Beginner' },
                                                     { value: 'intermediate', label: 'Intermediate' },
                                                     { value: 'professional', label: 'Professional' }
                                                 ],
                                                 selectedValue: '{{ old('experience_level', $profile->experience_level) }}',
                                                 onSelect: (value) => { }
                                             })">
                                            <input type="hidden" name="experience_level" x-model="selectedValue" />
                                            <div @click="showDropdown = !showDropdown" 
                                                 @click.outside="showDropdown = false"
                                                 class="block w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 pr-10 text-gray-900 bg-white cursor-pointer hover:border-gray-700">
                                                <span x-text="selectedLabel || 'Select...'" :class="selectedValue ? 'text-gray-900' : 'text-gray-400'"></span>
                                            </div>
                                            <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                            </div>
                                            <div x-show="showDropdown" 
                                                 x-cloak
                                                 x-transition
                                                 class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                 style="max-height: 240px;">
                                                <template x-for="(option, index) in options" :key="index">
                                                    <div @click="selectOption(option.value)" 
                                                         @mouseenter="highlightedIndex = index"
                                                         :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === option.value, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== option.value }"
                                                         class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                        <div class="font-medium" x-text="option.label"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="specialties" :value="__('Specialties')" />
                                    <p class="text-sm text-gray-600 mb-3">Select your photography specialties</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                        @foreach($specialtiesOptions as $key => $label)
                                            <label class="flex items-center cursor-pointer p-2 rounded hover:bg-gray-50 transition">
                                                <input type="checkbox" 
                                                       name="specialties[]" 
                                                       value="{{ $key }}"
                                                       @change="toggleSpecialty('{{ $key }}')"
                                                       :checked="specialties.includes('{{ $key }}')"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="specialties_json" :value="JSON.stringify(specialties)">
                                    <x-input-error :messages="$errors->get('specialties')" class="mt-2" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="services_offered" :value="__('Services Offered')" />
                                    <p class="text-sm text-gray-600 mb-3">Select the services you offer</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                        @foreach($servicesOptions as $key => $label)
                                            <label class="flex items-center cursor-pointer p-2 rounded hover:bg-gray-50 transition">
                                                <input type="checkbox" 
                                                       name="services_offered[]" 
                                                       value="{{ $key }}"
                                                       @change="toggleService('{{ $key }}')"
                                                       :checked="services.includes('{{ $key }}')"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="services_json" :value="JSON.stringify(services)">
                                    <x-input-error :messages="$errors->get('services_offered')" class="mt-2" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="studio_location" :value="__('Studio Location')" />
                                    @php
                                        $countriesData = config('countries');
                                        $studioLocationParts = $profile->studio_location ? explode(', ', $profile->studio_location) : [];
                                        $studioLocationCity = $studioLocationParts[0] ?? '';
                                        $studioLocationCountry = $studioLocationParts[1] ?? '';
                                        $locationCountry = $profile->location_country_code ? (config('countries')[$profile->location_country_code] ?? $profile->location_country ?? '') : ($profile->location_country ?? '');
                                    @endphp
                                    <div x-data="{ 
                                        showStudioLocationEditor: false, 
                                        studioLocationCity: @js($studioLocationCity), 
                                        studioLocationCountry: @js($studioLocationCountry), 
                                        locationCity: @js($profile->location_city ?? ''), 
                                        locationCountry: @js($locationCountry),
                                        updateStudioLocation(data) {
                                            if (data.city) this.studioLocationCity = data.city;
                                            if (data.country) this.studioLocationCountry = data.country;
                                        }
                                    }" @studio-location-updated.window="updateStudioLocation($event.detail)">
                                        <div x-show="!showStudioLocationEditor" class="mt-1">
                                            <div class="flex items-center justify-between p-3 border-2 border-gray-800 rounded-md bg-white">
                                                <div>
                                                    <span x-show="studioLocationCity || studioLocationCountry" class="text-gray-900 font-medium">
                                                        <span x-text="studioLocationCity || ''"></span><span x-show="studioLocationCity && studioLocationCountry">, </span><span x-text="studioLocationCountry || ''"></span>
                                                    </span>
                                                    <span x-show="!studioLocationCity && !studioLocationCountry" class="text-gray-400 italic">
                                                        <span x-show="locationCity || locationCountry">
                                                            <span x-text="locationCity || ''"></span><span x-show="locationCity && locationCountry">, </span><span x-text="locationCountry || ''"></span>
                                                            <span class="text-xs ml-2">(using main location)</span>
                                                        </span>
                                                        <span x-show="!locationCity && !locationCountry">No location set</span>
                                                    </span>
                                                </div>
                                                <button type="button" @click="showStudioLocationEditor = true" class="text-sm text-gray-600 hover:text-black underline">
                                                    Change Studio Location
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div x-show="showStudioLocationEditor" 
                                             x-transition
                                             class="mt-1"
                                             x-data="locationAutocomplete()" 
                                             x-init="init('{{ old('studio_location_country_code', '') }}', '{{ old('studio_location_city', $studioLocationCity) }}', {{ old('studio_location_geoname_id', 'null') }})">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <x-input-label for="studio_location_country_code" :value="__('Country')" />
                                                    <div class="relative mt-1" 
                                                         x-data="searchableDropdown()" 
                                                         x-init="initCountries(@js($countriesData), '{{ old('studio_location_country_code', '') }}')">
                                                        <div class="relative">
                                                            <x-text-input 
                                                                id="studio_location_country_code" 
                                                                type="text" 
                                                                x-model="searchInput"
                                                                @input="filterCountries()"
                                                                @focus="showDropdown = true; if(filteredCountries.length === 0 && countries.length > 0) { filteredCountries = countries.slice(0, 50); }"
                                                                @blur="setTimeout(() => showDropdown = false, 200)"
                                                                @keydown.arrow-down.prevent="highlightNext()"
                                                                @keydown.arrow-up.prevent="highlightPrevious()"
                                                                @keydown.enter.prevent="selectHighlighted()"
                                                                @keydown.escape="showDropdown = false"
                                                                class="block w-full pr-10" 
                                                                placeholder="Type to search countries..." 
                                                                autocomplete="off" />
                                                            <div class="absolute right-0 flex items-center pointer-events-none" style="top: 50%; transform: translateY(-50%); right: 12px;">
                                                                <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="studio_location_country_code" x-model="selectedValue" />
                                                        <div x-show="showDropdown && filteredCountries.length > 0" 
                                                             x-cloak
                                                             x-transition
                                                             class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                             style="max-height: 240px;">
                                                            <template x-for="(country, index) in filteredCountries" :key="country.code">
                                                                <div @click="selectCountry(country); $dispatch('studio-location-updated', {country: country.name})" 
                                                                     @mouseenter="highlightedIndex = index"
                                                                     :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === country.code, 'bg-white text-gray-900 hover:bg-gray-50': index !== highlightedIndex && selectedValue !== country.code }"
                                                                     class="px-4 py-2.5 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                                    <div class="font-medium" x-text="country.name"></div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <x-input-label for="studio_location_city" :value="__('City')" />
                                                    <div class="relative mt-1">
                                                        <x-text-input 
                                                            id="studio_location_city" 
                                                            name="studio_location_city" 
                                                            type="text" 
                                                            x-model="cityInput"
                                                            @input="searchCities()"
                                                            @focus="showSuggestions = true"
                                                            @blur="setTimeout(() => showSuggestions = false, 200)"
                                                            class="block w-full" 
                                                            placeholder="Start typing city name..." 
                                                            autocomplete="off" />
                                                        <input type="hidden" name="studio_location_geoname_id" x-model="selectedGeonameId" />
                                                        <input type="hidden" name="studio_location_country" x-model="selectedCountryName" />
                                                        
                                                        <div x-show="showSuggestions && suggestions.length > 0" 
                                                             x-cloak
                                                             class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-xl overflow-y-auto"
                                                             style="max-height: 240px;">
                                                            <template x-for="(suggestion, index) in suggestions" :key="index">
                                                                <div @click="selectCity(suggestion); const countryParts = suggestion.label.split(', '); $dispatch('studio-location-updated', {city: suggestion.city, country: countryParts.length > 1 ? countryParts[1] : ''})" 
                                                                     class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors duration-150">
                                                                    <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                                    <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center gap-4">
                                                <button type="button" @click="showStudioLocationEditor = false" class="text-sm text-gray-600 hover:text-black underline">
                                                    Cancel
                                                </button>
                                                <button type="button" @click="showStudioLocationEditor = false" class="text-sm bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                                    Save Location
                                                </button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="studio_location" :value="(studioLocationCity && studioLocationCountry) ? studioLocationCity + ', ' + studioLocationCountry : (locationCity && locationCountry) ? locationCity + ', ' + locationCountry : ''" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Optional - Where is your studio located? If not set, your main location will be used.</p>
                                    <x-input-error :messages="$errors->get('studio_location')" class="mt-2" />
                                </div>

                                <div class="mb-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="available_for_travel" value="1" {{ old('available_for_travel', $profile->available_for_travel) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                        <span class="ml-2 text-sm text-gray-700">Available for travel</span>
                                    </label>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="pricing_info" :value="__('Pricing Information')" />
                                    <textarea id="pricing_info" name="pricing_info" rows="3" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-600 focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-all duration-200 px-3 py-2 text-gray-900 placeholder-gray-400" placeholder="e.g., Starting at $500 for headshots">{{ old('pricing_info', $profile->pricing_info) }}</textarea>
                                    <x-input-error :messages="$errors->get('pricing_info')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Equipment Tab -->
                        <div x-show="activeTab === 'equipment'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-black mb-4">Equipment</h3>
                                <p class="text-sm text-gray-600 mb-6">List your photography equipment to showcase your capabilities</p>
                                
                                <!-- Cameras -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cameras</label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(item, index) in equipment.cameras" :key="index">
                                            <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                                <span x-text="item"></span>
                                                <button type="button" @click="equipment.cameras.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-text-input type="text" x-model="newCamera" @keyup.enter.prevent="if(newCamera.trim()) { equipment.cameras.push(newCamera.trim()); newCamera = ''; }" placeholder="e.g., Canon EOS R5" class="flex-1" />
                                        <button type="button" @click="if(newCamera.trim()) { equipment.cameras.push(newCamera.trim()); newCamera = ''; }" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Lenses -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lenses</label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(item, index) in equipment.lenses" :key="index">
                                            <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                                <span x-text="item"></span>
                                                <button type="button" @click="equipment.lenses.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-text-input type="text" x-model="newLens" @keyup.enter.prevent="if(newLens.trim()) { equipment.lenses.push(newLens.trim()); newLens = ''; }" placeholder="e.g., 24-70mm f/2.8" class="flex-1" />
                                        <button type="button" @click="if(newLens.trim()) { equipment.lenses.push(newLens.trim()); newLens = ''; }" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Lighting -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lighting Equipment</label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(item, index) in equipment.lighting" :key="index">
                                            <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                                <span x-text="item"></span>
                                                <button type="button" @click="equipment.lighting.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-text-input type="text" x-model="newLighting" @keyup.enter.prevent="if(newLighting.trim()) { equipment.lighting.push(newLighting.trim()); newLighting = ''; }" placeholder="e.g., Profoto B10" class="flex-1" />
                                        <button type="button" @click="if(newLighting.trim()) { equipment.lighting.push(newLighting.trim()); newLighting = ''; }" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Other Equipment -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Other Equipment</label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(item, index) in equipment.other" :key="index">
                                            <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                                <span x-text="item"></span>
                                                <button type="button" @click="equipment.other.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-text-input type="text" x-model="newOther" @keyup.enter.prevent="if(newOther.trim()) { equipment.other.push(newOther.trim()); newOther = ''; }" placeholder="e.g., Tripod, Backdrops, etc." class="flex-1" />
                                        <button type="button" @click="if(newOther.trim()) { equipment.other.push(newOther.trim()); newOther = ''; }" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="equipment" :value="JSON.stringify(equipment)">
                                <x-input-error :messages="$errors->get('equipment')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Contact & Social Tab -->
                        <div x-show="activeTab === 'contact'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-black mb-4">Contact & Social Media</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="public_email" :value="__('Public Email')" />
                                        <x-text-input id="public_email" name="public_email" type="email" class="block mt-1 w-full" :value="old('public_email', $profile->public_email)" />
                                        <x-input-error :messages="$errors->get('public_email')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="phone" :value="__('Phone')" />
                                        <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone', $profile->phone)" />
                                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="instagram" :value="__('Instagram')" />
                                        <x-text-input id="instagram" name="instagram" type="text" class="block mt-1 w-full" :value="old('instagram', $profile->instagram)" placeholder="@username" />
                                        <x-input-error :messages="$errors->get('instagram')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="facebook" :value="__('Facebook')" />
                                        <x-text-input id="facebook" name="facebook" type="text" class="block mt-1 w-full" :value="old('facebook', $profile->facebook)" />
                                        <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="twitter" :value="__('Twitter/X')" />
                                        <x-text-input id="twitter" name="twitter" type="text" class="block mt-1 w-full" :value="old('twitter', $profile->twitter)" />
                                        <x-input-error :messages="$errors->get('twitter')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label for="portfolio_website" :value="__('Portfolio Website')" />
                                        <x-text-input id="portfolio_website" name="portfolio_website" type="url" class="block mt-1 w-full" :value="old('portfolio_website', $profile->portfolio_website)" />
                                        <x-input-error :messages="$errors->get('portfolio_website')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div x-show="activeTab === 'settings'" x-transition class="space-y-6">
                            <div>
                                <h3 class="text-xl font-bold text-black mb-4">Profile Settings</h3>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition cursor-pointer">
                                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', $profile->is_public ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Make profile public</span>
                                            <p class="text-xs text-gray-500">Allow others to view your profile</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition cursor-pointer">
                                        <input type="checkbox" name="contains_nudity" value="1" {{ old('contains_nudity', $profile->contains_nudity) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-700">Portfolio contains nudity</span>
                                            <p class="text-xs text-gray-500">Mark if your portfolio includes artistic nude photography</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button (Fixed at bottom) -->
                    <div class="border-t-2 border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end">
                        <x-primary-button class="px-8 py-3">
                            <i class="fas fa-save mr-2"></i>{{ __('Save Profile') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('photographerProfileForm', () => ({
            specialties: [],
            services: [],
            equipment: {
                cameras: [],
                lenses: [],
                lighting: [],
                other: []
            },
            newCamera: '',
            newLens: '',
            newLighting: '',
            newOther: '',
            selectedCountry: '',
            cityInput: '',
            selectedGeonameId: null,
            selectedCountryName: '',
            suggestions: [],
            showSuggestions: false,
            highlightedIndex: -1,
            searchTimeout: null,
            init(initial) {
                if (!initial || typeof initial !== 'object') {
                    initial = {};
                }
                
                this.specialties = initial.specialties || [];
                this.services = initial.services || [];
                this.equipment = {
                    cameras: initial.equipment?.cameras || [],
                    lenses: initial.equipment?.lenses || [],
                    lighting: initial.equipment?.lighting || [],
                    other: initial.equipment?.other || []
                };
                
                this.selectedCountry = initial.locationCountryCode || '';
                this.cityInput = initial.locationCity || '';
                this.selectedGeonameId = initial.locationGeonameId || null;
                
                if (this.selectedCountry) {
                    const countries = @json(config('countries'));
                    this.selectedCountryName = countries[this.selectedCountry] || '';
                }
            },
            toggleSpecialty(value) {
                const index = this.specialties.indexOf(value);
                if (index > -1) {
                    this.specialties.splice(index, 1);
                } else {
                    this.specialties.push(value);
                }
            },
            toggleService(value) {
                const index = this.services.indexOf(value);
                if (index > -1) {
                    this.services.splice(index, 1);
                } else {
                    this.services.push(value);
                }
            },
            onCountryChange() {
                this.cityInput = '';
                this.selectedGeonameId = null;
                this.selectedCountryName = '';
                this.suggestions = [];
                this.showSuggestions = false;
            },
            searchCities() {
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                
                if (!this.selectedCountry || this.cityInput.length < 2) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }
                
                this.searchTimeout = setTimeout(() => {
                    fetch(`/api/locations?q=${encodeURIComponent(this.cityInput)}&country=${this.selectedCountry}&limit=10`)
                        .then(response => response.json())
                        .then(data => {
                            this.suggestions = data.data || [];
                            this.showSuggestions = this.suggestions.length > 0;
                            this.highlightedIndex = -1;
                        })
                        .catch(error => {
                            console.error('Error fetching cities:', error);
                            this.suggestions = [];
                        });
                }, 300);
            },
            selectCity(suggestion) {
                this.cityInput = suggestion.city;
                this.selectedGeonameId = suggestion.id;
                this.selectedCountryName = suggestion.country_name;
                this.suggestions = [];
                this.showSuggestions = false;
            }
        }));
    });
    
    function searchableDropdown() {
        return {
            countries: [],
            filteredCountries: [],
            searchInput: '',
            selectedValue: '',
            selectedLabel: '',
            showDropdown: false,
            highlightedIndex: -1,
            
            initCountries(countriesList, selectedCode) {
                if (!countriesList || typeof countriesList !== 'object' || Object.keys(countriesList).length === 0) {
                    console.error('No countries data provided', countriesList);
                    this.filteredCountries = [];
                    return;
                }
                
                this.countries = Object.keys(countriesList).map(code => ({
                    code: code,
                    name: countriesList[code]
                })).sort((a, b) => a.name.localeCompare(b.name));
                
                this.filteredCountries = this.countries.slice(0, 50);
                
                if (selectedCode) {
                    const selected = this.countries.find(c => c.code === selectedCode);
                    if (selected) {
                        this.selectedValue = selected.code;
                        this.selectedLabel = selected.name;
                        this.searchInput = selected.name;
                    }
                }
            },
            
            filterCountries() {
                if (!this.countries || this.countries.length === 0) {
                    return;
                }
                
                const search = this.searchInput.toLowerCase().trim();
                if (!search) {
                    this.filteredCountries = this.countries.slice(0, 50);
                } else {
                    this.filteredCountries = this.countries.filter(country => 
                        country.name.toLowerCase().includes(search) || 
                        country.code.toLowerCase().includes(search)
                    );
                }
                this.highlightedIndex = -1;
            },
            
            selectCountry(country) {
                this.selectedValue = country.code;
                this.selectedLabel = country.name;
                this.searchInput = country.name;
                this.showDropdown = false;
                if (window.locationAutocompleteInstance) {
                    window.locationAutocompleteInstance.selectedCountry = country.code;
                    window.locationAutocompleteInstance.onCountryChange();
                }
            },
            
            highlightNext() {
                if (this.highlightedIndex < this.filteredCountries.length - 1) {
                    this.highlightedIndex++;
                }
            },
            
            highlightPrevious() {
                if (this.highlightedIndex > 0) {
                    this.highlightedIndex--;
                }
            },
            
            selectHighlighted() {
                if (this.highlightedIndex !== -1 && this.filteredCountries[this.highlightedIndex]) {
                    this.selectCountry(this.filteredCountries[this.highlightedIndex]);
                }
            }
        };
    }
    
    function customSelect(config) {
        return {
            options: config.options || [],
            selectedValue: config.selectedValue || '',
            selectedLabel: '',
            showDropdown: false,
            highlightedIndex: -1,
            
            init() {
                const selected = this.options.find(opt => opt.value === this.selectedValue);
                if (selected) {
                    this.selectedLabel = selected.label;
                }
            },
            
            selectOption(value) {
                this.selectedValue = value;
                const selected = this.options.find(opt => opt.value === value);
                this.selectedLabel = selected ? selected.label : '';
                this.showDropdown = false;
                if (config.onSelect) {
                    config.onSelect(value);
                }
            }
        };
    }
    
    function locationAutocomplete() {
        return {
            selectedCountry: '',
            cityInput: '',
            selectedGeonameId: null,
            selectedCountryName: '',
            suggestions: [],
            showSuggestions: false,
            highlightedIndex: -1,
            searchTimeout: null,
            
            init(countryCode, cityName, geonameId) {
                this.selectedCountry = countryCode || '';
                this.cityInput = cityName || '';
                this.selectedGeonameId = geonameId || null;
                
                window.locationAutocompleteInstance = this;
                
                if (countryCode) {
                    const countries = @json(config('countries'));
                    this.selectedCountryName = countries[countryCode] || '';
                }
            },
            
            onCountryChange() {
                this.cityInput = '';
                this.selectedGeonameId = null;
                this.selectedCountryName = '';
                this.suggestions = [];
                this.showSuggestions = false;
            },
            
            searchCities() {
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                
                if (!this.selectedCountry || this.cityInput.length < 2) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }
                
                this.searchTimeout = setTimeout(() => {
                    fetch(`/api/locations?q=${encodeURIComponent(this.cityInput)}&country=${this.selectedCountry}&limit=10`)
                        .then(response => response.json())
                        .then(data => {
                            this.suggestions = data.data || [];
                            this.showSuggestions = this.suggestions.length > 0;
                            this.highlightedIndex = -1;
                        })
                        .catch(error => {
                            console.error('Error fetching cities:', error);
                            this.suggestions = [];
                        });
                }, 300);
            },
            
            selectCity(suggestion) {
                this.cityInput = suggestion.city;
                this.selectedGeonameId = suggestion.id;
                this.selectedCountryName = suggestion.country_name;
                this.suggestions = [];
                this.showSuggestions = false;
            }
        };
    }
    
    function imageCropper(fieldName, isRequired = false) {
        return {
            showCropModal: false,
            previewUrl: null,
            cropData: null,
            originalFile: null,
            cropper: null,
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                this.originalFile = file;
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    const img = this.$refs.cropImage;
                    // Reset image source to trigger load event
                    img.src = '';
                    this.showCropModal = true;
                    // Wait for modal to show, then set image source
                    this.$nextTick(() => {
                        img.src = e.target.result;
                        // Cropper will initialize when image loads (@load event)
                    });
                };
                
                reader.readAsDataURL(file);
            },
            
            initCropper() {
                const image = this.$refs.cropImage;
                if (!image || !image.complete) return;
                
                // Destroy existing cropper if any
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
                
                // Wait a tiny bit to ensure image is fully rendered
                setTimeout(() => {
                    if (!image || !this.showCropModal) return;
                    
                    // Initialize Cropper.js with square aspect ratio (1:1)
                    this.cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        minCropBoxWidth: 50,
                        minCropBoxHeight: 50,
                        ready: () => {
                            // Cropper is ready
                            console.log('Cropper initialized');
                        }
                    });
                }, 100);
            },
            
            cancelCrop() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
                // Clear the image source
                const img = this.$refs.cropImage;
                if (img) {
                    img.src = '';
                }
                this.showCropModal = false;
                // Don't clear originalFile - allow reopening
            },
            
            applyCrop() {
                if (!this.cropper) return;
                
                // Get cropped canvas from Cropper.js
                const canvas = this.cropper.getCroppedCanvas({
                    width: 800,
                    height: 800,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                
                // Store crop data
                const cropData = this.cropper.getData();
                this.cropData = JSON.stringify(cropData);
                
                // Create preview
                this.previewUrl = canvas.toDataURL('image/jpeg', 0.9);
                
                // Update file input with cropped image
                canvas.toBlob((blob) => {
                    const croppedFile = new File([blob], this.originalFile.name, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    const fileInput = document.getElementById(fieldName);
                    if (fileInput) {
                        fileInput.files = dataTransfer.files;
                    }
                }, 'image/jpeg', 0.9);
                
                // Clean up
                this.cropper.destroy();
                this.cropper = null;
                this.showCropModal = false;
            }
        };
    }
    
    function logoUploader() {
        return {
            previewUrl: null,
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const maxSize = 800;
                        let width = img.width;
                        let height = img.height;
                        
                        if (width > height) {
                            if (width > maxSize) {
                                height = (height / width) * maxSize;
                                width = maxSize;
                            }
                        } else {
                            if (height > maxSize) {
                                width = (width / height) * maxSize;
                                height = maxSize;
                            }
                        }
                        
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        
                        // Preserve transparency for PNG files
                        const isPNG = file.type === 'image/png';
                        if (isPNG) {
                            // Clear canvas with transparent background
                            ctx.clearRect(0, 0, width, height);
                        } else {
                            // Fill with white background for JPEG
                            ctx.fillStyle = '#FFFFFF';
                            ctx.fillRect(0, 0, width, height);
                        }
                        
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        // Preserve PNG format if original is PNG, otherwise use JPEG
                        if (isPNG) {
                            canvas.toBlob((blob) => {
                                const resizedFile = new File([blob], file.name.replace(/\.(jpg|jpeg)$/i, '.png'), { type: 'image/png' });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(resizedFile);
                                event.target.files = dataTransfer.files;
                            }, 'image/png');
                        } else {
                            canvas.toBlob((blob) => {
                                const resizedFile = new File([blob], file.name.replace(/\.(png)$/i, '.jpg'), { type: 'image/jpeg' });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(resizedFile);
                                event.target.files = dataTransfer.files;
                            }, 'image/jpeg', 0.9);
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }
</script>
