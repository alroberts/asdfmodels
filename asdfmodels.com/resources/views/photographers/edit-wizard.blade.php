<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Your Photographer Profile') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="photographerProfileWizard()" x-init="init()">
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
            <form method="POST" action="{{ route('photographers.profile.update') }}" @submit.prevent="saveProfile()" id="profileForm">
                @csrf
                @method('patch')

                @php
                    $specialtiesOptions = \App\Helpers\PhotographerOptions::specialties();
                    $servicesOptions = \App\Helpers\PhotographerOptions::services();
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
                            <textarea id="bio" name="bio" rows="4" x-model="formData.bio" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="Tell us about your photography style, experience, and what makes your work unique..."></textarea>
                            <x-input-error :messages="$errors?->get('bio') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="professional_name" :value="__('Professional/Business Name')" />
                            <x-text-input id="professional_name" name="professional_name" type="text" x-model="formData.professional_name" class="block mt-1 w-full" placeholder="e.g., ABC Photography, John Smith Photography" />
                            <p class="mt-1 text-xs text-gray-500">Optional - Your business or professional name if different from your real name</p>
                            <x-input-error :messages="$errors?->get('professional_name') ?? []" class="mt-2" />
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
                                <x-input-error :messages="$errors?->get('location_city') ?? []" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="gender" :value="__('Gender')" />
                                <select id="gender" name="gender" x-model="formData.gender" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                    <option value="">Select...</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <x-input-error :messages="$errors?->get('gender') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="experience_start_year" :value="__('What year did you start photography?')" />
                                <x-text-input id="experience_start_year" name="experience_start_year" type="number" x-model="formData.experience_start_year" class="block mt-1 w-full" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2015" />
                                <p class="mt-1 text-xs text-gray-500">Optional - helps show your experience level</p>
                                <x-input-error :messages="$errors?->get('experience_start_year') ?? []" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Professional Details -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Professional Details</h3>
                        <p class="text-gray-600">Tell us about your photography expertise</p>
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
                            <x-input-error :messages="$errors?->get('experience_level') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Specialties')" />
                            <p class="text-sm text-gray-600 mb-4">Select the types of photography you specialize in</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto pr-2">
                                @foreach($specialtiesOptions as $key => $label)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.specialties.includes('{{ $key }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="specialties[]" 
                                               value="{{ $key }}"
                                               x-model="formData.specialties"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.specialties.includes('{{ $key }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1" 
                                              :class="formData.specialties.includes('{{ $key }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('specialties') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Services Offered')" />
                            <p class="text-sm text-gray-600 mb-4">Select the services you offer</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto pr-2">
                                @foreach($servicesOptions as $key => $label)
                                    <label class="flex items-start cursor-pointer p-4 border-2 rounded-lg hover:border-black hover:shadow-md transition-all duration-200 group"
                                           :class="formData.services.includes('{{ $key }}') ? 'border-black bg-black text-white shadow-lg' : 'border-gray-300 bg-white'">
                                        <input type="checkbox" 
                                               name="services_offered[]" 
                                               value="{{ $key }}"
                                               x-model="formData.services"
                                               class="mt-0.5 w-5 h-5 rounded border-2 border-gray-400 text-black focus:ring-2 focus:ring-black focus:ring-offset-2 cursor-pointer transition-all"
                                               :class="formData.services.includes('{{ $key }}') ? 'border-white bg-white' : ''">
                                        <span class="ml-3 text-sm font-medium flex-1"
                                              :class="formData.services.includes('{{ $key }}') ? 'text-white' : 'text-gray-700 group-hover:text-black'">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors?->get('services_offered') ?? []" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 3: Equipment -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Your Equipment</h3>
                        <p class="text-gray-600">Show off your gear (optional but helps build credibility)</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Cameras -->
                        <div>
                            <x-input-label :value="__('Cameras')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.cameras" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.cameras.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newCamera" @keyup.enter.prevent="if(newCamera.trim()) { formData.equipment.cameras.push(newCamera.trim()); newCamera = ''; }" placeholder="e.g., Canon EOS R5" class="flex-1" />
                                <button type="button" @click="if(newCamera.trim()) { formData.equipment.cameras.push(newCamera.trim()); newCamera = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Lenses -->
                        <div>
                            <x-input-label :value="__('Lenses')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.lenses" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.lenses.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newLens" @keyup.enter.prevent="if(newLens.trim()) { formData.equipment.lenses.push(newLens.trim()); newLens = ''; }" placeholder="e.g., 24-70mm f/2.8" class="flex-1" />
                                <button type="button" @click="if(newLens.trim()) { formData.equipment.lenses.push(newLens.trim()); newLens = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Lighting -->
                        <div>
                            <x-input-label :value="__('Lighting Equipment')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.lighting" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.lighting.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newLighting" @keyup.enter.prevent="if(newLighting.trim()) { formData.equipment.lighting.push(newLighting.trim()); newLighting = ''; }" placeholder="e.g., Profoto B10" class="flex-1" />
                                <button type="button" @click="if(newLighting.trim()) { formData.equipment.lighting.push(newLighting.trim()); newLighting = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Other Equipment -->
                        <div>
                            <x-input-label :value="__('Other Equipment')" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(item, index) in formData.equipment.other" :key="index">
                                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm flex items-center">
                                        <span x-text="item"></span>
                                        <button type="button" @click="formData.equipment.other.splice(index, 1)" class="ml-2 text-gray-600 hover:text-red-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <x-text-input type="text" x-model="newOther" @keyup.enter.prevent="if(newOther.trim()) { formData.equipment.other.push(newOther.trim()); newOther = ''; }" placeholder="e.g., Tripod, Backdrops, etc." class="flex-1" />
                                <button type="button" @click="if(newOther.trim()) { formData.equipment.other.push(newOther.trim()); newOther = ''; }" class="bg-black text-white px-4 py-2 rounded">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Business Info -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Business Information</h3>
                        <p class="text-gray-600">Tell clients about your services and availability</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="studio_location" :value="__('Studio Location')" />
                            <x-text-input id="studio_location" name="studio_location" type="text" x-model="formData.studio_location" class="block mt-1 w-full" placeholder="City, Country or Address" />
                            <p class="mt-1 text-xs text-gray-500">Optional - Where is your studio located?</p>
                            <x-input-error :messages="$errors?->get('studio_location') ?? []" class="mt-2" />
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="available_for_travel" value="1" x-model="formData.available_for_travel" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Available for travel</span>
                            </label>
                            <p class="mt-2 ml-8 text-xs text-gray-500">Check this if you're willing to travel for photo shoots</p>
                        </div>

                        <div>
                            <x-input-label for="pricing_info" :value="__('Pricing Information')" />
                            <textarea id="pricing_info" name="pricing_info" rows="3" x-model="formData.pricing_info" class="block mt-1 w-full border-2 border-gray-800 rounded-md shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50" placeholder="e.g., Starting at €500 for headshots, €1500 for full-day sessions"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Optional - Give clients an idea of your pricing</p>
                            <x-input-error :messages="$errors?->get('pricing_info') ?? []" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Step 5: Contact & Social -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-black mb-2">Contact & Social Links</h3>
                        <p class="text-gray-600">How can people reach you?</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="public_email" :value="__('Public Email')" />
                            <x-text-input id="public_email" name="public_email" type="email" x-model="formData.public_email" class="block mt-1 w-full" placeholder="your@email.com" />
                            <p class="mt-1 text-xs text-gray-500">This will be visible on your public profile</p>
                            <x-input-error :messages="$errors?->get('public_email') ?? []" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" x-model="formData.phone" class="block mt-1 w-full" placeholder="+1 234 567 8900" />
                            <x-input-error :messages="$errors?->get('phone') ?? []" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="instagram" :value="__('Instagram')" />
                                <x-text-input id="instagram" name="instagram" type="text" x-model="formData.instagram" class="block mt-1 w-full" placeholder="@username" />
                                <x-input-error :messages="$errors?->get('instagram') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="facebook" :value="__('Facebook')" />
                                <x-text-input id="facebook" name="facebook" type="text" x-model="formData.facebook" class="block mt-1 w-full" placeholder="Your Facebook page" />
                                <x-input-error :messages="$errors?->get('facebook') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="twitter" :value="__('Twitter/X')" />
                                <x-text-input id="twitter" name="twitter" type="text" x-model="formData.twitter" class="block mt-1 w-full" placeholder="@username" />
                                <x-input-error :messages="$errors?->get('twitter') ?? []" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="portfolio_website" :value="__('Portfolio Website')" />
                                <x-text-input id="portfolio_website" name="portfolio_website" type="url" x-model="formData.portfolio_website" class="block mt-1 w-full" placeholder="https://yourportfolio.com" />
                                <x-input-error :messages="$errors?->get('portfolio_website') ?? []" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Settings -->
                <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="bg-white shadow-lg sm:rounded-lg p-6 md:p-8 border-2 border-gray-800">
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
                            <p class="mt-2 ml-8 text-xs text-gray-500">When enabled, your profile will be visible to models and other users</p>
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
                <input type="hidden" name="professional_name" x-model="formData.professional_name" />
                <input type="hidden" name="experience_start_year" x-model="formData.experience_start_year" />
                <input type="hidden" name="experience_level" x-model="formData.experience_level" />
                <template x-for="specialty in formData.specialties" :key="specialty">
                    <input type="hidden" name="specialties[]" :value="specialty" />
                </template>
                <template x-for="service in formData.services" :key="service">
                    <input type="hidden" name="services_offered[]" :value="service" />
                </template>
                <input type="hidden" name="equipment" :value="JSON.stringify(formData.equipment)" />
                <input type="hidden" name="studio_location" x-model="formData.studio_location" />
                <input type="hidden" name="available_for_travel" :value="formData.available_for_travel ? 1 : 0" />
                <input type="hidden" name="pricing_info" x-model="formData.pricing_info" />
                <input type="hidden" name="public_email" x-model="formData.public_email" />
                <input type="hidden" name="phone" x-model="formData.phone" />
                <input type="hidden" name="instagram" x-model="formData.instagram" />
                <input type="hidden" name="facebook" x-model="formData.facebook" />
                <input type="hidden" name="twitter" x-model="formData.twitter" />
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
    function photographerProfileWizard() {
        return {
            currentStep: 0,
            steps: [
                { title: 'Basic Info', icon: 'user' },
                { title: 'Professional', icon: 'briefcase' },
                { title: 'Equipment', icon: 'camera' },
                { title: 'Business', icon: 'store' },
                { title: 'Contact', icon: 'envelope' },
                { title: 'Settings', icon: 'cog' }
            ],
            formData: {
                bio: '',
                professional_name: '',
                locationCountryCode: '',
                locationCity: '',
                locationGeonameId: null,
                gender: '',
                experience_start_year: '',
                experience_level: '',
                specialties: [],
                services: [],
                equipment: {
                    cameras: [],
                    lenses: [],
                    lighting: [],
                    other: []
                },
                studio_location: '',
                available_for_travel: false,
                pricing_info: '',
                public_email: '',
                phone: '',
                instagram: '',
                facebook: '',
                twitter: '',
                portfolio_website: '',
                is_public: true,
                contains_nudity: false
            },
            newCamera: '',
            newLens: '',
            newLighting: '',
            newOther: '',
            init() {
                // Load existing profile data if available
                @php
                    $hasProfile = isset($profile) && method_exists($profile, 'exists') && $profile->exists;
                    $wizardData = null;
                    if ($hasProfile && $profile->id) {
                        $equipment = $profile->equipment ?? [];
                        if (!is_array($equipment) || !isset($equipment['cameras'])) {
                            $equipment = ['cameras' => [], 'lenses' => [], 'lighting' => [], 'other' => []];
                        }
                        $wizardData = [
                            'bio' => $profile->bio ?? '',
                            'professional_name' => $profile->professional_name ?? '',
                            'locationCountryCode' => $profile->location_country_code ?? '',
                            'locationCity' => $profile->location_city ?? '',
                            'locationGeonameId' => $profile->location_geoname_id ?? null,
                            'gender' => $profile->gender ?? '',
                            'experience_start_year' => $profile->experience_start_year ?? '',
                            'experience_level' => $profile->experience_level ?? '',
                            'specialties' => $profile->specialties ?? [],
                            'services' => $profile->services_offered ?? [],
                            'equipment' => $equipment,
                            'studio_location' => $profile->studio_location ?? '',
                            'available_for_travel' => (bool)($profile->available_for_travel ?? false),
                            'pricing_info' => $profile->pricing_info ?? '',
                            'public_email' => $profile->public_email ?? '',
                            'phone' => $profile->phone ?? '',
                            'instagram' => $profile->instagram ?? '',
                            'facebook' => $profile->facebook ?? '',
                            'twitter' => $profile->twitter ?? '',
                            'portfolio_website' => $profile->portfolio_website ?? '',
                            'is_public' => (bool)($profile->is_public ?? true),
                            'contains_nudity' => (bool)($profile->contains_nudity ?? false)
                        ];
                    }
                @endphp
                @if($wizardData)
                    const wizardData = @json($wizardData);
                    Object.assign(this.formData, wizardData);
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
                    if (key === 'equipment') {
                        formData.append('equipment', JSON.stringify(this.formData[key]));
                    } else if (Array.isArray(this.formData[key])) {
                        this.formData[key].forEach(item => {
                            formData.append(key + '[]', item);
                        });
                    } else {
                        formData.append(key, this.formData[key] || '');
                    }
                });
                formData.append('_method', 'PATCH');
                
                fetch('{{ route("photographers.profile.update") }}', {
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
                    console.error('No countries data provided', countriesList);
                    this.filteredCountries = [];
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
                
                console.log('Countries loaded:', this.countries.length, 'Filtered:', this.filteredCountries.length);
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

