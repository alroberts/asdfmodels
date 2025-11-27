<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Model Profile') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="modelProfileWizard()" x-init="init()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center flex-1">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                                             :class="currentStep > index ? 'bg-black border-black text-white' : (currentStep === index ? 'border-black bg-white text-black' : 'border-gray-300 bg-white text-gray-400')">
                                            <span x-show="currentStep > index"><i class="fas fa-check text-sm"></i></span>
                                            <span x-show="currentStep <= index" x-text="index + 1" class="font-semibold"></span>
                                        </div>
                                        <div class="ml-3 hidden md:block">
                                            <div class="text-sm font-medium" 
                                                 :class="currentStep >= index ? 'text-black' : 'text-gray-400'"
                                                 x-text="step.title"></div>
                                        </div>
                                    </div>
                                    <div x-show="index < steps.length - 1" class="flex-1 mx-4 h-0.5 transition-all duration-300"
                                         :class="currentStep > index ? 'bg-black' : 'bg-gray-300'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="text-center md:hidden">
                    <div class="text-sm font-medium text-black" x-text="steps[currentStep].title"></div>
                    <div class="text-xs text-gray-500 mt-1">Step <span x-text="currentStep + 1"></span> of <span x-text="steps.length"></span></div>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded border-2 border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Wizard Form -->
            <form method="POST" action="{{ route('profile.model.update') }}" @submit.prevent="saveProfile()" id="profileForm">
                @csrf
                @method('patch')

                @php
                    $countriesData = config('countries');
                @endphp

                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Tell Us About Yourself</h3>
                        <p class="text-gray-600">Let's start with the basics</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="bio" :value="__('Bio')" />
                            <textarea id="bio" name="bio" rows="4" x-model="formData.bio" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="Tell us about yourself, your experience, and what makes you unique..."></textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="locationAutocomplete()" x-init="init(formData.locationCountryCode || '', formData.locationCity || '', formData.locationGeonameId || null)">
                            <div>
                                <x-input-label for="location_country_code" :value="__('Country')" />
                                @php
                                    $countriesJson = json_encode($countriesData, JSON_HEX_APOS | JSON_HEX_QUOT);
                                @endphp
                                <div class="relative" 
                                     x-data="searchableDropdown()" 
                                     data-countries="{{ $countriesJson }}"
                                     x-init="
                                        const countriesJson = $el.getAttribute('data-countries');
                                        if (countriesJson) {
                                            try {
                                                const countriesData = JSON.parse(countriesJson);
                                                initCountries(countriesData, formData.locationCountryCode || '');
                                            } catch(e) {
                                                console.error('Error parsing countries:', e);
                                            }
                                        }
                                     ">
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
                                            class="block mt-1 w-full pr-10" 
                                            placeholder="Type to search countries..." 
                                            autocomplete="off" />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="location_country_code" x-model="selectedValue" />
                                    <div x-show="showDropdown && filteredCountries.length > 0" 
                                         x-cloak
                                         x-transition
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <template x-for="(country, index) in filteredCountries" :key="country.code">
                                            <div @click="selectCountry(country); $dispatch('location-updated', {country: country.code})" 
                                                 @mouseenter="highlightedIndex = index"
                                                 :class="{ 'bg-gray-800 text-white': index === highlightedIndex || selectedValue === country.code, 'bg-white text-gray-900': index !== highlightedIndex && selectedValue !== country.code }"
                                                 class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors">
                                                <div class="font-medium" x-text="country.name"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="showDropdown && filteredCountries.length === 0" 
                                         x-cloak
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-lg p-4 text-center text-gray-500">
                                        No countries found
                                    </div>
                                </div>
                                <x-input-error :messages="$errors?->get('location_country_code') ?? []" class="mt-2" />
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
                                        placeholder="Start typing city name..." 
                                        autocomplete="off" />
                                    <input type="hidden" name="location_geoname_id" x-model="selectedGeonameId" />
                                    <input type="hidden" name="location_country" x-model="selectedCountryName" />
                                    
                                    <div x-show="showSuggestions && suggestions.length > 0" 
                                         x-cloak
                                         class="absolute z-50 w-full mt-1 bg-white border-2 border-gray-800 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <template x-for="(suggestion, index) in suggestions" :key="index">
                                            <div @click="selectCity(suggestion); $dispatch('location-updated', {city: suggestion.city, geonameId: suggestion.id})" 
                                                 class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0">
                                                <div class="font-medium text-black" x-text="suggestion.city"></div>
                                                <div class="text-sm text-gray-600" x-text="suggestion.label"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('location_city')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="gender" :value="__('Gender')" />
                                <select id="gender" name="gender" x-model="formData.gender" @change="updateGenderFields()" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">Select...</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="experience_start_year" :value="__('What year did you start modeling?')" />
                                <x-text-input id="experience_start_year" name="experience_start_year" type="number" x-model="formData.experience_start_year" class="block mt-1 w-full" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2020" />
                                <p class="mt-1 text-xs text-gray-500">Optional - helps show your experience level</p>
                                <x-input-error :messages="$errors->get('experience_start_year')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                            <x-text-input id="date_of_birth" name="date_of_birth" type="date" x-model="formData.date_of_birth" class="block mt-1 w-full" max="{{ date('Y-m-d') }}" />
                            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Physical Stats -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Physical Stats</h3>
                        <p class="text-gray-600">Help photographers find the right fit for their projects</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Common Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="height" :value="__('Height')" />
                                <x-text-input id="height" name="height" type="text" x-model="formData.height" class="block mt-1 w-full" placeholder="e.g., 5'10&quot; or 178cm" />
                                <x-input-error :messages="$errors->get('height')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="weight" :value="__('Weight')" />
                                <x-text-input id="weight" name="weight" type="text" x-model="formData.weight" class="block mt-1 w-full" placeholder="e.g., 70kg or 154lbs" />
                                <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Male Fields -->
                        <div x-show="formData.gender === 'male'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="chest" :value="__('Chest')" />
                                <x-text-input id="chest" name="chest" type="text" x-model="formData.chest" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('chest')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="waist" :value="__('Waist')" />
                                <x-text-input id="waist" name="waist" type="text" x-model="formData.waist" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('waist')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="inseam" :value="__('Inseam')" />
                                <x-text-input id="inseam" name="inseam" type="text" x-model="formData.inseam" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('inseam')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="suit_size" :value="__('Suit Size')" />
                                <x-text-input id="suit_size" name="suit_size" type="text" x-model="formData.suit_size" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('suit_size')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Female Fields -->
                        <div x-show="formData.gender === 'female'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="bust" :value="__('Bust')" />
                                <x-text-input id="bust" name="bust" type="text" x-model="formData.bust" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('bust')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="waist" :value="__('Waist')" />
                                <x-text-input id="waist" name="waist" type="text" x-model="formData.waist" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('waist')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hips" :value="__('Hips')" />
                                <x-text-input id="hips" name="hips" type="text" x-model="formData.hips" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('hips')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="dress_size" :value="__('Dress Size')" />
                                <x-text-input id="dress_size" name="dress_size" type="text" x-model="formData.dress_size" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('dress_size')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="shoe_size" :value="__('Shoe Size')" />
                                <x-text-input id="shoe_size" name="shoe_size" type="text" x-model="formData.shoe_size" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('shoe_size')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="hair_color" :value="__('Hair Color')" />
                                <x-text-input id="hair_color" name="hair_color" type="text" x-model="formData.hair_color" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('hair_color')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="eye_color" :value="__('Eye Color')" />
                                <x-text-input id="eye_color" name="eye_color" type="text" x-model="formData.eye_color" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('eye_color')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Professional Information -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Professional Details</h3>
                        <p class="text-gray-600">Tell us about your modeling experience</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="experience_level" :value="__('Experience Level')" />
                            <select id="experience_level" name="experience_level" x-model="formData.experience_level" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                <option value="">Select...</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="professional">Professional</option>
                            </select>
                            <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Specialties')" />
                            <p class="text-sm text-gray-600 mb-4">Select the types of modeling you specialize in</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @php
                                    $specialties = ['fashion', 'commercial', 'beauty', 'editorial', 'runway', 'fitness', 'artistic', 'portrait', 'lifestyle', 'glamour'];
                                @endphp
                                @foreach($specialties as $specialty)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.specialties.includes('{{ $specialty }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="{{ $specialty }}"
                                               x-model="formData.specialties"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.specialties.includes('{{ $specialty }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1 capitalize"
                                              :class="formData.specialties.includes('{{ $specialty }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $specialty }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('specialties') ?? []" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 4: Contact & Social -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Contact & Social Links</h3>
                        <p class="text-gray-600">How can people reach you?</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="public_email" :value="__('Public Email')" />
                            <x-text-input id="public_email" name="public_email" type="email" x-model="formData.public_email" class="block mt-1 w-full" placeholder="your@email.com" />
                            <p class="mt-1 text-xs text-gray-500">This will be visible on your public profile</p>
                            <x-input-error :messages="$errors->get('public_email')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="instagram" :value="__('Instagram')" />
                                <x-text-input id="instagram" name="instagram" type="text" x-model="formData.instagram" class="block mt-1 w-full" placeholder="@username" />
                                <x-input-error :messages="$errors->get('instagram')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="portfolio_website" :value="__('Portfolio Website')" />
                                <x-text-input id="portfolio_website" name="portfolio_website" type="url" x-model="formData.portfolio_website" class="block mt-1 w-full" placeholder="https://yourportfolio.com" />
                                <x-input-error :messages="$errors->get('portfolio_website')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Settings -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Almost Done!</h3>
                        <p class="text-gray-600">Final settings for your profile</p>
                    </div>

                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_public" value="1" x-model="formData.is_public" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Make profile public</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">When enabled, your profile will be visible to photographers and other users</p>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="contains_nudity" value="1" x-model="formData.contains_nudity" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Portfolio contains nudity</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">Check this if your portfolio includes artistic or fashion photography with nudity</p>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for all form data -->
                <input type="hidden" name="bio" x-model="formData.bio" />
                <input type="hidden" name="gender" x-model="formData.gender" />
                <input type="hidden" name="experience_start_year" x-model="formData.experience_start_year" />
                <input type="hidden" name="date_of_birth" x-model="formData.date_of_birth" />
                <input type="hidden" name="height" x-model="formData.height" />
                <input type="hidden" name="weight" x-model="formData.weight" />
                <input type="hidden" name="chest" x-model="formData.chest" />
                <input type="hidden" name="waist" x-model="formData.waist" />
                <input type="hidden" name="inseam" x-model="formData.inseam" />
                <input type="hidden" name="suit_size" x-model="formData.suit_size" />
                <input type="hidden" name="bust" x-model="formData.bust" />
                <input type="hidden" name="hips" x-model="formData.hips" />
                <input type="hidden" name="dress_size" x-model="formData.dress_size" />
                <input type="hidden" name="shoe_size" x-model="formData.shoe_size" />
                <input type="hidden" name="hair_color" x-model="formData.hair_color" />
                <input type="hidden" name="eye_color" x-model="formData.eye_color" />
                <input type="hidden" name="experience_level" x-model="formData.experience_level" />
                <template x-for="specialty in formData.specialties" :key="specialty">
                    <input type="hidden" name="specialties[]" :value="specialty" />
                </template>
                <input type="hidden" name="public_email" x-model="formData.public_email" />
                <input type="hidden" name="instagram" x-model="formData.instagram" />
                <input type="hidden" name="portfolio_website" x-model="formData.portfolio_website" />
                <input type="hidden" name="is_public" :value="formData.is_public ? 1 : 0" />
                <input type="hidden" name="contains_nudity" :value="formData.contains_nudity ? 1 : 0" />
            </form>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between items-center">
                <button type="button" 
                        @click="previousStep()" 
                        x-show="currentStep > 0"
                        class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <div class="flex-1"></div>
                <button type="button" 
                        @click="nextStep()" 
                        x-show="currentStep < steps.length - 1"
                        class="px-6 py-3 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition">
                    Continue <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button type="button" 
                        @click="saveProfile()" 
                        x-show="currentStep === steps.length - 1"
                        class="px-6 py-3 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition">
                    <i class="fas fa-check mr-2"></i> Complete Profile
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function modelProfileWizard() {
        return {
            currentStep: 0,
            steps: [
                { title: 'Basic Info', icon: 'user' },
                { title: 'Physical Stats', icon: 'ruler' },
                { title: 'Professional', icon: 'briefcase' },
                { title: 'Contact', icon: 'envelope' },
                { title: 'Settings', icon: 'cog' }
            ],
            formData: {
                bio: '',
                locationCountryCode: '',
                locationCity: '',
                locationGeonameId: null,
                gender: '',
                experience_start_year: '',
                date_of_birth: '',
                height: '',
                weight: '',
                chest: '',
                waist: '',
                inseam: '',
                suit_size: '',
                bust: '',
                hips: '',
                dress_size: '',
                shoe_size: '',
                hair_color: '',
                eye_color: '',
                experience_level: '',
                specialties: [],
                public_email: '',
                instagram: '',
                portfolio_website: '',
                is_public: true,
                contains_nudity: false
            },
            init() {
                // Load existing profile data
                @if(isset($profile))
                    this.formData = {
                        bio: @json($profile->bio ?? ''),
                        locationCountryCode: @json($profile->location_country_code ?? ''),
                        locationCity: @json($profile->location_city ?? ''),
                        locationGeonameId: @json($profile->location_geoname_id ?? null),
                        gender: @json($profile->gender ?? ''),
                        experience_start_year: @json($profile->experience_start_year ?? ''),
                        date_of_birth: @json($profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : ''),
                        height: @json($profile->height ?? ''),
                        weight: @json($profile->weight ?? ''),
                        chest: @json($profile->chest ?? ''),
                        waist: @json($profile->waist ?? ''),
                        inseam: @json($profile->inseam ?? ''),
                        suit_size: @json($profile->suit_size ?? ''),
                        bust: @json($profile->bust ?? ''),
                        hips: @json($profile->hips ?? ''),
                        dress_size: @json($profile->dress_size ?? ''),
                        shoe_size: @json($profile->shoe_size ?? ''),
                        hair_color: @json($profile->hair_color ?? ''),
                        eye_color: @json($profile->eye_color ?? ''),
                        experience_level: @json($profile->experience_level ?? ''),
                        specialties: @json($profile->specialties ?? []),
                        public_email: @json($profile->public_email ?? ''),
                        instagram: @json($profile->instagram ?? ''),
                        portfolio_website: @json($profile->portfolio_website ?? ''),
                        is_public: @json($profile->is_public ?? true),
                        contains_nudity: @json($profile->contains_nudity ?? false)
                    };
                @endif
            },
            nextStep() {
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    // Auto-save disabled for now - will save on final submit
                }
            },
            previousStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                }
            },
            autoSave() {
                // Auto-save current step data (silent, don't show errors)
                const formData = new FormData();
                Object.keys(this.formData).forEach(key => {
                    if (Array.isArray(this.formData[key])) {
                        this.formData[key].forEach(item => {
                            formData.append(key + '[]', item);
                        });
                    } else {
                        formData.append(key, this.formData[key] || '');
                    }
                });
                formData.append('_method', 'PATCH');
                
                fetch('{{ route("profile.model.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                }).catch(() => {
                    // Silently fail - auto-save is optional
                });
            },
            saveProfile() {
                document.getElementById('profileForm').submit();
            },
            updateGenderFields() {
                // Clear gender-specific fields when gender changes
                if (this.formData.gender !== 'male') {
                    this.formData.chest = '';
                    this.formData.inseam = '';
                    this.formData.suit_size = '';
                }
                if (this.formData.gender !== 'female') {
                    this.formData.bust = '';
                    this.formData.hips = '';
                    this.formData.dress_size = '';
                }
            }
        };
    }
    
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
                    console.error('No countries data provided');
                    return;
                }
                
                this.countries = Object.keys(countriesList).map(code => ({
                    code: code,
                    name: countriesList[code]
                })).sort((a, b) => a.name.localeCompare(b.name));
                
                // Initialize with first 50 countries visible
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
                    // Show all countries when search is empty
                    this.filteredCountries = this.countries.slice(0, 50); // Limit to first 50 for performance
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
                // Update the parent locationAutocomplete component
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
                
                // Store instance for country dropdown to access
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
</script>

