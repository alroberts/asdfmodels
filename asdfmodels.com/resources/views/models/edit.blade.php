<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Model Profile') }}
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

                <form method="POST" action="{{ route('profile.model.update') }}">
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-data="locationAutocomplete()" x-init="init('{{ old('location_country_code', $profile->location_country_code) }}', '{{ old('location_city', $profile->location_city) }}', {{ old('location_geoname_id', $profile->location_geoname_id) ?? 'null' }})">
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="block mt-1 w-full" :value="old('date_of_birth', $profile->date_of_birth?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gender" :value="__('Gender')" />
                                <select id="gender" name="gender" class="block mt-1 w-full border-2 border-black rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">Select...</option>
                                    <option value="male" {{ old('gender', $profile->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $profile->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $profile->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Physical Stats - Gender Specific -->
                    <div class="mb-6" x-data="{ gender: '{{ old('gender', $profile->gender) }}' }">
                        <h3 class="text-lg font-semibold text-black mb-4">Physical Stats</h3>
                        
                        <!-- Common Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="height" :value="__('Height')" />
                                <x-text-input id="height" name="height" type="text" class="block mt-1 w-full" :value="old('height', $profile->height)" placeholder="e.g., 5'10&quot; or 178cm" />
                                <x-input-error :messages="$errors->get('height')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="weight" :value="__('Weight')" />
                                <x-text-input id="weight" name="weight" type="text" class="block mt-1 w-full" :value="old('weight', $profile->weight)" placeholder="e.g., 70kg or 154lbs" />
                                <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Male Fields -->
                        <div x-show="gender === 'male'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="chest" :value="__('Chest')" />
                                <x-text-input id="chest" name="chest" type="text" class="block mt-1 w-full" :value="old('chest', $profile->chest)" />
                                <x-input-error :messages="$errors->get('chest')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="waist" :value="__('Waist')" />
                                <x-text-input id="waist" name="waist" type="text" class="block mt-1 w-full" :value="old('waist', $profile->waist)" />
                                <x-input-error :messages="$errors->get('waist')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="inseam" :value="__('Inseam')" />
                                <x-text-input id="inseam" name="inseam" type="text" class="block mt-1 w-full" :value="old('inseam', $profile->inseam)" />
                                <x-input-error :messages="$errors->get('inseam')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="suit_size" :value="__('Suit Size')" />
                                <x-text-input id="suit_size" name="suit_size" type="text" class="block mt-1 w-full" :value="old('suit_size', $profile->suit_size)" />
                                <x-input-error :messages="$errors->get('suit_size')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Female Fields -->
                        <div x-show="gender === 'female'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="bust" :value="__('Bust')" />
                                <x-text-input id="bust" name="bust" type="text" class="block mt-1 w-full" :value="old('bust', $profile->bust)" />
                                <x-input-error :messages="$errors->get('bust')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="waist" :value="__('Waist')" />
                                <x-text-input id="waist" name="waist" type="text" class="block mt-1 w-full" :value="old('waist', $profile->waist)" />
                                <x-input-error :messages="$errors->get('waist')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="hips" :value="__('Hips')" />
                                <x-text-input id="hips" name="hips" type="text" class="block mt-1 w-full" :value="old('hips', $profile->hips)" />
                                <x-input-error :messages="$errors->get('hips')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="dress_size" :value="__('Dress Size')" />
                                <x-text-input id="dress_size" name="dress_size" type="text" class="block mt-1 w-full" :value="old('dress_size', $profile->dress_size)" />
                                <x-input-error :messages="$errors->get('dress_size')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="shoe_size" :value="__('Shoe Size')" />
                                <x-text-input id="shoe_size" name="shoe_size" type="text" class="block mt-1 w-full" :value="old('shoe_size', $profile->shoe_size)" />
                                <x-input-error :messages="$errors->get('shoe_size')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="hair_color" :value="__('Hair Color')" />
                                <x-text-input id="hair_color" name="hair_color" type="text" class="block mt-1 w-full" :value="old('hair_color', $profile->hair_color)" />
                                <x-input-error :messages="$errors->get('hair_color')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="eye_color" :value="__('Eye Color')" />
                                <x-text-input id="eye_color" name="eye_color" type="text" class="block mt-1 w-full" :value="old('eye_color', $profile->eye_color)" />
                                <x-input-error :messages="$errors->get('eye_color')" class="mt-2" />
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
                                <x-input-label for="instagram" :value="__('Instagram')" />
                                <x-text-input id="instagram" name="instagram" type="text" class="block mt-1 w-full" :value="old('instagram', $profile->instagram)" placeholder="@username" />
                                <x-input-error :messages="$errors->get('instagram')" class="mt-2" />
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
                
                // Set country name from code if we have it
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
</script>

