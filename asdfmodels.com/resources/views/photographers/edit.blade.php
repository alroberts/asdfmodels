<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Photographer Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                @if(!$profile->isVerified())
                    <div class="mb-6 bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4">
                        <h3 class="font-semibold text-yellow-800 mb-2">Get Verified</h3>
                        <p class="text-yellow-700 mb-3">Increase your credibility by getting verified. Submit an ID document or video identification.</p>
                        <a href="{{ route('verification.create') }}" class="inline-block bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                            Submit Verification
                        </a>
                    </div>
                @else
                    <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-lg p-4">
                        <p class="text-green-800">
                            <i class="fas fa-check-circle"></i> Your profile is verified!
                        </p>
                    </div>
                @endif

                @php
                    $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
                    $servicesOptions = \App\Helpers\PhotographerOptions::services();
                    $oldSpecialties = old('specialties', $profile->specialties ?? []);
                    $oldServices = old('services_offered', $profile->services_offered ?? []);
                    
                    // Ensure arrays for Alpine.js
                    if (!is_array($oldSpecialties)) {
                        $oldSpecialties = [];
                    }
                    if (!is_array($oldServices)) {
                        $oldServices = [];
                    }
                    
                    // Handle equipment structure - convert old array format to new structured format
                    $oldEquipment = old('equipment', $profile->equipment ?? []);
                    if (is_array($oldEquipment) && !isset($oldEquipment['cameras'])) {
                        // Old format: simple array, convert to new structured format
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
                    
                    // Build initial data for Alpine.js
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
                <form method="POST" action="{{ route('photographers.profile.update') }}"
                      x-data="photographerProfileForm()"
                      x-init="init(window.photographerProfileInitialData || {})">
                    @csrf
                    @method('patch')

                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-black mb-4">Basic Information</h3>
                        
                        <div class="mb-4">
                            <x-input-label for="bio" :value="__('Bio')" />
                            <textarea id="bio" name="bio" rows="4" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">{{ old('bio', $profile->bio) }}</textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="location_country_code" :value="__('Country')" />
                                <select id="location_country_code" name="location_country_code" x-model="selectedCountry" @change="onCountryChange()" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">Select Country...</option>
                                    @foreach(config('countries') as $code => $name)
                                        <option value="{{ $code }}" {{ old('location_country_code', $profile->location_country_code) === $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('location_country_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="location_city" :value="__('City')" />
                                <div class="relative">
                                    <x-text-input 
                                        id="location_city" 
                                        name="location_city" 
                                        type="text" 
                                        x-model="cityInput"
                                        @input="searchCities()"
                                        @focus="showSuggestions = true"
                                        @blur="setTimeout(() => showSuggestions = false, 200)"
                                        class="block mt-1 w-full" 
                                        :value="old('location_city', $profile->location_city)" 
                                        placeholder="Start typing city name..." 
                                        autocomplete="off" />
                                    <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId" />
                                    <input type="hidden" name="location_country" x-model="selectedCountryName" />
                                    
                                    <div x-show="showSuggestions && suggestions.length > 0" 
                                         x-cloak
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-black rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <template x-for="(suggestion, index) in suggestions" :key="index">
                                            <div @click="selectCity(suggestion)" 
                                                 class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
                                                 :class="{ 'bg-gray-100': index === highlightedIndex }">
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

                    <!-- Professional Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-black mb-4">Professional Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="experience_level" :value="__('Experience Level')" />
                                <select id="experience_level" name="experience_level" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">Select...</option>
                                    <option value="beginner" {{ old('experience_level', $profile->experience_level) === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="intermediate" {{ old('experience_level', $profile->experience_level) === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="professional" {{ old('experience_level', $profile->experience_level) === 'professional' ? 'selected' : '' }}>Professional</option>
                                </select>
                                <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="specialties" :value="__('Specialties')" />
                            <p class="text-sm text-gray-600 mb-2">Select your photography specialties</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($specialtiesOptions as $key => $label)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="{{ $key }}"
                                               @change="toggleSpecialty('{{ $key }}')"
                                               :checked="specialties.includes('{{ $key }}')"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="hidden" name="specialties_json" :value="JSON.stringify(specialties)">
                            <x-input-error :messages="$errors->get('specialties')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="equipment" :value="__('Equipment')" />
                            <p class="text-sm text-gray-600 mb-2">List your photography equipment</p>
                            
                            <!-- Cameras -->
                            <div class="mb-4">
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
                                    <button type="button" @click="if(newCamera.trim()) { equipment.cameras.push(newCamera.trim()); newCamera = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Lenses -->
                            <div class="mb-4">
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
                                    <button type="button" @click="if(newLens.trim()) { equipment.lenses.push(newLens.trim()); newLens = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Lighting -->
                            <div class="mb-4">
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
                                    <button type="button" @click="if(newLighting.trim()) { equipment.lighting.push(newLighting.trim()); newLighting = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Other Equipment -->
                            <div class="mb-4">
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
                                    <button type="button" @click="if(newOther.trim()) { equipment.other.push(newOther.trim()); newOther = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="equipment" :value="JSON.stringify(equipment)">
                            <x-input-error :messages="$errors->get('equipment')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="services_offered" :value="__('Services Offered')" />
                            <p class="text-sm text-gray-600 mb-2">Select the services you offer</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($servicesOptions as $key => $label)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               name="services_offered[]" 
                                               value="{{ $key }}"
                                               @change="toggleService('{{ $key }}')"
                                               :checked="services.includes('{{ $key }}')"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="hidden" name="services_json" :value="JSON.stringify(services)">
                            <x-input-error :messages="$errors->get('services_offered')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="studio_location" :value="__('Studio Location')" />
                                <x-text-input id="studio_location" name="studio_location" type="text" class="block mt-1 w-full" :value="old('studio_location', $profile->studio_location)" placeholder="City, Country or Address" />
                                <p class="mt-1 text-xs text-gray-500">Note: Future integration with mapping services (OpenStreetMap/Google Maps) is planned</p>
                                <x-input-error :messages="$errors->get('studio_location')" class="mt-2" />
                            </div>

                            <div>
                                <label class="flex items-center mt-6">
                                    <input type="checkbox" name="available_for_travel" value="1" {{ old('available_for_travel', $profile->available_for_travel) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Available for travel</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="pricing_info" :value="__('Pricing Information')" />
                            <textarea id="pricing_info" name="pricing_info" rows="3" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="e.g., Starting at $500 for headshots">{{ old('pricing_info', $profile->pricing_info) }}</textarea>
                            <x-input-error :messages="$errors->get('pricing_info')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-black mb-4">Contact Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <!-- Settings -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-black mb-4">Settings</h3>
                        
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $profile->is_public ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Make profile public</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="contains_nudity" value="1" {{ old('contains_nudity', $profile->contains_nudity) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Portfolio contains nudity</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>
                            {{ __('Save Profile') }}
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
            // Location autocomplete
            selectedCountry: '',
            cityInput: '',
            selectedGeonameId: null,
            selectedCountryName: '',
            suggestions: [],
            showSuggestions: false,
            highlightedIndex: -1,
            searchTimeout: null,
            init(initial) {
                // Safety check - ensure initial is an object
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
                
                // Initialize location fields
                this.selectedCountry = initial.locationCountryCode || '';
                this.cityInput = initial.locationCity || '';
                this.selectedGeonameId = initial.locationGeonameId || null;
                
                // Set country name from code if we have it
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
</script>

